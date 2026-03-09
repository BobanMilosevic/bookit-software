<?php
require_once __DIR__ . '/../../app/auth/bootstrap.php';

$loggedIn    = !empty($_SESSION['user_id']);
$userName    = $_SESSION['user_name'] ?? '';
$userEmail   = $_SESSION['user_email'] ?? '';
$displayName = $userName !== '' ? $userName : $userEmail;
?>

<nav class="bk-nav" id="bk-nav">
  <div class="bk-nav__inner">

    <!-- Brand -->
    <a class="bk-nav__brand" href="/index.php">
      <img src="/assets/img/logo.png" alt="BookIT Logo" class="bk-nav__logo">
      <span class="bk-nav__name">Book<span>IT</span></span>
    </a>

    <!-- Desktop links -->
    <ul class="bk-nav__links">
      <li><a href="/webshop.php">Webshop</a></li>
      <li><a href="/#demo">Demo</a></li>
      <li><a href="/about.php">Über uns</a></li>
      <li><a href="/internal-news.php">News</a></li>
      <li><a href="/#contact">Kontakt</a></li>
    </ul>

    <!-- Right actions -->
    <div class="bk-nav__actions">

      <!-- Cart -->
      <a class="bk-nav__cart" href="/cart.php" aria-label="Warenkorb">
        <i class="bi bi-bag"></i>
        <span class="bk-nav__cart-badge" id="cart-badge" style="display:none;">0</span>
      </a>

      <?php if ($loggedIn): ?>
        <!-- Dashboard Button -->
        <a class="bk-nav__btn bk-nav__btn--dashboard" href="dashboard.php">
          <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <!-- User chip -->
        <span class="bk-nav__user">
          <i class="bi bi-person-circle"></i>
          <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a class="bk-nav__btn bk-nav__btn--ghost" href="/auth/logout.php">Logout</a>
      <?php else: ?>
        <a class="bk-nav__btn bk-nav__btn--primary" href="/login.php">Login</a>
      <?php endif; ?>

      <!-- Hamburger -->
      <button class="bk-nav__hamburger" id="bk-hamburger" aria-label="Menü öffnen" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>

  <!-- Mobile drawer -->
  <div class="bk-nav__drawer" id="bk-drawer">
    <ul>
      <li><a href="/webshop.php">Webshop</a></li>
      <li><a href="/#demo">Demo</a></li>
      <li><a href="/about.php">Über uns</a></li>
      <li><a href="/internal-news.php">News</a></li>
      <li><a href="/#contact">Kontakt</a></li>
      <li><a href="/cart.php">Warenkorb</a></li>
      <?php if ($loggedIn): ?>
        <li><a href="/dashboard.php" class="bk-nav__drawer-dashboard"><i class="bi bi-grid-1x2"></i> Dashboard</a></li>
        <li><a href="/auth/logout.php" class="bk-nav__drawer-logout">Logout</a></li>
      <?php else: ?>
        <li><a href="/login.php" class="bk-nav__drawer-login">Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<style>
/* ══════════════════════════════════════════════════
   BookIT Navbar
   Sticky + scroll-aware + mobile drawer
   ══════════════════════════════════════════════════ */

/* ── STICKY: Diese 3 Zeilen machen die Navbar sticky ──────
   position: sticky  → klebt am oberen Rand beim Scrollen
   top: 0            → Abstand vom oberen Viewport-Rand
   z-index: 1000     → liegt über allem anderen Inhalt
   ──────────────────────────────────────────────────────── */
.bk-nav {
  position: sticky;
  top: 0;
  z-index: 1000;

  background: rgba(255, 255, 255, 0.97);
  border-bottom: 1px solid #e2e8f0;
  transition: box-shadow 0.25s ease, background 0.25s ease;
}

/* Schatten erscheint sobald man scrollt (via JS) */
.bk-nav.is-scrolled {
  box-shadow: 0 2px 16px rgba(30, 41, 59, 0.08);
}

.bk-nav__inner {
  max-width: 1160px;
  margin: 0 auto;
  padding: 0 2rem;
  height: 64px;
  display: flex;
  align-items: center;
  gap: 2rem;
}

/* ── Brand ────────────────────────────────────────── */
.bk-nav__brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  flex-shrink: 0;
}

.bk-nav__logo {
  height: 34px;
  width: auto;
  display: block;
}

.bk-nav__name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  letter-spacing: -0.02em;
  line-height: 1;
}

.bk-nav__name span {
  color: #118075;
}

/* ── Desktop links ────────────────────────────────── */
.bk-nav__links {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  list-style: none;
  margin: 0 auto 0 2rem;
  padding: 0;
}

.bk-nav__links a {
  display: block;
  padding: 0.4rem 0.7rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
  text-decoration: none;
  border-radius: 6px;
  transition: color 0.18s, background 0.18s;
}

