<?php
declare(strict_types=1);

/**
 * Mail-Konfiguration für PHPMailer
 */
return [
    'host' => getenv('MAIL_HOST') ?: 'smtp-relay.brevo.com',
    'port' => getenv('MAIL_PORT') ?: 587,
    'username' => getenv('MAIL_USERNAME') ?: 'a3c074001@smtp-brevo.com',
    'password' => getenv('MAIL_PASSWORD') ?: 'M7hawr3EnJCPv9Ng',
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'sabs38@gmail.com',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'BookIT'
];
