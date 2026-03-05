<?php
/**
 * checkout.php - Bestellung abschließen
 * 
 * Funktion: 
 * - Nimmt Warenkorb-Daten per POST JSON
 * - Generiert Rechnung HTML
 * - Versendet per Mail an eingeloggten Kunden
 * - Antwortet mit JSON
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

// === INIT (REIHENFOLGE WICHTIG!) ===
require_once __DIR__ . '/../app/auth/bootstrap.php';  // Session ZUERST
require_once __DIR__ . '/../vendor/autoload.php';


header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // === VALIDIERUNG ===
    if (empty($_SESSION['user_id'])) {
        throw new Exception('Sie sind nicht eingeloggt');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('nur POST erlaubt');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['cart'])) {
        throw new Exception('Warenkorb ist leer');
    }
    
    // === DATEN ===
    $cart = $input['cart'];
    $services = $input['services'] ?? [];
    $paymentMethod = $input['paymentMethod'] ?? 'bank_transfer';
    
    $customerName = $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Kunde';
    $customerEmail = $_SESSION['user_email'] ?? '';
    
    if (!$customerEmail) {
        throw new Exception('Keine Email in Session');
    }
    
    // === BETRAG BERECHNEN ===
    $total = 0;
    $items = [];
    
    foreach ($cart as $item) {
        $price = floatval($item['price'] ?? 0);
        $total += $price;
        $items[] = [
            'name' => htmlspecialchars($item['plan'] ?? 'Artikel'),
            'price' => $price
        ];
    }
    
    if ($services['website'] ?? false) {
        $total += 499;
        $items[] = ['name' => 'Website-Erstellung', 'price' => 499];
    }
    
    if ($services['hosting'] ?? false) {
        $total += 9.99;
        $items[] = ['name' => 'Hosting/Monat', 'price' => 9.99];
    }
    
    // === RECHNUNG GENERIEREN ===
    $invoiceNum = 'RG-' . date('Ymd') . '-' . strtoupper(substr(md5(microtime()), 0, 6));
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #118075; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .header h1 { margin: 0; font-size: 28px; }
            .header p { margin: 5px 0; font-size: 14px; opacity: 0.9; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #e0e0e0; border-radius: 0 0 8px 8px; }
            .info { margin: 20px 0; }
            .info p { margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            table th { background: #f0f0f0; font-weight: bold; }
            .total-row { background: #118075; color: white; font-weight: bold; }
            .total-row td { border-bottom: none; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>RECHNUNG</h1>
                <p>$invoiceNum</p>
            </div>
            
            <div class='content'>
                <div class='info'>
                    <p><strong>Rechnungsdatum:</strong> " . date('d.m.Y H:i') . "</p>
                    <p><strong>Für:</strong> " . htmlspecialchars($customerName) . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($customerEmail) . "</p>
                </div>
                
                <table>
                    <tr>
                        <th>Position</th>
                        <th style='text-align: right;'>Betrag</th>
                    </tr>";
    
    foreach ($items as $item) {
        $html .= "
                    <tr>
                        <td>" . $item['name'] . "</td>
                        <td style='text-align: right;'>€" . number_format($item['price'], 2, ',', '.') . "</td>
                    </tr>";
    }
    
    $html .= "
                    <tr class='total-row'>
                        <td><strong>GESAMTBETRAG</strong></td>
                        <td style='text-align: right;'><strong>€" . number_format($total, 2, ',', '.') . "</strong></td>
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
    </html>
    ";
    
    // === EMAIL VERSENDEN ===
    $mailConfig = require __DIR__ . '/../config/mail.php';
    
    if (!is_array($mailConfig) || empty($mailConfig['host'])) {
        throw new Exception('Mail config ungültig');
    }
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = intval($mailConfig['port']);
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($customerEmail, $customerName);
    
    $mail->isHTML(true);
    $mail->Subject = "Ihre Rechnung $invoiceNum - BookIT";
    $mail->Body = $html;
    $mail->AltBody = "Rechnung $invoiceNum\nBetrag: €" . number_format($total, 2, ',', '.');
    
    $emailSent = false;
    $emailError = null;
    try {
        $mail->send();
        $emailSent = true;
    } catch (Exception $mailEx) {
        $emailError = $mailEx->getMessage();
        error_log('Mail send error: ' . $emailError);
    }
    
    // === RESPONSE ===
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $emailSent ? 'Rechnung versendet' : 'Bestellung gespeichert',
        'email_sent' => $emailSent,
        'email_error' => $emailError,
        'invoiceNumber' => $invoiceNum,
        'amount' => $total,
        'email' => $customerEmail
    ]);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(400);
    error_log("Checkout: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>