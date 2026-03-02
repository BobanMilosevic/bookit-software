# BookIT (Refactored) – Raumverwaltung & Webshop (PHP)

## Ordnerstruktur

- `public/` – **DocumentRoot** (alle aufrufbaren Seiten + Assets)
  - `auth/` – HTTP Endpoints (login/register/logout) als Wrapper
  - `assets/` – CSS/JS/Images
- `app/` – Anwendungslogik (DB, Auth)
- `config/` – Konfiguration (ENV/Local)
- `views/` – Partials/Templates
- `storage/` – Logs/Uploads (nicht öffentlich)

## Setup (XAMPP)

1. Projekt ins `htdocs` legen.
2. **Empfohlen:** VirtualHost/DocumentRoot auf `public/` setzen  
   (Alternativ: Aufrufen über `.../public/index.php`).

3. Composer:
   ```bash
   composer install
   ```

4. DB Zugangsdaten setzen:
   - via ENV: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - oder lokal `config/local.php` anhand `config/local.sample.php` erstellen.

## Datenbank (Minimal-Schema)

### Users (Login)
```sql
CREATE TABLE users (
  idusers INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  passwort_hash VARCHAR(255) NOT NULL,
  username VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Kategorien + Artikel (Webshop)
```sql
CREATE TABLE Kategorien (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE Artikel (
  Artikelnummer INT PRIMARY KEY,
  Bezeichnung VARCHAR(255) NOT NULL,
  Preis DECIMAL(10,2) NOT NULL,
  Waehrung VARCHAR(10) NOT NULL DEFAULT 'EUR',
  Stueckzahl INT NOT NULL DEFAULT 0,
  kategorie_id INT NOT NULL,
  CONSTRAINT fk_artikel_kategorie
    FOREIGN KEY (kategorie_id) REFERENCES Kategorien(id)
);
```

## Security Notes

- `config/local.php` **nicht** committen (Passwörter/Secrets).
- Passwort-Policy in `app/auth/register.php`: min. 12 Zeichen + Großbuchstabe + Zahl + Sonderzeichen.
