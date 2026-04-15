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
        .qty-controls { display: inline-flex; align-items: center; gap: .5rem; border: 1px solid #dee2e6; border-radius: 999px; padding: .25rem .5rem; }
        .qty-value { min-width: 2rem; text-align: center; font-weight: 600; }
        .stock-hint { font-size: .875rem; color: #6b7280; }
        .stock-warning { font-size: .875rem; color: #b45309; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../views/partials/navbar.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Ihr Warenkorb</h1>

    <div id="cart-items"></div>

    <div id="additional-services" class="cart-item" style="display: none;">
        <h4 style="color: var(--primary-color);">Zusatzservices</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="website-service" onchange="toggleService('website')">
                    <label class="form-check-label" for="website-service">
                        <strong>Komplette Website-Erstellung</strong><br>
                        <small class="text-muted">Professionelle Website-Erstellung für Ihr Unternehmen</small><br>
                        <span class="price">€499,00 (einmalig)</span>
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="hosting-service" onchange="toggleService('hosting')">
                    <label class="form-check-label" for="hosting-service">
                        <strong>Server-Hosting</strong><br>
                        <small class="text-muted">Professionelles Hosting für Ihre BookIT-Installation</small><br>
                        <span class="price">€9,99/Monat</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="cart-total" class="cart-total" style="display: none;">
        <div class="row">
            <div class="col-md-8">
                <h4>Gesamt: <span id="total-price">€0,00</span></h4>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary btn-lg me-2" onclick="proceedToCheckout()">Kauf abschließen</button>
                <button class="btn btn-outline-secondary" onclick="clearCart()">Warenkorb leeren</button>
            </div>
        </div>
    </div>

    <div id="empty-cart" class="empty-cart">
        <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
        <h3>Ihr Warenkorb ist leer</h3>
        <p>Fügen Sie Artikel oder Abonnements hinzu, um hier Ihren Warenkorb zu sehen.</p>
        <a href="webshop.php" class="btn btn-primary">Zum Webshop</a>
    </div>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bestellung abschließen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Rechnungsempfänger</label>
                        <div class="form-control-plaintext"><strong id="displayCustomerName"><?= htmlspecialchars($user_name) ?></strong></div>
                        <small class="text-muted" id="displayCustomerEmail"><?= htmlspecialchars($user_email) ?></small>
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
                        Beim Klick auf „Kauf abschließen“ wird der Lagerstand nochmals auf dem Server geprüft und bei Erfolg reduziert.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button type="button" class="btn btn-primary" onclick="completeOrder()">Kauf abschließen</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let cart = normalizeCart(JSON.parse(localStorage.getItem('bookit_cart')) || []);
let additionalServices = JSON.parse(localStorage.getItem('bookit_services')) || { website: false, hosting: false };

const cartBadge = document.getElementById('cart-badge');
const cartItems = document.getElementById('cart-items');
const additionalServicesDiv = document.getElementById('additional-services');
const cartTotal = document.getElementById('cart-total');
const emptyCart = document.getElementById('empty-cart');
const totalPrice = document.getElementById('total-price');

const planNames = {
    basic: 'Basic',
    pro: 'Pro',
    enterprise: 'Enterprise'
};

const planDescriptions = {
    basic: 'Bis zu 5 Räume',
    pro: 'Bis zu 20 Räume',
    enterprise: 'Unbegrenzte Räume'
};

function normalizeCart(items) {
    return items.map(item => ({
        ...item,
        qty: Math.max(1, Number(item.qty || 1)),
        price: Number(item.price || 0),
        stock: item.stock !== undefined ? Number(item.stock) : null,
        currency: item.currency || 'EUR'
    }));
}

function saveCart() {
    localStorage.setItem('bookit_cart', JSON.stringify(cart));
}

function formatPrice(value) {
    return `€${Number(value || 0).toFixed(2)}`;
}

function updateCartBadge() {
    const totalItems = cart.reduce((sum, item) => sum + Number(item.qty || 1), 0);
    if (!cartBadge) return;

    if (totalItems > 0) {
        cartBadge.textContent = totalItems;
        cartBadge.style.display = 'inline-block';
    } else {
        cartBadge.style.display = 'none';
    }
}

function getCartItemMeta(item) {
    const qty = Math.max(1, Number(item.qty || 1));
    const isPlan = Boolean(item.plan && planNames[item.plan]);
    const hasStockInfo = !isPlan && item.stock !== null && !Number.isNaN(Number(item.stock));
    const stock = hasStockInfo ? Math.max(0, Number(item.stock)) : null;

    const displayName = isPlan
        ? `${planNames[item.plan]} Plan`
        : (item.name || item.sku || 'Artikel');

    const description = isPlan
        ? planDescriptions[item.plan]
        : (item.sku ? `Artikelnummer: ${item.sku}` : 'Webshop-Artikel');

    const badgeClass = isPlan ? `plan-badge plan-${item.plan}` : 'plan-badge plan-basic';
    const badgeText = isPlan ? planNames[item.plan] : 'Artikel';
    const priceSuffix = isPlan ? '/Monat' : '';
    const linePrice = Number(item.price || 0) * qty;

    return {
        qty,
        isPlan,
        displayName,
        description,
        badgeClass,
        badgeText,
        priceSuffix,
        linePrice,
        stock,
        hasStockInfo,
        atMaxStock: !isPlan && stock !== null && qty >= stock,
        soldOut: !isPlan && stock !== null && stock <= 0
    };
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
        const meta = getCartItemMeta(item);
        total += meta.linePrice;

        const itemDiv = document.createElement('div');
        itemDiv.className = 'cart-item';

        let stockInfoHtml = '';
        if (!meta.isPlan) {
            if (meta.soldOut) {
                stockInfoHtml = '<div class="stock-warning mt-2"><i class="bi bi-exclamation-triangle"></i> Dieser Artikel ist aktuell ausverkauft. Bitte entfernen Sie ihn aus dem Warenkorb.</div>';
            } else if (meta.hasStockInfo) {
                stockInfoHtml = `<div class="${meta.atMaxStock ? 'stock-warning' : 'stock-hint'} mt-2">Lagernd: ${meta.stock}${meta.atMaxStock ? ' · Mehr kann nicht hinzugefügt werden.' : ''}</div>`;
            } else {
                stockInfoHtml = '<div class="stock-warning mt-2">Lagerbestand unbekannt. Bitte Artikel neu aus dem Webshop hinzufügen.</div>';
            }
        }

        let qtyControlsHtml = '';
        if (!meta.isPlan) {
            qtyControlsHtml = `
                <div class="mt-3">
                    <div class="qty-controls">
                        <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${index}, -1)" ${meta.qty <= 1 ? 'disabled' : ''}>
                            <i class="bi bi-dash"></i>
                        </button>
                        <span class="qty-value">${meta.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${index}, 1)" ${(meta.soldOut || !meta.hasStockInfo || meta.atMaxStock) ? 'disabled' : ''}>
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        itemDiv.innerHTML = `
            <div class="row align-items-center g-3">
                <div class="col-md-2">
                    <span class="${meta.badgeClass}">${meta.badgeText}</span>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-1">${meta.displayName}</h5>
                    <p class="text-muted mb-0">${meta.description}</p>
                    ${stockInfoHtml}
                </div>
                <div class="col-md-3">
                    <strong>${formatPrice(meta.linePrice)}${meta.priceSuffix}</strong>
                    ${qtyControlsHtml}
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

    if (additionalServices.website) total += 499;
    if (additionalServices.hosting) total += 9.99;

    totalPrice.textContent = formatPrice(total);
    document.getElementById('website-service').checked = additionalServices.website;
    document.getElementById('hosting-service').checked = additionalServices.hosting;
}

function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartBadge();
    renderCart();
}

function changeQty(index, delta) {
    const item = cart[index];
    if (!item) return;

    const isPlan = Boolean(item.plan && planNames[item.plan]);
    if (isPlan) return;

    const currentQty = Math.max(1, Number(item.qty || 1));
    const newQty = currentQty + delta;

    if (newQty < 1) return;

    const hasStockInfo = item.stock !== null && item.stock !== undefined && !Number.isNaN(Number(item.stock));
    const stock = hasStockInfo ? Math.max(0, Number(item.stock)) : null;

    if (!hasStockInfo) {
        alert('Lagerbestand unbekannt. Bitte entfernen Sie den Artikel und fügen Sie ihn erneut aus dem Webshop hinzu.');
        return;
    }

    if (stock <= 0) {
        alert('Dieser Artikel ist ausverkauft und kann nicht erhöht werden.');
        return;
    }

    if (newQty > stock) {
        alert(`Es sind nur ${stock} Stück lagernd.`);
        return;
    }

    item.qty = newQty;
    saveCart();
    updateCartBadge();
    renderCart();
}

function clearCart() {
    if (confirm('Möchten Sie wirklich den gesamten Warenkorb leeren?')) {
        cart = [];
        additionalServices = { website: false, hosting: false };
        saveCart();
        localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
        updateCartBadge();
        renderCart();
    }
}

function toggleService(service) {
    additionalServices[service] = !additionalServices[service];
    localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
    renderCart();
}

function validateCartForCheckout() {
    for (const item of cart) {
        const meta = getCartItemMeta(item);
        if (!meta.isPlan) {
            if (!meta.hasStockInfo) {
                alert(`Beim Artikel "${meta.displayName}" fehlt die Lagerinfo. Bitte entfernen Sie ihn und fügen Sie ihn erneut aus dem Webshop hinzu.`);
                return false;
            }
            if (meta.soldOut) {
                alert(`Der Artikel "${meta.displayName}" ist aktuell ausverkauft.`);
                return false;
            }
            if (meta.qty > meta.stock) {
                alert(`Vom Artikel "${meta.displayName}" sind nur ${meta.stock} Stück lagernd.`);
                return false;
            }
        }
    }
    return true;
}

function proceedToCheckout() {
    if (cart.length === 0) {
        alert('Ihr Warenkorb ist leer!');
        return;
    }

    if (!validateCartForCheckout()) {
        return;
    }

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

    if (!validateCartForCheckout()) {
        return;
    }

    const orderData = {
        cart: cart,
        services: additionalServices,
        paymentMethod: paymentMethod
    };

    fetch('checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(async (res) => {
        const text = await res.text();
        let data = {};
        try {
            data = text ? JSON.parse(text) : {};
        } catch (e) {
            throw new Error('Ungültige Serverantwort');
        }

        if (!res.ok || !data.success) {
            throw new Error(data.error || `HTTP ${res.status}`);
        }

        return data;
    })
    .then((data) => {
        const customerEmail = document.getElementById('displayCustomerEmail').textContent;
        let msg = 'Bestellung erfolgreich abgeschlossen. Der Lagerstand wurde reduziert.';

        if (data.email_sent) {
            msg += ' Eine Rechnung wurde an ' + customerEmail + ' versendet.';
        } else if (data.email_error) {
            msg += ' Die Rechnung konnte per E-Mail nicht versendet werden: ' + data.email_error;
        }

        alert(msg);

        cart = [];
        additionalServices = { website: false, hosting: false };
        saveCart();
        localStorage.setItem('bookit_services', JSON.stringify(additionalServices));
        updateCartBadge();
        renderCart();

        const modal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
        if (modal) modal.hide();
    })
    .catch((err) => {
        alert('Fehler beim Abschließen des Kaufs: ' + err.message);
    });
}

updateCartBadge();
renderCart();
</script>
</body>
</html>