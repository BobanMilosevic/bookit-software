# BookIT - Raumverwaltung und Buchungssoftware

BookIT ist eine PHP-basierte Buchungsplattform für Raumverwaltung. Benutzer können Räume online buchen, erhalten einen Verifizierungscode per E-Mail und checken vor Ort ein, indem sie einen QR-Code scannen und den Code eingeben.

## Features

- Online-Buchung von Räumen
- E-Mail-Verifizierung mit Code
- Vor-Ort-Check-in via QR-Code und Code-Eingabe
- Professionelle Website mit Bootstrap-Design
- Mock-Versionen für Tests ohne DB/E-Mail

## Installation

1. Installiere Composer-Abhängigkeiten: `composer install`
2. Richte eine MySQL-Datenbank 'bookit_db' ein mit der Tabelle:
   ```sql
   CREATE TABLE bookings (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_email VARCHAR(255),
       room_id VARCHAR(255),
       date DATE,
       time TIME,
       verification_code INT,
       verified BOOLEAN DEFAULT 0
   );
   ```
3. Konfiguriere SMTP in booking.php für E-Mail-Versand.
4. Stelle die Dateien auf einem Webserver bereit.

## Verwendung

- **index.php**: Startseite der Company Website
- **booking.php**: Für echte Buchungen (mit DB und E-Mail)
- **checkin.php**: Für echten Check-in (mit DB)
- **mock_booking.php**: Mock-Buchung mit Raum-Auswahl (8:00-20:00, verfügbare/belegte Räume mit Belegungszeiten, Dauer-Auswahl 1-8 Stunden)
- **mock_checkin.php**: Mock-Check-in ohne DB

## Logo

Das Logo wird als `logo.png` im Projektordner erwartet. Verwenden Sie Tools wie Canva, LogoMaker oder Hatchful, um ein cooles Logo zu erstellen. Ideen:
- "BookIT" mit einem Buch-Icon oder stilisiertem "B" und "IT".
- Farben: Blau (#007bff) für Vertrauen, vielleicht mit Akzenten.
- Speichern Sie es als PNG mit transparentem Hintergrund.

Wenn kein Logo vorhanden ist, wird der Text "BookIT" angezeigt.