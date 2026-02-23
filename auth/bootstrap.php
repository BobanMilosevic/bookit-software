<?php
// auth/bootstrap.php
declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

session_set_cookie_params([
  'secure' => (bool)$config['session']['cookie_secure'],
  'httponly' => (bool)$config['session']['cookie_httponly'],
  'samesite' => $config['session']['cookie_samesite'],
]);

session_start();