<?php
require __DIR__ . '/../app/auth/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BookIT – Raumverwaltung leichtxx gemacht</title>

  <!-- Bootstrap (grid only) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <!-- Page stylesheet -->
  <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>

  <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

  <!-- ═══════════════════════════════════════════════════
       HERO
  ════════════════════════════════════════════════════ -->
  <section class="hero">
    <div class="container">

      <h1 class="hero__h1">Raumverwaltung leicht gemacht</h1>

      <p class="hero__sub">
        Entdecken Sie unsere Buchungssoftware für effiziente Raumverwaltung —
        von der Online-Buchung bis zum QR-Code-Check-in.
      </p>

      <div class="hero__actions">
        <a href="#pricing" class="btn btn--primary">
          Pakete ansehen
        </a>
        <a href="#demo" class="btn btn--outline-white">
          Demo ansehen
        </a>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════
       STATS STRIPE
  ════════════════════════════════════════════════════ -->
  <div class="stats-stripe">
    <div class="container">
      <div class="stats-stripe__inner">
        <div class="stats-stripe__item">
          <span class="stats-stripe__value"><span>3</span></span>
          <span class="stats-stripe__label">Abo-Pakete</span>
        </div>
        <div class="stats-stripe__divider"></div>
        <div class="stats-stripe__item">
          <span class="stats-stripe__value">∞</span>
          <span class="stats-stripe__label">Räume (Enterprise)</span>
        </div>
        <div class="stats-stripe__divider"></div>
        <div class="stats-stripe__item">
          <span class="stats-stripe__value">24<span>/7</span></span>
          <span class="stats-stripe__label">Enterprise-Support</span>
        </div>
        <div class="stats-stripe__divider"></div>
        <div class="stats-stripe__item">
          <span class="stats-stripe__value"><span>€49</span></span>
          <span class="stats-stripe__label">Ab / Monat</span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════
       FEATURES
  ════════════════════════════════════════════════════ -->
  <section id="features" class="features">
    <div class="container">

      <div class="section-header">
        <div class="section-header__left">
          <div class="section-num">01 — Features</div>
          <h2 class="section-title">Alles, was Sie<br>wirklich brauchen.</h2>
        </div>
        <div class="section-header__right reveal reveal-delay-1">
          Drei Kernfunktionen, die Ihren Buchungsalltag
          vollständig abdecken — ohne unnötige Komplexität.
        </div>
      </div>

      <ul class="feature-list">

        <li class="feature-item reveal">
          <div class="feature-item__icon">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div class="feature-item__body">
            <h3>Online-Buchung</h3>
            <p>Räume bequem von überall buchen — 24 Stunden am Tag,
               ohne Telefon oder E-Mail-Hin-und-Her. Echtzeit-Verfügbarkeit,
               sofortige Bestätigung.</p>
          </div>
          <div class="feature-item__num">01</div>
        </li>

        <li class="feature-item reveal reveal-delay-1">
          <div class="feature-item__icon">
            <i class="bi bi-envelope-check"></i>
          </div>
          <div class="feature-item__body">
            <h3>E-Mail-Verifizierung</h3>
            <p>Sicherer Buchungscode per E-Mail. Nur verifizierte Nutzer
               erhalten Zugang — kein Passwort, kein Aufwand,
               maximale Sicherheit.</p>
          </div>
          <div class="feature-item__num">02</div>
        </li>

        <li class="feature-item reveal reveal-delay-2">
          <div class="feature-item__icon">
            <i class="bi bi-qr-code-scan"></i>
          </div>
          <div class="feature-item__body">
            <h3>QR-Code Check-in</h3>
            <p>QR-Code scannen, Code eingeben — fertig. Der Check-in
               dauert Sekunden und funktioniert komplett ohne
               zusätzliche Hardware.</p>
          </div>
          <div class="feature-item__num">03</div>
        </li>

      </ul>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════
       PRICING
  ════════════════════════════════════════════════════ -->
  <section id="pricing" class="pricing">
    <div class="container">

      <div class="section-header">
        <div class="section-header__left">
          <div class="section-num">02 — Preise</div>
          <h2 class="section-title">Transparent.<br>Keine Überraschungen.</h2>
        </div>
        <div class="section-header__right reveal reveal-delay-1">
          Wählen Sie das Paket, das zu Ihrer Organisation passt.
          Jederzeit wechselbar, monatlich kündbar.
        </div>
      </div>

      <div class="pricing-grid">

        <!-- Basic -->
        <div class="pricing-card reveal">
          <div class="pricing-card__head">
            <div class="pricing-card__plan">Basic</div>
            <div class="pricing-card__price">
              <span class="currency">€</span>
              <span class="amount">49</span>
              <span class="period">/Monat</span>
            </div>
          </div>
          <div class="pricing-card__body">
            <ul class="pricing-card__features">
              <li>Bis zu 5 Räume</li>
              <li>Online-Buchung</li>
              <li>E-Mail-Verifizierung</li>
              <li>Basis-Support</li>
            </ul>
          </div>
          <div class="pricing-card__foot">
            <button class="btn btn--ghost add-to-cart" data-plan="basic" data-price="49">
              In den Warenkorb
            </button>
          </div>
        </div>

        <!-- Pro -->
        <div class="pricing-card pricing-card--popular reveal reveal-delay-1">
          <div class="pricing-card__popular-badge">Beliebt</div>
          <div class="pricing-card__head">
            <div class="pricing-card__plan">Pro</div>
            <div class="pricing-card__price">
              <span class="currency">€</span>
              <span class="amount">199</span>
              <span class="period">/Monat</span>
            </div>
          </div>
          <div class="pricing-card__body">
            <ul class="pricing-card__features">
              <li>Bis zu 20 Räume</li>
              <li>Alle Basic-Features</li>
              <li>Erweiterte Berichte</li>
              <li>Prioritäts-Support</li>
              <li>Anpassbare E-Mails</li>
            </ul>
          </div>
          <div class="pricing-card__foot">
            <button class="btn btn--primary add-to-cart" data-plan="pro" data-price="199">
              In den Warenkorb
            </button>
          </div>
        </div>

        <!-- Enterprise -->
        <div class="pricing-card reveal reveal-delay-2">
          <div class="pricing-card__head">
            <div class="pricing-card__plan">Enterprise</div>
            <div class="pricing-card__price">
              <span class="currency">€</span>
              <span class="amount">299</span>
              <span class="period">/Monat</span>
            </div>
          </div>
          <div class="pricing-card__body">
            <ul class="pricing-card__features">
              <li>Unbegrenzte Räume</li>
              <li>Alle Pro-Features</li>
              <li>API-Zugang</li>
              <li>24/7 Support</li>
              <li>Individuelle Anpassungen</li>
            </ul>
          </div>
          <div class="pricing-card__foot">
            <button class="btn btn--ghost add-to-cart" data-plan="enterprise" data-price="299">
              In den Warenkorb
            </button>
          </div>
        </div>

      </div>

      <!-- Addons -->
      <div class="pricing__addons reveal">
        <div>
          <h4>Zusatzoptionen</h4>
          <p style="font-size:.85rem; color:var(--ink-60); margin:0;">Individuell zubuchbar zu jedem Paket</p>
        </div>
        <div class="pricing__addon-items">
          <div class="pricing__addon-item">
            <span class="label">Website-Erstellung</span>
            <span class="value">+ €499 <span style="font-size:.75rem; font-weight:400; color:var(--ink-60);">einmalig</span></span>
          </div>
          <div class="pricing__addon-item">
            <span class="label">Server-Hosting</span>
            <span class="value">+ €9,99 <span style="font-size:.75rem; font-weight:400; color:var(--ink-60);">/Monat</span></span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════
       CONTACT
  ════════════════════════════════════════════════════ -->
  <section id="contact" class="contact">
    <div class="container">
      <div class="contact__grid">

        <div class="contact__left reveal">
          <span class="tag" style="margin-bottom:1.4rem;">03 — Kontakt</span>
          <h2>Interesse?<br>Schreiben Sie uns.</h2>
          <p>Wir beantworten alle Fragen rund um BookIT —
             Preise, technische Details oder individuelle Anpassungen.</p>
          <div class="contact__info">
            <div class="contact__info-item">
              <i class="bi bi-envelope"></i>
              <span>hallo@bookit.at</span>
            </div>
            <div class="contact__info-item">
              <i class="bi bi-geo-alt"></i>
              <span>Österreich</span>
            </div>
          </div>
        </div>

        <div class="reveal reveal-delay-1">
          <form id="contact-form">
            <div class="form-group">
              <label class="form-label" for="name">Name</label>
              <input type="text" class="form-control" id="name" placeholder="Max Mustermann">
            </div>
            <div class="form-group">
              <label class="form-label" for="email">E-Mail</label>
              <input type="email" class="form-control" id="email" required placeholder="max@firma.at">
              <div id="email-feedback" class="invalid-feedback">
                Bitte geben Sie eine gültige E-Mail-Adresse ein.
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="message">Nachricht</label>
              <textarea class="form-control" id="message" rows="4" placeholder="Wie können wir helfen?"></textarea>
            </div>
            <button type="submit" class="btn btn--primary" style="width:100%; justify-content:center;">
              <i class="bi bi-send"></i>
              Nachricht senden
            </button>
          </form>
        </div>

      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════
       FOOTER
  ════════════════════════════════════════════════════ -->
  <footer>
    <div class="container">
      <p>
        &copy; 2026 BookIT. Alle Rechte vorbehalten.
        &nbsp;·&nbsp; <a href="about.php">Über uns</a>
        &nbsp;·&nbsp; <a href="impressum.php">Impressum</a>
        &nbsp;·&nbsp; <a href="#contact">Kontakt</a>
      </p>
    </div>
  </footer>

  <!-- ═══════════════════════════════════════════════════
       MODAL — Warenkorb
  ════════════════════════════════════════════════════ -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel"
              style="font-family:'Fraunces',serif; font-weight:700; letter-spacing:-.02em;">
            Hinzugefügt!
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body text-center" style="padding:2rem;">
          <i class="bi bi-check-circle-fill" style="font-size:3rem; color:var(--green); display:block; margin-bottom:1rem;"></i>
          <p style="color:var(--ink-60); font-size:.92rem;">Ihr Abonnement-Plan wurde erfolgreich ausgewählt.</p>
        </div>
        <div class="modal-footer" style="justify-content:center; gap:.75rem;">
          <a href="cart.php" class="btn btn--primary">Zum Warenkorb</a>
          <button type="button" class="btn btn--ghost" data-bs-dismiss="modal">Weiter stöbern</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    /* ── Scroll-reveal ──────────────────────────────── */
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          revealObserver.unobserve(e.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    /* ── Smooth scroll ──────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    /* ── Shopping Cart ──────────────────────────────── */
    let cart = JSON.parse(localStorage.getItem('bookit_cart') || '[]');
    const cartBadge = document.getElementById('cart-badge');

    function updateCartBadge() {
      if (!cartBadge) return;
      cartBadge.textContent  = cart.length;
      cartBadge.style.display = cart.length > 0 ? 'inline-block' : 'none';
    }

    function updatePlanButtons() {
      document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.textContent = 'In den Warenkorb';
        btn.classList.remove('btn--primary');
        btn.classList.add('btn--ghost');
      });
      if (cart.length > 0) {
        const sel = document.querySelector(`.add-to-cart[data-plan="${cart[0].plan}"]`);
        if (sel) {
          sel.textContent = '✓ Ausgewählt';
          sel.classList.remove('btn--ghost');
          sel.classList.add('btn--primary');
        }
      }
    }

    function addToCart(plan, price) {
      cart = [{ plan, price: parseFloat(price) }];
      localStorage.setItem('bookit_cart', JSON.stringify(cart));
      updateCartBadge();
      updatePlanButtons();
      new bootstrap.Modal(document.getElementById('cartModal')).show();
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => addToCart(btn.dataset.plan, btn.dataset.price));
      });
    });

    updateCartBadge();
    updatePlanButtons();

    /* ── Email validation ───────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
      const emailInput    = document.getElementById('email');
      const emailFeedback = document.getElementById('email-feedback');
      const form          = document.getElementById('contact-form');
      const isValid = v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);

      if (emailInput) {
        emailInput.addEventListener('input', function () {
          if (!this.value) {
            this.classList.remove('is-valid', 'is-invalid');
            if (emailFeedback) emailFeedback.style.display = 'none';
          } else if (isValid(this.value)) {
            this.classList.replace('is-invalid', 'is-valid') || this.classList.add('is-valid');
            if (emailFeedback) emailFeedback.style.display = 'none';
          } else {
            this.classList.replace('is-valid', 'is-invalid') || this.classList.add('is-invalid');
            if (emailFeedback) emailFeedback.style.display = 'block';
          }
        });
      }

      if (form) {
        form.addEventListener('submit', e => {
          e.preventDefault();
          if (!isValid(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            if (emailFeedback) emailFeedback.style.display = 'block';
            emailInput.focus();
            return;
          }
          alert('Vielen Dank für Ihre Nachricht! Wir melden uns bald.');
          form.reset();
          emailInput.classList.remove('is-valid', 'is-invalid');
          if (emailFeedback) emailFeedback.style.display = 'none';
        });
      }
    });
  </script>

</body>
</html>