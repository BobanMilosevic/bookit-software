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

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($email === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Bitte E-Mail und Passwort eingeben.']);
  exit;
}

$pdo = db();

$stmt = $pdo->prepare('SELECT idusers, email, passwort_hash, username FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['passwort_hash'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail oder Passwort.']);
  exit;
}

// Session sicher machen
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['idusers'];
$_SESSION['user_email'] = (string)$user['email'];
$_SESSION['user_name'] = (string)($user['username'] ?? 'User');

echo json_encode(['ok' => true]);