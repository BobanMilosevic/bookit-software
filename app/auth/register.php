<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
  exit;
}

$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Bitte alle Felder ausfüllen.']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail-Adresse.']);
  exit;
}

if (mb_strlen($password) < 12
    || !preg_match('/[A-Z]/', $password)
    || !preg_match('/\d/', $password)
    || !preg_match('/[^A-Za-z0-9]/', $password)
) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Passwort muss mindestens 12 Zeichen lang sein und Großbuchstaben, Zahl und Sonderzeichen enthalten.']);
  exit;
}

$pdo = db();

// E-Mail schon vorhanden?
$stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetchColumn()) {
  http_response_code(409);
  echo json_encode(['ok' => false, 'error' => 'Diese E-Mail-Adresse ist bereits registriert.']);
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Erwartete Spalten: idusers (AI), email, passwort_hash, username
$insert = $pdo->prepare('INSERT INTO users (email, password_hash, username) VALUES (?, ?, ?)');
$insert->execute([$email, $hash, $name]);

$userId = (int)$pdo->lastInsertId();

header('Location: /login.php?error=' . urlencode('Registrierung fehlgeschlagen'));
// ✅ Erfolgreich registriert -> zurück zur Startseite
header('Location: /index.php');
exit;