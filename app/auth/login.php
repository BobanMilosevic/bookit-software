<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../db.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));
$pass  = (string)($_POST['password'] ?? '');

if ($email === '' || $pass === '') {
    header('Location: /login.php?error=' . urlencode('Bitte E-Mail und Passwort eingeben.'));
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        u.idusers,
        u.email,
        u.username,
        u.password_hash,
        CASE
            WHEN EXISTS (
                SELECT 1
                FROM users_has_Rollen uhr
                INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                WHERE uhr.users_idusers = u.idusers
                  AND r.Rollenname = 'admin'
            ) THEN 'admin'
            WHEN EXISTS (
                SELECT 1
                FROM users_has_Rollen uhr
                INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                WHERE uhr.users_idusers = u.idusers
                  AND r.Rollenname = 'employee'
            ) THEN 'employee'
            ELSE 'customer'
        END AS role
    FROM users u
    WHERE u.email = :email
    LIMIT 1
");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$hash = (string)($user['password_hash'] ?? '');

if (!$user || $hash === '' || !password_verify($pass, $hash)) {
    header('Location: /login.php?error=' . urlencode('Login fehlgeschlagen.'));
    exit;
}

// Session keys einheitlich setzen
$_SESSION['user_id'] = (int)$user['idusers'];
$_SESSION['user_email'] = (string)($user['email'] ?? '');
$_SESSION['user_name'] = (string)($user['username'] ?? '');
$_SESSION['user_role'] = (string)($user['role'] ?? 'customer');

session_regenerate_id(true);

header('Location: /dashboard.php');
exit;