.bk-nav__links a:hover {
  color: #118075;
  background: #e6f4f2;
}

/* ── Right actions ────────────────────────────────── */
.bk-nav__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

/* Cart icon */
.bk-nav__cart {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  color: #475569;
  text-decoration: none;
  font-size: 1.1rem;
  transition: color 0.18s, background 0.18s;
}

.bk-nav__cart:hover {
  color: #118075;
  background: #e6f4f2;
}

.bk-nav__cart-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  min-width: 16px;
  height: 16px;
  background: #dc2626;
  color: #fff;
  font-size: 0.6rem;
  font-weight: 700;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
  line-height: 1;
}

/* User chip */
.bk-nav__user {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: #64748b;
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.bk-nav__user i { color: #118075; font-size: 1rem; }

/* Buttons */
.bk-nav__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.42rem 1.1rem;
  border-radius: 7px;
  text-decoration: none;
  transition: all 0.2s ease;
  border: 1.5px solid transparent;
  white-space: nowrap;
}

.bk-nav__btn--primary {
  background: #118075;
  color: #fff;
  border-color: #118075;
}

.bk-nav__btn--primary:hover {
  background: #0d6560;
  border-color: #0d6560;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(17,128,117,0.3);
}

.bk-nav__btn--dashboard {
  background: var(--db-green-pale, #e6f4f2);
  color: #118075;
  border-color: rgba(17,128,117,.3);
}

.bk-nav__btn--dashboard:hover {
  background: #118075;
  color: #fff;
  border-color: #118075;
  transform: translateY(-1px);
}

.bk-nav__btn--ghost {
  background: transparent;
  color: #dc2626;
  border-color: #fca5a5;
}

.bk-nav__btn--ghost:hover {
  background: #fef2f2;
  border-color: #dc2626;
}

/* ── Hamburger ────────────────────────────────────── */
.bk-nav__hamburger {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 36px;
  height: 36px;
  padding: 6px;
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.18s;
}

.bk-nav__hamburger:hover { background: #f1f5f9; }

.bk-nav__hamburger span {
  display: block;
  height: 2px;
  background: #475569;
  border-radius: 2px;
  transition: transform 0.25s ease, opacity 0.25s ease;
}

/* Open state */
.bk-nav__hamburger.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.bk-nav__hamburger.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.bk-nav__hamburger.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ── Mobile drawer ────────────────────────────────── */
.bk-nav__drawer {
  display: none;
  border-top: 1px solid #e2e8f0;
  background: #fff;
  padding: 0.75rem 1.5rem 1.25rem;
}

.bk-nav__drawer ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.bk-nav__drawer a {
  display: block;
  padding: 0.6rem 0.5rem;
  font-size: 0.95rem;
  font-weight: 500;
  color: #334155;
  text-decoration: none;
  border-radius: 6px;
  transition: color 0.18s, background 0.18s;
}

.bk-nav__drawer a:hover { color: #118075; background: #e6f4f2; }

.bk-nav__drawer-login {
  color: #118075 !important;
  font-weight: 600 !important;
  margin-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
  padding-top: 0.75rem !important;
}

.bk-nav__drawer-dashboard {
  color: #118075 !important;
  font-weight: 600 !important;
}

.bk-nav__drawer-logout {
  color: #dc2626 !important;
  font-weight: 600 !important;
  margin-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
  padding-top: 0.75rem !important;
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 768px) {
  .bk-nav__links  { display: none; }
  .bk-nav__user   { display: none; }
  .bk-nav__btn    { display: none; }
  .bk-nav__hamburger { display: flex; }
  .bk-nav__drawer { display: block; }  /* shown/hidden via JS class below */
  .bk-nav__drawer:not(.is-open) { display: none; }
}
</style>

<script>
(function () {
  const nav       = document.getElementById('bk-nav');
  const hamburger = document.getElementById('bk-hamburger');
  const drawer    = document.getElementById('bk-drawer');

  /* ── Sticky scroll shadow ───────────────────────────────
     Fügt die Klasse "is-scrolled" hinzu sobald die Seite
     mehr als 10px gescrollt wurde → löst den Box-Shadow aus
  ──────────────────────────────────────────────────────── */
  window.addEventListener('scroll', function () {
    nav.classList.toggle('is-scrolled', window.scrollY > 10);
  }, { passive: true });

  /* ── Mobile hamburger toggle ────────────────────────── */
  hamburger.addEventListener('click', function () {
    const isOpen = drawer.classList.toggle('is-open');
    hamburger.classList.toggle('is-open', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen);
  });

  /* Drawer schließen wenn ein Link geklickt wird */
  drawer.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      drawer.classList.remove('is-open');
      hamburger.classList.remove('is-open');
      hamburger.setAttribute('aria-expanded', 'false');
    });
  });
})();
</script>