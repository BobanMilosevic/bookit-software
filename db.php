<?php
declare(strict_types=1);

$pdo = new PDO(
  "mysql:host=https://php.sylyx.xyz/;dbname=bookit_software_gruppeMAHAMI;charset=utf8mb4",
  "root",
  "root",
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]
);
