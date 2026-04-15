<?php
/**
 * checkout.php - Bestellung abschließen
 *
 * Funktion:
 * - Nimmt Warenkorb-Daten per POST JSON
 * - Prüft Lagerbestand serverseitig
 * - Reduziert Lagerbestand bei erfolgreichem Kauf
 * - Generiert Rechnung HTML/PDF
 * - Versendet per Mail an eingeloggten Kunden
 * - Antwortet mit JSON
 */

use PHPMailer\PHPMailer\PHPMailer;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../app/auth/bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/db.php';

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Sie sind nicht eingeloggt');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST erlaubt');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['cart']) || !is_array($input['cart'])) {
        throw new Exception('Warenkorb ist leer');
    }

    $cart = $input['cart'];
    $services = is_array($input['services'] ?? null) ? $input['services'] : [];
    $paymentMethod = (string)($input['paymentMethod'] ?? 'bank_transfer');

    $customerName = $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Kunde';
    $customerEmail = $_SESSION['user_email'] ?? '';

    if (!$customerEmail) {
        throw new Exception('Keine E-Mail-Adresse in der Session gefunden');
    }

    $pdo = db();

    // === KUNDENDATEN AUS DB ===
    $cd = [];
    try {
        $stmt = $pdo->prepare("
            SELECT
                k.Firmenname,
                k.`Ansprechpartner Vorname`  AS ap_vorname,
                k.`Ansprechpartner Nachname` AS ap_nachname,
                k.SubTier,
                k.Kontodaten_IBAN            AS iban,
                p.Adresse,
                p.PLZ,
                p.Ort,
                p.Telefon,
                kd.Bank,
                kd.BIC
            FROM users u
            LEFT JOIN Kunden k ON k.Kundennummer = u.Kunden_Kundennummer
            LEFT JOIN persoenliche_daten p ON p.id_persoenliche_daten = k.persoenliche_daten_id
            LEFT JOIN Kontodaten kd ON kd.IBAN = k.Kontodaten_IBAN
            WHERE u.idusers = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cd = $stmt->fetch() ?: [];
    } catch (Throwable $dbEx) {
        error_log('Customer data fetch: ' . $dbEx->getMessage());
    }

    // === POSITIONEN PRÜFEN + LAGERSTAND REDUZIEREN ===
    $total = 0.0;
    $items = [];
    $stockUpdates = [];

    $planNames = [
        'basic' => 'Basic Plan',
        'pro' => 'Pro Plan',
        'enterprise' => 'Enterprise Plan',
    ];

    $pdo->beginTransaction();

    try {
        $articleStmt = $pdo->prepare("
            SELECT Artikelnummer, Bezeichnung, Preis, Waehrung, Stueckzahl
            FROM Artikel
            WHERE Artikelnummer = ?
            FOR UPDATE
        ");

        foreach ($cart as $item) {
            $qty = max(1, (int)($item['qty'] ?? 1));

            // Webshop-Artikel
            if (!empty($item['sku'])) {
                $sku = (string)$item['sku'];

                $articleStmt->execute([$sku]);
                $dbArticle = $articleStmt->fetch();

                if (!$dbArticle) {
                    throw new Exception("Artikel mit der Nummer {$sku} wurde nicht gefunden.");
                }

                $availableStock = (int)$dbArticle['Stueckzahl'];
                $articleName = (string)($dbArticle['Bezeichnung'] ?? 'Artikel');
                $unitPrice = (float)($dbArticle['Preis'] ?? 0);
                $linePrice = $unitPrice * $qty;

                if ($availableStock <= 0) {
                    throw new Exception("Der Artikel „{$articleName}“ ist ausverkauft.");
                }

                if ($qty > $availableStock) {
                    throw new Exception("Vom Artikel „{$articleName}“ sind nur {$availableStock} Stück lagernd.");
                }

                $displayName = $articleName;
                if ($qty > 1) {
                    $displayName .= ' (x' . $qty . ')';
                }

                $items[] = [
                    'name' => $displayName,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'price' => $linePrice,
                    'type' => 'article'
                ];

                $total += $linePrice;
                $stockUpdates[] = [
                    'sku' => $sku,
                    'qty' => $qty
                ];

                continue;
            }

            // Abos / alte Plan-Positionen
            $unitPrice = (float)($item['price'] ?? 0);
            $linePrice = $unitPrice * $qty;

            $itemName = $item['name'] ?? null;
            if (!$itemName && !empty($item['plan']) && isset($planNames[$item['plan']])) {
                $itemName = $planNames[$item['plan']];
            }
            if (!$itemName) {
                $itemName = 'Artikel';
            }
            if ($qty > 1) {
                $itemName .= ' (x' . $qty . ')';
            }

            $items[] = [
                'name' => $itemName,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'price' => $linePrice,
                'type' => 'plan'
            ];

            $total += $linePrice;
        }

        if (!empty($services['website'])) {
            $items[] = [
                'name' => 'Website-Erstellung',
                'qty' => 1,
                'unit_price' => 499.00,
                'price' => 499.00,
                'type' => 'service'
            ];
            $total += 499.00;
        }

        if (!empty($services['hosting'])) {
            $items[] = [
                'name' => 'Hosting/Monat',
                'qty' => 1,
                'unit_price' => 9.99,
                'price' => 9.99,
                'type' => 'service'
            ];
            $total += 9.99;
        }

        if (!empty($stockUpdates)) {
            $updateStmt = $pdo->prepare("
                UPDATE Artikel
                SET Stueckzahl = Stueckzahl - ?
                WHERE Artikelnummer = ? AND Stueckzahl >= ?
            ");

            foreach ($stockUpdates as $update) {
                $updateStmt->execute([
                    $update['qty'],
                    $update['sku'],
                    $update['qty']
                ]);

                if ($updateStmt->rowCount() !== 1) {
                    throw new Exception('Lagerstand konnte nicht aktualisiert werden. Bitte versuchen Sie es erneut.');
                }
            }
        }

        $pdo->commit();
    } catch (Throwable $txEx) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $txEx;
    }

    // === RECHNUNG GENERIEREN ===
    $invoiceNum = 'RG-' . date('Ymd') . '-' . strtoupper(substr(md5((string)microtime(true)), 0, 6));
    $invoiceDate = date('d.m.Y H:i');

    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 700px; margin: 0 auto; padding: 20px; }
            .header { background: #118075; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; }
            .info { margin: 20px 0; }
            .info p { margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            table th { background: #f0f0f0; font-weight: bold; }
            .total-row { background: #118075; color: white; font-weight: bold; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>RECHNUNG</h1>
                <p>{$invoiceNum}</p>
            </div>

            <div class='content'>
                <div class='info'>
                    <p><strong>Rechnungsdatum:</strong> " . htmlspecialchars($invoiceDate) . "</p>
                    <p><strong>Für:</strong> " . htmlspecialchars($customerName) . "</p>
                    <p><strong>E-Mail:</strong> " . htmlspecialchars($customerEmail) . "</p>
                </div>

                <table>
                    <tr>
                        <th>Position</th>
                        <th>Menge</th>
                        <th style='text-align:right;'>Betrag</th>
                    </tr>";

    foreach ($items as $item) {
        $html .= "
                    <tr>
                        <td>" . htmlspecialchars($item['name']) . "</td>
                        <td>" . (int)$item['qty'] . "</td>
                        <td style='text-align:right;'>€" . number_format((float)$item['price'], 2, ',', '.') . "</td>
                    </tr>";
    }

    $html .= "
                    <tr class='total-row'>
                        <td><strong>GESAMTBETRAG</strong></td>
                        <td></td>
                        <td style='text-align:right;'><strong>€" . number_format($total, 2, ',', '.') . "</strong></td>
                    </tr>
                </table>

                <div class='info'>
                    <p><strong>Zahlungsart:</strong> " . htmlspecialchars($paymentMethod) . "</p>
                </div>

                <div class='footer'>
                    <p>Vielen Dank für Ihren Einkauf!</p>
                    <p>BookIT - Automatisch generierte Rechnung</p>
                </div>
            </div>
        </div>
    </body>
    </html>";

    // === PDF ERSTELLEN ===
    $mwstSatz   = 0.20;
    $nettoGes   = $total / (1 + $mwstSatz);
    $mwstGes    = $total - $nettoGes;
    $rechnDatum = $invoiceDate;

    $pdfHtml = '<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
  body  { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; margin:0; padding:0; }
  .page { padding: 40px 50px; }
  table { width:100%; border-collapse:collapse; }
  th    { background:#118075; color:#fff; padding:8px 10px; text-align:left; font-size:10px; }
  td    { padding:7px 10px; border-bottom:1px solid #e8e8e8; }
  tr:nth-child(even) td { background:#f8faf9; }
  .total-row td { border-top:2px solid #118075; font-weight:bold; font-size:13px; color:#118075; padding-top:8px; }
  .badge { background:#e6f4f1; color:#118075; padding:3px 10px; border-radius:12px; font-size:9px; font-weight:bold; text-transform:uppercase; }
  .hint { margin-top:24px; background:#f0faf8; border-left:4px solid #118075; padding:10px 14px; font-size:10px; }
  .footer { margin-top:40px; border-top:1px solid #ccc; padding-top:10px; font-size:9px; color:#888; text-align:center; }
</style>
</head>
<body>
<div class="page">

  <table style="border:none; margin-bottom:28px;">
    <tr>
      <td style="border:none; padding:0; vertical-align:top;">
        <div style="font-size:22px; font-weight:bold; color:#118075; letter-spacing:1px;">BookIT</div>
        <div style="color:#888; font-size:10px;">Raumverwaltung</div>
      </td>
      <td style="border:none; padding:0; text-align:right; vertical-align:top;">
        <div style="font-size:20px; font-weight:bold; color:#118075;">RECHNUNG</div>
        <div style="font-size:11px; margin-top:4px;">Nr. <strong>' . htmlspecialchars($invoiceNum) . '</strong></div>
        <div style="font-size:11px;">Datum: <strong>' . htmlspecialchars($rechnDatum) . '</strong></div>
        <div class="badge">Zahlungsart: ' . htmlspecialchars($paymentMethod) . '</div>
      </td>
    </tr>
  </table>

  <table style="border:none; margin-bottom:24px;">
    <tr>
      <td style="border:none; padding:0; width:50%; vertical-align:top;">
        <div style="color:#888; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Absender</div>
        <strong>bookIt GmbH</strong><br>
        Musterstraße 1<br>1010 Wien<br>
        E-Mail: office@bookit.at<br>UID: ATU12345678
      </td>
      <td style="border:none; padding:0; width:50%; vertical-align:top;">
        <div style="color:#888; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Rechnungsempfänger</div>
        ' . (!empty($cd['Firmenname']) ? '<strong>' . htmlspecialchars($cd['Firmenname']) . '</strong><br>' : '') . '
        ' . (!empty($cd['ap_vorname']) || !empty($cd['ap_nachname']) ? htmlspecialchars(trim(($cd['ap_vorname'] ?? '') . ' ' . ($cd['ap_nachname'] ?? ''))) . '<br>' : '<strong>' . htmlspecialchars($customerName) . '</strong><br>') . '
        ' . (!empty($cd['Adresse']) ? htmlspecialchars($cd['Adresse']) . '<br>' : '') . '
        ' . (!empty($cd['PLZ']) || !empty($cd['Ort']) ? htmlspecialchars(trim(($cd['PLZ'] ?? '') . ' ' . ($cd['Ort'] ?? ''))) . '<br>' : '') . '
        E-Mail: ' . htmlspecialchars($customerEmail) . '<br>
        ' . (!empty($cd['Telefon']) ? 'Tel.: ' . htmlspecialchars($cd['Telefon']) . '<br>' : '') . '
        ' . (!empty($cd['iban']) ? 'IBAN: ' . htmlspecialchars($cd['iban']) . '<br>' : '') . '
        ' . (!empty($cd['Bank']) ? 'Bank: ' . htmlspecialchars($cd['Bank']) . (!empty($cd['BIC']) ? ' | BIC: ' . htmlspecialchars($cd['BIC']) : '') . '<br>' : '') . '
      </td>
    </tr>
  </table>

  <table>
    <thead>
      <tr>
        <th style="width:44%;">Bezeichnung</th>
        <th style="width:12%; text-align:right;">Menge</th>
        <th style="width:22%; text-align:right;">Einzelpreis (netto)</th>
        <th style="width:22%; text-align:right;">Betrag (netto)</th>
      </tr>
    </thead>
    <tbody>';

    foreach ($items as $item) {
        $unitNetto = ((float)$item['unit_price']) / (1 + $mwstSatz);
        $lineNetto = ((float)$item['price']) / (1 + $mwstSatz);

        $pdfHtml .= '<tr>
            <td>' . htmlspecialchars($item['name']) . '</td>
            <td style="text-align:right;">' . (int)$item['qty'] . '</td>
            <td style="text-align:right;">' . number_format($unitNetto, 2, ',', '.') . ' &euro;</td>
            <td style="text-align:right;">' . number_format($lineNetto, 2, ',', '.') . ' &euro;</td>
        </tr>';
    }

    $pdfHtml .= '</tbody>
  </table>

  <table style="border:none; margin-top:16px;">
    <tr>
      <td style="border:none; width:55%;"></td>
      <td style="border:none; text-align:right; padding:4px 10px;">Nettobetrag:</td>
      <td style="border:none; text-align:right; padding:4px 10px; white-space:nowrap;">' . number_format($nettoGes, 2, ',', '.') . ' &euro;</td>
    </tr>
    <tr>
      <td style="border:none;"></td>
      <td style="border:none; text-align:right; padding:4px 10px;">MwSt. (20&nbsp;%):</td>
      <td style="border:none; text-align:right; padding:4px 10px; white-space:nowrap;">' . number_format($mwstGes, 2, ',', '.') . ' &euro;</td>
    </tr>
    <tr class="total-row">
      <td style="border:none;"></td>
      <td style="text-align:right; font-weight:bold; font-size:13px; color:#118075; border-top:2px solid #118075; padding:8px 10px 4px;" colspan="2">Gesamtbetrag (brutto):</td>
      <td style="text-align:right; font-weight:bold; font-size:13px; color:#118075; border-top:2px solid #118075; padding:8px 10px 4px; white-space:nowrap;">' . number_format($total, 2, ',', '.') . ' &euro;</td>
    </tr>
  </table>

  <div class="hint">
    <strong>Zahlungsinformationen:</strong><br>
    Bitte überweisen Sie <strong>' . number_format($total, 2, ',', '.') . ' &euro;</strong> innerhalb von
    <strong>14 Tagen</strong> unter Angabe der Rechnungsnummer <strong>' . htmlspecialchars($invoiceNum) . '</strong>.<br>
    IBAN: AT12 3456 7890 1234 5678 &nbsp;|&nbsp; BIC: OPSKATWW
  </div>

  <div class="footer">
    bookIt GmbH &bull; Musterstraße 1 &bull; 1010 Wien &bull; office@bookit.at &bull; UID: ATU12345678
  </div>
</div>
</body>
</html>';

    $pdfAttachment = null;
    $pdfTempFile = null;
    try {
        $dompdfOpt = new Options();
        $dompdfOpt->set('isRemoteEnabled', false);
        $dompdfOpt->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($dompdfOpt);
        $dompdf->loadHtml($pdfHtml, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfAttachment = $dompdf->output();
        if (!empty($pdfAttachment)) {
            $pdfTempFile = tempnam(sys_get_temp_dir(), 'bookit_inv_') . '.pdf';
            file_put_contents($pdfTempFile, $pdfAttachment);
        }
    } catch (Throwable $pdfEx) {
        error_log('PDF generation error: ' . $pdfEx->getMessage());
    }

    // === EMAIL VERSENDEN ===
    $mailConfig = require __DIR__ . '/../config/mail.php';

    if (!is_array($mailConfig) || empty($mailConfig['host'])) {
        throw new Exception('Mail-Konfiguration ungültig');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)$mailConfig['port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($customerEmail, $customerName);

    $mail->isHTML(true);
    $mail->Subject = "Ihre Rechnung {$invoiceNum} - BookIT";
    $mail->Body = $html;
    $mail->AltBody = "Rechnung {$invoiceNum}\nBetrag: €" . number_format($total, 2, ',', '.') . "\nDie detaillierte Rechnung finden Sie im PDF-Anhang.";

    if ($pdfTempFile && file_exists($pdfTempFile)) {
        $mail->addAttachment($pdfTempFile, 'Rechnung_' . $invoiceNum . '.pdf', PHPMailer::ENCODING_BASE64, 'application/pdf');
    }

    $emailSent = false;
    $emailError = null;
    try {
        $mail->send();
        $emailSent = true;
    } catch (Throwable $mailEx) {
        $emailError = $mailEx->getMessage();
        error_log('Mail send error: ' . $emailError);
    } finally {
        if ($pdfTempFile && file_exists($pdfTempFile)) {
            @unlink($pdfTempFile);
        }
    }

    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $emailSent ? 'Rechnung versendet' : 'Bestellung erfolgreich gespeichert',
        'email_sent' => $emailSent,
        'email_error' => $emailError,
        'invoiceNumber' => $invoiceNum,
        'amount' => $total,
        'email' => $customerEmail
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    ob_end_clean();
    http_response_code(400);
    error_log('Checkout: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>