<?php
require __DIR__ . '/../app/auth/bootstrap.php';

require __DIR__ . '/../app/auth/require_login.php';

// Lese User-Daten aus Session
$user_name = $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Benutzer';
$user_email = $_SESSION['user_email'] ?? 'keine@email.de';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warenkorb - BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #118075;
            --secondary-color: #4D8496;
            --accent-color: #80111B;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark-text); }
        .cart-item { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1rem; padding: 1.5rem; }
        .cart-total { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 1.5rem; margin-top: 2rem; }
        .btn { transition: all 0.3s ease; border-radius: 8px; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; }
        .empty-cart { text-align: center; padding: 3rem; color: #6b7280; }
        .plan-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600; }
        .plan-basic { background: #e5e7eb; color: #374151; }
        .plan-pro { background: var(--primary-color); color: white; }
        .plan-enterprise { background: #059669; color: white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="logo.png" alt="BookIT Logo" style="height: 40px; margin-right: 10px;">
                <span style="font-weight: 700; color: var(--primary-color);">BookIT</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#pricing">Preise</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#demo">Demo</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Kontakt</a></li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart3"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" id="cart-badge" style="display: none;">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="text-center mb-4">Ihr Warenkorb</h1>
        
        <div id="cart-items">
            <!-- Cart items will be loaded here -->
        </div>
        
        <!-- Zusatzservices Section -->
        <div id="additional-services" class="cart-item" style="display: none;">
            <h4 style="color: var(--primary-color);">Zusatzservices</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="website-service" onchange="toggleService('website', 499)">
                        <label class="form-check-label" for="website-service">
                            <strong>Komplette Website-Erstellung</strong><br>
                            <small class="text-muted">Professionelle Website-Erstellung für Ihr Unternehmen</small><br>
                            <span class="price">€499 (einmalig)</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="hosting-service" onchange="toggleService('hosting', 9.99)">
                        <label class="form-check-label" for="hosting-service">
                            <strong>Server-Hosting</strong><br>
                            <small class="text-muted">Professionelles Hosting für Ihre BookIT-Installation</small><br>
                            <span class="price">€9.99/Monat</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="cart-total" class="cart-total" style="display: none;">
            <div class="row">
                <div class="col-md-8">
                    <h4>Gesamt: <span id="total-price">€0.00</span>/Monat</h4>
                    <p class="text-muted">Alle Preise verstehen sich zzgl. MwSt.</p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary btn-lg me-2" onclick="proceedToCheckout()">Zur Kasse</button>
                    <button class="btn btn-outline-secondary" onclick="clearCart()">Warenkorb leeren</button>
                </div>
            </div>
        </div>
        
        <div id="empty-cart" class="empty-cart">
            <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
            <h3>Ihr Warenkorb ist leer</h3>
            <p>Entdecken Sie unsere Abonnement-Optionen und fügen Sie ein Plan hinzu.</p>
            <a href="index.php#pricing" class="btn btn-primary">Zu den Preisen</a>
        </div>
    </div>

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
                    <p>Der Plan wurde erfolgreich zu Ihrem Warenkorb hinzugefügt.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" onclick="goToCart()">Zum Warenkorb</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Weiter einkaufen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal für Checkout (Zahlungsmethode) -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Bestellung abschließen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Rechnungsempfänger</label>
                            <div class="form-control-plaintext"><strong id="displayCustomerName"><?php echo htmlspecialchars($user_name); ?></strong></div>
                            <small class="text-muted" id="displayCustomerEmail"><?php echo htmlspecialchars($user_email); ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="paymentMethodSelect" class="form-label">Zahlungsmethode</label>
                            <select class="form-select" id="paymentMethodSelect" required>
                                <option value="">-- Wählen Sie eine Zahlungsmethode --</option>
                                <option value="credit_card">Kreditkarte</option>
                                <option value="bank_transfer">Banküberweisung</option>
                                <option value="paypal">PayPal</option>
                                <option value="sepa">SEPA-Lastschrift</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i>
                            Eine detaillierte Rechnung wird nach Abschluss an Ihre E-Mail-Adresse versendet.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="completeOrder()">Bestellung abschließen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = JSON.parse(localStorage.getItem('bookit_cart')) || [];
        let additionalServices = JSON.parse(localStorage.getItem('bookit_services')) || { website: false, hosting: false };
        const cartBadge = document.getElementById('cart-badge');
        const cartItems = document.getElementById('cart-items');
        const additionalServicesDiv = document.getElementById('additional-services');
        const cartTotal = document.getElementById('cart-total');
        const emptyCart = document.getElementById('empty-cart');
        const totalPrice = document.getElementById('total-price');

        const planNames = {
            'basic': 'Basic',
            'pro': 'Pro',
            'enterprise': 'Enterprise'
        };

        const planDescriptions = {
            'basic': 'Bis zu 5 Räume',
            'pro': 'Bis zu 20 Räume',
            'enterprise': 'Unbegrenzte Räume'
        };

        function updateCartBadge() {
            const totalItems = cart.length;
            if (totalItems > 0) {
                cartBadge.textContent = totalItems;
                cartBadge.style.display = 'inline-block';
            } else {
                cartBadge.style.display = 'none';
            }
        }

        function renderCart() {
            cartItems.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                emptyCart.style.display = 'block';
                additionalServicesDiv.style.display = 'none';
                cartTotal.style.display = 'none';
                return;
            }

            emptyCart.style.display = 'none';
            additionalServicesDiv.style.display = 'block';
            cartTotal.style.display = 'block';

            cart.forEach((item, index) => {
                total += item.price;
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';
                itemDiv.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <span class="plan-badge plan-${item.plan}">${planNames[item.plan]}</span>
                        </div>
                        <div class="col-md-4">
                            <h5 class="mb-1">${planNames[item.plan]} Plan</h5>
                            <p class="text-muted mb-0">${planDescriptions[item.plan]}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>€${item.price.toFixed(2)}/Monat</strong>
                        </div>
                        <div class="col-md-3 text-end">
                            <button class="btn btn-outline-danger btn-sm" onclick="removeFromCart(${index})">
                                <i class="bi bi-trash"></i> Entfernen
                            </button>
                        </div>
                    </div>
                `;
                cartItems.appendChild(itemDiv);
            });

            // Add additional services to total
            if (additionalServices.website) {
                total += 499; // One-time fee, but we'll show it as monthly for simplicity
            }
            if (additionalServices.hosting) {
                total += 9.99;
            }

            totalPrice.textContent = `€${total.toFixed(2)}`;
            
            // Update service checkboxes
            document.getElementById('website-service').checked = additionalServices.website;
            document.getElementById('hosting-service').checked = additionalServices.hosting;
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('bookit_cart', JSON.stringify(cart));
            updateCartBadge();
            renderCart();
        }

        function clearCart() {
            if (confirm('Möchten Sie wirklich den gesamten Warenkorb leeren?')) {
                cart = [];
                additionalServices = { website: false, hosting: false };
                localStorage.setItem('bookit_cart', JSON.stringify(cart));
                localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
                updateCartBadge();
                renderCart();
            }
        }

        function proceedToCheckout() {
            // Öffne Zahlungsmethoden-Modal
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
        }
        
        function completeOrder() {
            const paymentMethod = document.getElementById('paymentMethodSelect').value;
            
            if (!paymentMethod) {
                alert('Bitte wählen Sie eine Zahlungsmethode!');
                return;
            }
            
            if (cart.length === 0) {
                alert('Ihr Warenkorb ist leer!');
                return;
            }
            
            // Sende Bestellung an checkout.php
            const orderData = {
                cart: cart,
                services: additionalServices,
                paymentMethod: paymentMethod
            };
            
            // Debug: Log der versendeten Daten
            console.log('=== CHECKOUT DEBUG ===');
            console.log('Cart Items:', cart.length);
            console.log('Cart Data:', cart);
            console.log('Payment Method:', paymentMethod);
            console.log('Services:', additionalServices);
            console.log('Sending:', JSON.stringify(orderData));
            console.log('================');
            
            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            })
            .then(res => {
                // Debug: Log der Response
                console.log('Checkout Response Status:', res.status);
                console.log('Checkout Response Headers:', res.headers.get('Content-Type'));
                
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                
                return res.text();
            })
            .then(text => {
                // Debug: Log des raw Text
                console.log('Checkout Raw Response:', text);
                
                if (!text) {
                    throw new Error('Leere Antwort von Server');
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(`JSON Parse Fehler: ${e.message}, Response: ${text.substring(0, 200)}`);
                }
            })
            .then(data => {
                if (data.success) {
                    const customerEmail = document.getElementById('displayCustomerEmail').textContent;
                    let msg = 'Bestellung erfolgreich! ';
                    
                    if (data.email_sent) {
                        msg += 'Eine Rechnung wurde an ' + customerEmail + ' versendet.';
                    } else {
                        msg += 'Bestellung gespeichert. ';
                        if (data.email_error) {
                            msg += '(Rechnung konnte nicht versendet werden: ' + data.email_error.substring(0, 50) + ')';
                        }
                    }
                    
                    alert(msg);
                    cart = [];
                    additionalServices = { website: false, hosting: false };
                    localStorage.setItem('bookit_cart', JSON.stringify(cart));
                    localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
                    updateCartBadge();
                    renderCart();
                    
                    // Schließe Modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                    modal.hide();
                } else {
                    alert('Fehler: ' + (data.error || 'Bestellung konnte nicht verarbeitet werden.'));
                }
            })
            .catch(err => {
                alert('Fehler beim Senden der Bestellung: ' + err);
            });
        }

        function toggleService(service, price) {
            additionalServices[service] = !additionalServices[service];
            localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
            renderCart();
        }

        function goToCart() {
            // Already on cart page, just close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            modal.hide();
        }

        // Initialize
        updateCartBadge();
        renderCart();
        
        // Lade User-Daten nach dem Laden des Modal (wird durch PHP provided)
        document.addEventListener('DOMContentLoaded', function() {
            const customerNameElement = document.getElementById('displayCustomerName');
            const customerEmailElement = document.getElementById('displayCustomerEmail');
            
            if (customerNameElement && customerEmailElement) {
                // User-Daten sind bereits im HTML gesetzt (durch PHP)
                // Sie werden automatisch angezeigt wenn Modal geöffnet wird
            }
        });
    </script>
</body>
</html>