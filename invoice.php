<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../app/db.php';
require __DIR__ . '/../../vendor/autoload.php';

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

// ======== 3. Mail via PHPMailer verschicken ========
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $mailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $mailConfig['port'];
    $mail->CharSet    = 'UTF-8'; // Wichtig für Umlaute

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress('sebastian.hauss@gmx.at', $rechnung['Firmenname']);

    $mail->isHTML(true);
    $mail->Subject = "Ihre Rechnung Nr. {$rechnungNr}";
    $mail->Body    = $html;

    $mail->send();
    echo "Rechnung erfolgreich an sebastian.hauss@gmx.at gesendet!";

} catch (Exception $e) {
    echo "Fehler beim Mailversand: {$mail->ErrorInfo}";
}