<?php
declare(strict_types=1);
require __DIR__ . '/../app/auth/bootstrap.php';

$loggedIn = !empty($_SESSION['user_id']);
$displayName = ($_SESSION['user_name'] ?? '') !== '' ? $_SESSION['user_name'] : ($_SESSION['user_email'] ?? '');

/**
 * Optional: Flash messages via query string:
 *   login.php?success=... or login.php?error=...
 */
$success = isset($_GET['success']) ? (string) $_GET['success'] : '';
$error = isset($_GET['error']) ? (string) $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - BookIT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #118075;
      --secondary-color: #4D8496;
      --light-bg: #f8fafc;
      --dark-text: #1e293b;
    }

    body {
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 20px 45px rgba(0, 0, 0, .18);
      padding: 28px;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 18px;
      color: var(--primary-color);
      font-weight: 800;
      font-size: 28px;
      letter-spacing: .2px;
    }

    .logo i {
      font-size: 30px;
    }

    .links {
      margin-top: 14px;
      text-align: center;
    }

    .links a {
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background: #0e6a62;
      border-color: #0e6a62;
    }
  </style>
</head>

<body>

  <div class="login-container">
    <div class="logo">
      <i class="bi bi-calendar-check"></i>
      <span>BookIT</span>
    </div>

    <?php if ($success !== ''): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($loggedIn): ?>
      <div class="alert alert-success">
        ✅ Eingeloggt als <strong>
          <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
        </strong>
        <div class="mt-2 d-flex gap-2">
          <a class="btn btn-primary btn-sm" href="/dashboard.php">Zum Dashboard</a>
          <a class="btn btn-outline-danger btn-sm" href="/auth/logout.php">Logout</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- LOGIN (server-side) -->
    <form method="post" action="/auth/login.php" class="mt-2">
      <div class="mb-3">
        <label for="email" class="form-label">E-Mail</label>
        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Passwort</label>
        <input type="password" class="form-control" id="password" name="password" required
          autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary w-100">Anmelden</button>
    </form>

    <div class="links">
      <!-- No JS needed; Bootstrap handles opening the modal -->
      <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Noch kein Konto? Registrieren</a><br>
      <a href="index.php">Zurück zur Startseite</a>
    </div>
  </div>

  <!-- REGISTER MODAL (server-side) -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Registrieren</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <form method="post" action="auth/register.php" id="registerForm">
            <div class="mb-3">
              <label for="regName" class="form-label">Vollständiger Name</label>
              <input type="text" class="form-control" id="regName" name="name" required autocomplete="name">
            </div>
            <div class="mb-3">
              <label for="regEmail" class="form-label">E-Mail</label>
              <input type="email" class="form-control" id="regEmail" name="email" required autocomplete="email">
            </div>
            <div class="mb-3">
              <label for="regPassword" class="form-label">Passwort</label>
              <input type="password" class="form-control" id="regPassword" name="password" required minlength="6"
                autocomplete="new-password">
              <div class="form-text">min. 12 Zeichen,min 1 Groß- und Kleinuchstaben, min 1 Sonderzeichen</div>
            </div>
            <div class="mb-3">
              <label for="regConfirmPassword" class="form-label">Passwort bestätigen</label>
              <input type="password" class="form-control" id="regConfirmPassword" required minlength="6"
                autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary w-100">Registrieren</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Kleine Client-Validierung, damit "Registrieren" nicht scheinbar "nichts macht"
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      const pw = document.getElementById('regPassword').value;
      const pw2 = document.getElementById('regConfirmPassword').value;
      if (pw !== pw2) {
        e.preventDefault();
        alert('Passwörter stimmen nicht überein.');
      }
    });
  </script>
</body>

</html>