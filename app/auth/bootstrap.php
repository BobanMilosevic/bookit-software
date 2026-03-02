<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    // Für VirtualHost (bookit.local) MUSS path "/" sein
    session_name('bookit_sid');

    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} 