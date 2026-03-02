<?php
require_once __DIR__ . '/../../app/auth/bootstrap.php';

$loggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$displayName = $userName !== '' ? $userName : $userEmail;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="/index.php">
      <img src="/assets/img/logo.png" alt="BookIT Logo" class="me-2" style="height:40px;">
      <span class="fw-bold text-primary">BookIT</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="/#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="/#pricing">Preise</a></li>
        <li class="nav-item"><a class="nav-link" href="/webshop.php">Webshop</a></li>
        <li class="nav-item"><a class="nav-link" href="/#demo">Demo</a></li>
        <li class="nav-item"><a class="nav-link" href="/about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link" href="/internal-news.php">News</a></li>
        <li class="nav-item"><a class="nav-link" href="/#contact">Kontakt</a></li>

        <li class="nav-item ms-lg-2">
          <?php if ($loggedIn): ?>
            <a class="btn btn-outline-danger btn-sm" href="/auth/logout.php">Logout</a>
          <?php else: ?>
            <a class="btn btn-primary btn-sm" href="/login.php">Login</a>
          <?php endif; ?>
        </li>

        <li class="nav-item ms-lg-2">
          <a class="nav-link position-relative" href="/cart.php">
            <i class="bi bi-cart3"></i>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                  id="cart-badge" style="display:none;">0</span>
          </a>
        </li>

        <?php if ($loggedIn): ?>
          <li class="nav-item ms-lg-2">
            <span class="navbar-text small text-muted">
            Eingeloggt als <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>