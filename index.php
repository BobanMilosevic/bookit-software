<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookIT - Raumverwaltung leicht gemacht</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #118075;
            --secondary-color: #4D8496;
            --accent-color: #80111B;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark-text); overflow-x: hidden; }
        .hero { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 120px 0; text-align: center; position: relative; overflow: hidden; transform: translateZ(0); }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.3); z-index: 1; }
        .hero > * { position: relative; z-index: 2; }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat; opacity: 0.1; animation: float 20s ease-in-out infinite; }
        .hero h1 { font-size: 4rem; font-weight: 700; margin-bottom: 1rem; animation: slideInFromTop 1s ease-out; }
        .hero p { font-size: 1.5rem; opacity: 0.9; animation: slideInFromBottom 1s ease-out 0.5s both; }
        .logo { font-size: 3em; font-weight: bold; margin-bottom: 20px; }
        .feature-card { margin: 20px 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: none; padding: 2rem; opacity: 0; transform: translateY(50px); transition: all 0.6s ease; }
        .feature-card.visible { opacity: 1; transform: translateY(0); }
        .feature-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .feature-card i { font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem; animation: bounceIn 1s ease-out; }
        .btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 12px; font-weight: 600; padding: 0.75rem 2rem; position: relative; overflow: hidden; }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s; }
        .btn:hover::before { left: 100%; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; }
        .pricing-card h5 { color: white; font-weight: 700; }
        .pricing-card.popular { border: 2px solid var(--primary-color); position: relative; }
        .pricing-card.popular::before { content: 'Beliebt'; position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--primary-color); color: white; padding: 0.25rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600; animation: pulse 2s infinite; }
        .product-card { margin: 20px 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: none; padding: 0; opacity: 0; transform: translateY(50px); transition: all 0.6s ease; }
        .product-card.visible { opacity: 1; transform: translateY(0); }
        .product-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .product-card.popular { border: 2px solid var(--secondary-color); position: relative; }
        footer { background: var(--dark-text); color: white; padding: 3rem 0; text-align: center; margin-top: 5rem; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeInUp 0.8s ease-out; }
        .section-title { font-size: 2.5rem; font-weight: 700; margin-bottom: 3rem; color: var(--dark-text); }
        @keyframes slideInFromTop { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideInFromBottom { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        @keyframes bounceIn { 0% { opacity: 0; transform: scale(0.3); } 50% { opacity: 1; transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { opacity: 1; transform: scale(1); } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        .parallax { background-attachment: fixed; background-size: cover; }
        .floating-shapes { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; overflow: hidden; }
        .shape { position: absolute; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 10s ease-in-out infinite; }
        .shape:nth-child(1) { width: 50px; height: 50px; top: 10%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { width: 30px; height: 30px; top: 20%; right: 10%; animation-delay: 2s; }
        .shape:nth-child(3) { width: 40px; height: 40px; bottom: 20%; left: 20%; animation-delay: 4s; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/navbar.php'; ?>
    <section class="hero parallax">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        <div class="container">
            <div style="padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo" style="max-width: 300px; display: block; margin: 0 auto; filter: drop-shadow(5px 5px 10px rgba(0,0,0,0.5));">
            </div>
            <h1>Raumverwaltung leicht gemacht</h1>
            <p class="lead">Entdecken Sie unsere Buchungssoftware für effiziente Raumverwaltung.</p>
            <a href="#demo" class="btn btn-light btn-lg">Demo ansehen</a>
        </div>
    </section>

    <section id="features" class="bg-light py-5 fade-in">
        <div class="container">
            <h2 class="text-center">Unsere Features</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-check"></i>
                            <h5 class="card-title">Online-Buchung</h5>
                            <p class="card-text">Buchen Sie Räume bequem von zu Hause aus.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="bi bi-envelope-check"></i>
                            <h5 class="card-title">E-Mail-Verifizierung</h5>
                            <p class="card-text">Erhalten Sie Ihren Code sicher per E-Mail.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <div class="card-body text-center">
                            <i class="bi bi-qr-code-scan"></i>
                            <h5 class="card-title">QR-Code Check-in</h5>
                            <p class="card-text">Scannen und Code eingeben vor Ort.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-5 bg-light fade-in">
        <div class="container">
            <h2 class="text-center mb-5">Unsere Abonnement-Optionen</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card h-100 pricing-card">
                        <div class="card-header text-center text-white" style="background-color: var(--primary-color);">
                            <h4>Basic</h4>
                            <h5>€49/Monat</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Bis zu 5 Räume</li>
                                <li>✓ Online-Buchung</li>
                                <li>✓ E-Mail-Verifizierung</li>
                                <li>✓ Basis-Support</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary add-to-cart" data-plan="basic" data-price="49">In den Warenkorb</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 pricing-card popular">
                        <div class="card-header text-center text-white" style="background-color: var(--primary-color);">
                            <h4>Pro</h4>
                            <h5>€199/Monat</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Bis zu 20 Räume</li>
                                <li>✓ Alle Basic-Features</li>
                                <li>✓ Erweiterte Berichte</li>
                                <li>✓ Prioritäts-Support</li>
                                <li>✓ Anpassbare E-Mails</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary add-to-cart" data-plan="pro" data-price="199">In den Warenkorb</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 pricing-card">
                        <div class="card-header text-center text-white" style="background-color: var(--primary-color);">
                            <h4>Enterprise</h4>
                            <h5>€299/Monat</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Unbegrenzte Räume</li>
                                <li>✓ Alle Pro-Features</li>
                                <li>✓ API-Zugang</li>
                                <li>✓ 24/7 Support</li>
                                <li>✓ Individuelle Anpassungen</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-success add-to-cart" data-plan="enterprise" data-price="299">In den Warenkorb</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p><strong>Zusatzoptionen:</strong></p>
                <p>✓ Komplette Website-Erstellung auf Anfrage: +€499 (einmalig)</p>
                <p>✓ Server-Hosting: +€9.99/Monat (oder bringen Sie Ihren eigenen Server mit: Mindestens 4GB RAM, 20GB SSD, PHP 8+, MySQL)</p>
            </div>
        </div>
    </section>

    <!-- Zusätzliche Produkte ausgeblendet - Webshop kommt später
    <section id="additional-products" class="py-5 bg-light fade-in">
        <div class="container">
            <h2 class="text-center mb-5">Zusätzliche Produkte</h2>
            
            <h3 class="text-center mb-4" style="color: var(--primary-color);">Hardware-Anforderungen</h3>
            <div class="row mb-5">
                <div class="col-md-4">
                    <div class="card h-100 product-card">
                        <div class="card-header text-center text-white" style="background-color: var(--secondary-color);">
                            <h5>Mindestanforderungen</h5>
                            <h6>€299</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Prozessor: ≥ 2 CPU-Kerne</li>
                                <li>✓ Arbeitsspeicher: ≥ 4 GB RAM</li>
                                <li>✓ Festplattenspeicher: ≥ 20 GB</li>
                                <li>✓ Basis-Server-Setup</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 product-card popular">
                        <div class="card-header text-center text-white" style="background-color: var(--secondary-color);">
                            <h5>Empfohlene Anforderungen</h5>
                            <h6>€499</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Prozessor: ≥ 4 CPU-Kerne</li>
                                <li>✓ Arbeitsspeicher: ≥ 8 GB RAM</li>
                                <li>✓ Festplattenspeicher: ≥ 50 GB</li>
                                <li>✓ Erweiterte Server-Leistung</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 product-card">
                        <div class="card-header text-center text-white" style="background-color: var(--secondary-color);">
                            <h5>Premium Anforderungen</h5>
                            <h6>€799</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li>✓ Prozessor: ≥ 8 CPU-Kerne</li>
                                <li>✓ Arbeitsspeicher: ≥ 16 GB RAM</li>
                                <li>✓ Festplattenspeicher: ≥ 100 GB</li>
                                <li>✓ Hochleistungs-Server</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <h3 class="text-center mb-4" style="color: var(--primary-color);">BookIT Merchandise</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card h-100 product-card">
                        <div class="card-header text-center text-white" style="background-color: var(--accent-color);">
                            <h5>T-Shirt</h5>
                            <h6>€19.99</h6>
                        </div>
                        <div class="card-body text-center">
                            <i class="bi bi-shirt" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <p class="card-text">Offizielles BookIT T-Shirt in verschiedenen Größen.</p>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 product-card">
                        <div class="card-header text-center text-white" style="background-color: var(--accent-color);">
                            <h5>Kappe</h5>
                            <h6>€14.99</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="card-text">Stylische BookIT Kappe für den Alltag.</p>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 product-card">
                        <div class="card-header text-center text-white" style="background-color: var(--accent-color);">
                            <h5>Rucksack</h5>
                            <h6>€24.99</h6>
                        </div>
                        <div class="card-body text-center">
                            <i class="bi bi-backpack" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <p class="card-text">Praktischer BookIT Rucksack für unterwegs.</p>
                        </div>
                        <div class="card-footer text-center">
                            <button class="btn btn-primary" onclick="showComingSoon()">Demo anfordern</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Ende zusätzliche Produkte -->
        <div class="container">
            <h2 class="text-center">Demo unseres Produkts</h2>
            <p class="text-center">Erleben Sie BookIT in Aktion – Buchen Sie einen Raum und checken Sie ein (simuliert).</p>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Schritt 1: Buchung</h5>
                            <p class="card-text">Benutzer buchen online einen Raum und erhalten einen Verifizierungscode per E-Mail.</p>
                            <a href="mock_booking.php" class="btn btn-primary">Demo Buchung</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Schritt 2: Check-in</h5>
                            <p class="card-text">Vor Ort QR-Code scannen und Code eingeben für Verifizierung.</p>
                            <a href="mock_checkin.php" class="btn btn-primary">Demo Check-in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="container my-5 fade-in">
        <h2 class="text-center">Kontaktieren Sie uns</h2>
        <p class="text-center">Interessiert an BookIT? Schreiben Sie uns!</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-Mail</label>
                        <input type="email" class="form-control" id="email" required>
                        <div id="email-feedback" class="invalid-feedback">
                            Bitte geben Sie eine gültige E-Mail-Adresse ein.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Nachricht</label>
                        <textarea class="form-control" id="message" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Senden</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten. | <a href="about.php">About Us</a> | <a href="impressum.php">Impressum</a> | <a href="#contact">Kontakt</a></p>
        </div>
    </footer>

    <!-- Modal für Warenkorb-Aktion -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Artikel hinzugefügt!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
                    <p>Ihr Abonnement-Plan wurde erfolgreich ausgewählt!</p>
                    <p class="text-muted">Der separate Webshop kommt bald!</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <a href="cart.php" class="btn btn-primary">Zum Warenkorb</a>
                    <a href="mock_booking.php" class="btn btn-outline-primary">Demo buchen</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Schließen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.feature-card, .pricing-card, .product-card').forEach(card => {
            observer.observe(card);
        });

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            
            // Blur effect on scroll - starts after 200px scroll
            const blurStart = 200;
            const blurAmount = scrolled > blurStart ? Math.min((scrolled - blurStart) * 0.02, 5) : 0;
            
            // Darken effect on scroll instead of opacity
            const darkenStart = 300;
            const darkenAmount = scrolled > darkenStart ? Math.min((scrolled - darkenStart) * 0.005, 0.7) : 0;
            
            hero.style.filter = `blur(${blurAmount}px) brightness(${1 - darkenAmount})`;
        });

        // Smooth scrolling for nav links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Shopping Cart functionality
        let cart = JSON.parse(localStorage.getItem('bookit_cart')) || [];
        const cartBadge = document.getElementById('cart-badge');

        function updateCartBadge() {
            const totalItems = cart.length;
            if (totalItems > 0) {
                cartBadge.textContent = totalItems;
                cartBadge.style.display = 'inline-block';
            } else {
                cartBadge.style.display = 'none';
            }
        }

        function updatePlanButtons() {
            // Reset all buttons
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.textContent = 'In den Warenkorb';
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
            });

            // Highlight selected plan
            if (cart.length > 0) {
                const selectedPlan = cart[0].plan;
                const selectedButton = document.querySelector(`.add-to-cart[data-plan="${selectedPlan}"]`);
                if (selectedButton) {
                    selectedButton.textContent = 'Ausgewählt';
                    selectedButton.classList.remove('btn-primary');
                    selectedButton.classList.add('btn-success');
                }
            }
        }

        function addToCart(plan, price) {
            // Remove any existing plan
            cart = [];

            // Add new plan
            cart.push({ plan, price: parseFloat(price) });
            localStorage.setItem('bookit_cart', JSON.stringify(cart));
            updateCartBadge();
            updatePlanButtons();

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('cartModal'));
            modal.show();
        }

        // Add event listeners to cart buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const plan = this.getAttribute('data-plan');
                    const price = this.getAttribute('data-price');
                    addToCart(plan, price);
                });
            });
        });

        // Initialize cart badge and buttons on page load
        updateCartBadge();
        updatePlanButtons();

        // Coming Soon functionality
        function showComingSoon() {
            alert('Der separate Webshop kommt bald! In der Zwischenzeit können Sie gerne eine Demo bei uns anfordern.');
        }

        // E-Mail validation
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const emailFeedback = document.getElementById('email-feedback');
            const contactForm = document.querySelector('#contact form');

            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }

            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    if (this.value === '') {
                        this.classList.remove('is-valid', 'is-invalid');
                        if (emailFeedback) emailFeedback.style.display = 'none';
                    } else if (validateEmail(this.value)) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        if (emailFeedback) emailFeedback.style.display = 'none';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        if (emailFeedback) emailFeedback.style.display = 'block';
                    }
                });
            }

            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!validateEmail(emailInput.value)) {
                        emailInput.classList.add('is-invalid');
                        if (emailFeedback) emailFeedback.style.display = 'block';
                        emailInput.focus();
                        return false;
                    }
                    // Hier könnte man das Formular absenden
                    alert('Vielen Dank für Ihre Nachricht! Wir melden uns bald bei Ihnen.');
                    this.reset();
                    emailInput.classList.remove('is-valid', 'is-invalid');
                    if (emailFeedback) emailFeedback.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>