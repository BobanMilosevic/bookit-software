<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../app/db.php';
require __DIR__ . '/../vendor/autoload.php';

// SMTP Config laden
$mailConfig = require __DIR__ . '/../config/mail.php';

// ======== 1. Kunde & letzte Rechnung laden ========
$kundennummer = 1; // Testkunde
$pdo = db();

// JOIN über die sauberen Tabellen- und Spaltennamen
$stmt = $pdo->prepare("
    SELECT b.Rechnungsnummer, b.Datum, b.Betrag, b.Zahlungsmethode,
           k.Firmenname, pd.Adresse, pd.PLZ, pd.Ort, pd.Telefon, pd.EMail
    FROM Belege b
    JOIN Kunden k ON b.Kunden_Kundennummer = k.Kundennummer
    JOIN persoenliche_daten pd 
         ON k.persoenliche_daten_id = pd.id_persoenliche_daten
    WHERE k.Kundennummer = ?
    ORDER BY b.Datum DESC
    LIMIT 1
");

$stmt->execute([$kundennummer]);
$rechnung = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rechnung) {
    die("Keine Rechnung oder verknüpfte Kundendaten gefunden.");
}

// Artikel der Rechnung laden (mit der neuen Spalte 'Stueckzahl')
$stmt2 = $pdo->prepare("
    SELECT a.Bezeichnung, a.Preis, bha.Stueckzahl AS Menge
    FROM Belege_has_Artikel bha
    JOIN Artikel a ON bha.Artikel_Artikelnummer = a.Artikelnummer
    WHERE bha.Belege_Rechnungsnummer = ?
");
$stmt2->execute([$rechnung['Rechnungsnummer']]);
$artikel = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ======== 2. HTML-Rechnung erstellen ========
$rechnungNr = $rechnung['Rechnungsnummer'];
$datum = date('d.m.Y', strtotime($rechnung['Datum']));

$html = "<h1 style='font-family: Arial;'>Rechnung {$rechnungNr}</h1>";
$html .= "<p style='font-family: Arial;'>Datum: {$datum}</p>";
$html .= "<p style='font-family: Arial;'><strong>Empfänger:</strong><br>";
$html .= "{$rechnung['Firmenname']}<br>";
$html .= "{$rechnung['Adresse']}<br>";
$html .= "{$rechnung['PLZ']} {$rechnung['Ort']}<br>";
$html .= "E-Mail: {$rechnung['EMail']}</p>";

$html .= "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse: collapse; font-family: Arial;'>
<tr style='background-color: #f2f2f2;'><th>Produkt</th><th>Menge</th><th>Preis</th><th>Summe</th></tr>";

$gesamt = 0;
foreach ($artikel as $item) {
    $summe = $item['Preis'] * $item['Menge'];
    $gesamt += $summe;
    $html .= "<tr>
        <td>{$item['Bezeichnung']}</td>
        <td align='center'>{$item['Menge']}</td>
        <td align='right'>" . number_format($item['Preis'], 2, ',', '.') . " €</td>
        <td align='right'>" . number_format($summe, 2, ',', '.') . " €</td>
    </tr>";
}

$html .= "<tr>
<td colspan='3' align='right'><strong>Gesamtbetrag</strong></td>
<td align='right'><strong>" . number_format($gesamt, 2, ',', '.') . " €</strong></td>
</tr>";
$html .= "</table>";

// ======== 3. PDF erstellen ========
$mwstSatz = 0.20; // 20% MwSt.
$netto    = $gesamt / (1 + $mwstSatz);
$mwst     = $gesamt - $netto;

$pdfHtml = '<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
  body        { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 0; }
  .page       { padding: 40px 50px; }
  .header     { display: flex; justify-content: space-between; margin-bottom: 30px; }
  .logo       { font-size: 22px; font-weight: bold; color: #1a3c5e; letter-spacing: 1px; }
  .invoice-meta { text-align: right; font-size: 11px; }
  .invoice-meta h2 { margin: 0 0 6px 0; font-size: 18px; color: #1a3c5e; }
  .address-block { margin-bottom: 25px; font-size: 11px; line-height: 1.6; }
  .address-block .label { color: #888; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
  table       { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th          { background-color: #1a3c5e; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  td          { padding: 7px 10px; border-bottom: 1px solid #e8e8e8; vertical-align: top; }
  tr:nth-child(even) td { background-color: #f8f9fc; }
  .totals     { margin-top: 20px; width: 100%; }
  .totals td  { border: none; padding: 4px 10px; }
  .totals .label-col { width: 70%; }
  .totals .sep { border-top: 2px solid #1a3c5e; }
  .totals .grand-total td { font-weight: bold; font-size: 13px; color: #1a3c5e; border-top: 2px solid #1a3c5e; padding-top: 8px; }
  .footer     { margin-top: 50px; border-top: 1px solid #ccc; padding-top: 12px; font-size: 9px; color: #888; text-align: center; }
  .badge      { display: inline-block; background: #e8f0fe; color: #1a3c5e; padding: 3px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; margin-top: 4px; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <table style="width:100%; border:none; margin-bottom:30px;">
    <tr>
      <td style="border:none; padding:0; vertical-align:top;">
        <div class="logo">bookIt</div>
        <div style="color:#888; font-size:10px;">Buchungs- &amp; Fakturierungssystem</div>
      </td>
      <td style="border:none; padding:0; text-align:right; vertical-align:top;">
        <div style="font-size:20px; font-weight:bold; color:#1a3c5e;">RECHNUNG</div>
        <div style="font-size:11px; margin-top:4px;">Nr. <strong>' . htmlspecialchars($rechnungNr) . '</strong></div>
        <div style="font-size:11px;">Datum: <strong>' . htmlspecialchars($datum) . '</strong></div>
        <div class="badge">Zahlungsmethode: ' . htmlspecialchars($rechnung['Zahlungsmethode']) . '</div>
      </td>
    </tr>
  </table>

  <!-- Adressblock -->
  <table style="width:100%; border:none; margin-bottom:25px;">
    <tr>
      <td style="border:none; padding:0; width:50%; vertical-align:top;">
        <div style="color:#888; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Absender</div>
        <strong>bookIt GmbH</strong><br>
        Musterstraße 1<br>
        1010 Wien<br>
        E-Mail: office@bookit.at<br>
        UID: ATU12345678
      </td>
      <td style="border:none; padding:0; width:50%; vertical-align:top;">
        <div style="color:#888; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Rechnungsempfänger</div>
        <strong>' . htmlspecialchars($rechnung['Firmenname']) . '</strong><br>
        ' . htmlspecialchars($rechnung['Adresse']) . '<br>
        ' . htmlspecialchars($rechnung['PLZ']) . ' ' . htmlspecialchars($rechnung['Ort']) . '<br>
        Tel.: ' . htmlspecialchars($rechnung['Telefon']) . '<br>
        E-Mail: ' . htmlspecialchars($rechnung['EMail']) . '
      </td>
    </tr>
  </table>

  <!-- Artikeltabelle -->
  <table>
    <thead>
      <tr>
        <th style="width:45%">Bezeichnung</th>
        <th style="width:15%; text-align:center;">Menge</th>
        <th style="width:20%; text-align:right;">Einzelpreis (netto)</th>
        <th style="width:20%; text-align:right;">Zeilensumme (netto)</th>
      </tr>
    </thead>
    <tbody>';

foreach ($artikel as $item) {
    $einzelNetto  = $item['Preis'] / (1 + $mwstSatz);
    $zeilenNetto  = $einzelNetto * $item['Menge'];
    $pdfHtml .= '<tr>
        <td>' . htmlspecialchars($item['Bezeichnung']) . '</td>
        <td style="text-align:center;">' . (int)$item['Menge'] . '</td>
        <td style="text-align:right;">' . number_format($einzelNetto, 2, ',', '.') . ' &euro;</td>
        <td style="text-align:right;">' . number_format($zeilenNetto, 2, ',', '.') . ' &euro;</td>
    </tr>';
}

$pdfHtml .= '    </tbody>
  </table>

  <!-- Summenblock -->
  <table class="totals" style="width:100%; border:none; margin-top:20px;">
    <tr>
      <td class="label-col" style="border:none;"></td>
      <td style="border:none; text-align:right; padding:4px 10px;">Nettobetrag:</td>
      <td style="border:none; text-align:right; padding:4px 10px;">' . number_format($netto, 2, ',', '.') . ' &euro;</td>
    </tr>
    <tr>
      <td style="border:none;"></td>
      <td style="border:none; text-align:right; padding:4px 10px;">MwSt. (20&nbsp;%):</td>
      <td style="border:none; text-align:right; padding:4px 10px;">' . number_format($mwst, 2, ',', '.') . ' &euro;</td>
    </tr>
    <tr class="grand-total">
      <td style="border:none;"></td>
      <td style="text-align:right; font-weight:bold; font-size:13px; color:#1a3c5e; border-top:2px solid #1a3c5e; padding:8px 10px 4px;">Gesamtbetrag (brutto):</td>
      <td style="text-align:right; font-weight:bold; font-size:13px; color:#1a3c5e; border-top:2px solid #1a3c5e; padding:8px 10px 4px;">' . number_format($gesamt, 2, ',', '.') . ' &euro;</td>
    </tr>
  </table>

  <!-- Zahlungshinweis -->
  <div style="margin-top:30px; background:#f0f4ff; border-left:4px solid #1a3c5e; padding:10px 14px; font-size:10px;">
    <strong>Zahlungsinformationen:</strong><br>
    Bitte überweisen Sie den Betrag von <strong>' . number_format($gesamt, 2, ',', '.') . ' &euro;</strong>
    innerhalb von <strong>14 Tagen</strong> unter Angabe der Rechnungsnummer <strong>' . htmlspecialchars($rechnungNr) . '</strong>.<br>
    IBAN: AT12 3456 7890 1234 5678 &nbsp;&nbsp;|&nbsp;&nbsp; BIC: OPSKATWW
  </div>

  <!-- Footer -->
  <div class="footer">
    bookIt GmbH &bull; Musterstraße 1 &bull; 1010 Wien &bull; office@bookit.at &bull; UID: ATU12345678
  </div>

</div>
</body>
</html>';

// ======== 4. PDF via Dompdf erzeugen ========
try {
    $dompdfOptions = new Options();
    $dompdfOptions->set('isRemoteEnabled', false);
    $dompdfOptions->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($dompdfOptions);
    $dompdf->loadHtml($pdfHtml, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    if (empty($pdfOutput)) {
        throw new \RuntimeException('Dompdf hat einen leeren Output geliefert.');
    }
} catch (\Throwable $e) {
    die('PDF-Generierungsfehler: ' . $e->getMessage());
}

// ======== 5. Mail via PHPMailer verschicken ========
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailConfig['port'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress('sebastian.hauss@gmx.at', $rechnung['Firmenname']);

    $mail->isHTML(true);
    $mail->Subject  = "Ihre Rechnung Nr. {$rechnungNr}";
    $mail->Body     = $html;
    $mail->AltBody  = "Rechnung Nr. {$rechnungNr} vom {$datum}. Gesamtbetrag: " . number_format($gesamt, 2, ',', '.') . " EUR. Die detaillierte Rechnung finden Sie im PDF-Anhang.";

    // PDF als Anhang (Rohstring, Base64-kodiert)
    $mail->addStringAttachment(
        $pdfOutput,
        "Rechnung_{$rechnungNr}.pdf",
        PHPMailer::ENCODING_BASE64,
        'application/pdf',
        'attachment'
    );

    $mail->send();
    echo "Rechnung (PDF: " . round(strlen($pdfOutput) / 1024, 1) . " KB) erfolgreich an sebastian.hauss@gmx.at gesendet!";

} catch (Exception $e) {
    echo "Fehler beim Mailversand: " . $mail->ErrorInfo;
}