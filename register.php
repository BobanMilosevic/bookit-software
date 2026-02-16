<?php
declare(strict_types=1);
require __DIR__ . "/db.php";
session_start();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($name === '' || $email === '' || strlen($password) < 6) {
  http_response_code(422);
  exit("Ungültige Eingaben.");
}

$username = strtolower(preg_replace('/\s+/', '.', $name)); // simple username
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO `users` (`email`,`password_hash`,`username`) VALUES (?,?,?)");
try {
  $stmt->execute([$email, $hash, $username]);
} catch (PDOException $e) {
  http_response_code(409);
  exit("E-Mail/Username bereits vergeben.");
}

$_SESSION['user_id'] = (int)$pdo->lastInsertId();
header("Location: /dashboard.php");
exit;
