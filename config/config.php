<?php
declare(strict_types=1);

/**
 * Zentrale Konfiguration.
 * - In Produktion bitte via ENV Variablen setzen (z.B. Apache/Nginx, Docker, .env Loader).
 * - Lokal kannst du optional config/local.php anlegen (nicht einchecken).
 */
$localFile = __DIR__ . '/local.php';
$local = file_exists($localFile) ? (require $localFile) : [];

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);

return array_replace_recursive([
  'app' => [
    'base_path' => dirname(__DIR__),
    'public_path' => dirname(__DIR__) . '/public',
  ],
  'db' => [
    'host' => getenv('DB_HOST') ?: 'php.sylyx.xyz',
    'name' => getenv('DB_NAME') ?: 'bookit_db',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: 'UTn_rHX7(wLDDA:=',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
  ],
  'session' => [
    'cookie_secure' => $https,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_path' => '/',
  ],
], is_array($local) ? $local : []);
