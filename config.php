<?php
// config.php
declare(strict_types=1);

return [
  'db' => [
    'host' => 'php.sylyx.xyz',
    'name' => 'bookit_db',
    'user' => 'root',
    'pass' => 'root',
    'charset' => 'utf8mb4',
  ],

  // Session/Cookie Settings (für Login sehr wichtig)
  'session' => [
    'cookie_secure' => true, // auf true setzen, wenn HTTPS
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
  ],
];