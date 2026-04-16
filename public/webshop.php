<?php
declare(strict_types=1);

require __DIR__ . '/../app/auth/bootstrap.php';
require __DIR__ . '/../app/db.php';

$articles      = [];
$grouped       = [];
$allCategories = [];

try {
    $pdo  = db();
    $stmt = $pdo->query("
        SELECT
          a.Artikelnummer,
          a.Bezeichnung,
          a.Beschreibung,
          a.Preis,
          a.Waehrung,
          a.Stueckzahl,
          a.bild_pfad,
          k.name AS Kategorie
        FROM Artikel a
        JOIN Kategorien k ON k.id = a.kategorie_id
        ORDER BY k.name, a.Bezeichnung
    ");
    $articles = $stmt->fetchAll();
} catch (Throwable $e) {
    echo "<pre>FEHLER:\n" . $e->getMessage() . "\n\n" . $e->getFile() . ":" . $e->getLine() . "</pre>";
    exit;
}

// Kategorien vorbereiten
$grouped = [];
foreach ($articles as $a) {
    $cat = (string)$a['Kategorie'];
    $grouped[$cat][] = $a;
}
$allCategories = array_keys($grouped);
sort($allCategories);

// Sortierung der Kategorien
$preferredOrder = ['Abos', 'Hardware', 'Merch', 'Sonstiges'];
uksort($grouped, function ($a, $b) use ($preferredOrder) {
    $pa = array_search($a, $preferredOrder, true);
    $pb = array_search($b, $preferredOrder, true);
    $pa = $pa === false ? 999 : $pa;
    $pb = $pb === false ? 999 : $pb;
    return $pa <=> $pb ?: strcmp($a, $b);
});

$allCategories = array_keys($grouped);

/* Kategorie-Icons als Fallback wenn kein Bild in DB gespeichert */
$categoryIcons = [
    'Abos'      => 'bi-calendar-check',
    'Hardware'  => 'bi-cpu',
    'Merch'     => 'bi-bag-heart',
    'Sonstiges' => 'bi-box-seam',
];

function getProductImage(string $bildPfad, string $cat, array $icons): array {
    if ($bildPfad !== '') {
        return ['type' => 'img', 'src' => $bildPfad];
    }
    $icon = $icons[$cat] ?? 'bi-box';
    return ['type' => 'icon', 'icon' => $icon];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop – BookIT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/webshop.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        .product-item {
            cursor: pointer;
        }

        .ws-card {
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .ws-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(0,0,0,.10);
        }

        .ws-card__body {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            height: 100%;
        }

        .ws-card__footer {
            margin-top: auto;
        }

        .ws-card__desc {
            color: #64748b;
            font-size: .95rem;
            line-height: 1.45;
            min-height: 2.8em;
        }

        .ws-stock-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .75rem;
            border-radius: 999px;
            font-size: .65rem;
            font-weight: 500;
        }

        .ws-stock-badge.in-stock {
            background: rgba(17,128,117,.12);
            color: #118075;
        }

        .ws-stock-badge.out-of-stock {
            background: rgba(220,53,69,.12);
            color: #dc3545;
        }

        .ws-detail-modal .modal-content {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(15, 23, 42, .22);
        }

        .ws-detail-modal .modal-header {
            border-bottom: 0;
            padding: 1.25rem 1.5rem 0;
        }

        .ws-detail-modal .modal-body {
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .ws-detail-media {
            background: linear-gradient(180deg, #f8fafc 0%, #eef4f7 100%);
            border-radius: 18px;
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .06);
        }

        .ws-detail-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .ws-detail-media--placeholder {
            flex-direction: column;
            color: #64748b;
            gap: .75rem;
        }

        .ws-detail-media--placeholder i {
            font-size: 4rem;
            color: #118075;
        }

        .ws-detail-sku {
            color: #64748b;
            font-size: .92rem;
            margin-bottom: .35rem;
        }

        .ws-detail-title {
            font-size: 1.85rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: .75rem;
        }

        .ws-detail-price {
            font-size: 1.65rem;
            font-weight: 700;
            color: #118075;
            margin-bottom: 1rem;
        }

        .ws-detail-description {
            color: #475569;
            line-height: 1.7;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 16px;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
            white-space: pre-line;
        }

        .ws-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            margin-bottom: 1.25rem;
        }

        .ws-detail-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .5rem .8rem;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, .06);
            border-radius: 999px;
            color: #334155;
            font-size: .9rem;
            font-weight: 500;
        }

        .ws-detail-actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .ws-detail-btn {
            border: 0;
            border-radius: 12px;
            padding: .9rem 1.15rem;
            font-weight: 600;
            transition: transform .2s ease, opacity .2s ease;
        }

        .ws-detail-btn:hover {
            transform: translateY(-2px);
        }

        .ws-detail-btn-primary {
            background: linear-gradient(135deg, #118075, #4D8496);
            color: #fff;
        }

        .ws-detail-btn-secondary {
            background: #eef2f7;
            color: #334155;
        }

        @media (max-width: 991.98px) {
            .ws-detail-media {
                min-height: 240px;
                margin-bottom: 1rem;
            }

            .ws-detail-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>

<?php require __DIR__ . '/../views/partials/navbar.php'; ?>

<header class="ws-header">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1>Webshop</h1>
                <p>Filtere nach Kategorie oder suche nach Artikeln.</p>
            </div>
            <div class="ws-header__count">
                <i class="bi bi-box-seam me-1"></i>
                <?= count($articles) ?> Artikel
            </div>
        </div>
    </div>
</header>

<main class="container py-4">

    <div class="ws-filterbar">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-5">
                <div class="ws-search-wrap">
                    <i class="bi bi-search"></i>
                    <input id="search" class="ws-search" type="text"
                           placeholder="Suche nach Name oder Artikelnummer …">
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="ws-chips justify-content-lg-end">
                    <button class="ws-chip active" data-filter="all" type="button">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Alle
                    </button>
                    <?php foreach ($allCategories as $cat): ?>
                        <button class="ws-chip" data-filter="<?= htmlspecialchars($cat) ?>" type="button">
                            <i class="<?= $categoryIcons[$cat] ?? 'bi-tag' ?>"></i>
                            <?= htmlspecialchars($cat) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="ws-no-results">
        <i class="bi bi-search"></i>
        <h3>Keine Artikel gefunden</h3>
        <p>Versuche einen anderen Suchbegriff oder wähle eine andere Kategorie.</p>
    </div>

    <?php if (empty($articles)): ?>
        <div class="ws-empty">
            <i class="bi bi-box-seam"></i>
            <h3>Keine Artikel vorhanden</h3>
            <p>Es sind noch keine Produkte im Shop verfügbar.</p>
        </div>
    <?php else: ?>

        <?php foreach ($grouped as $cat => $items): ?>
            <section class="category-section" data-category="<?= htmlspecialchars($cat) ?>">

                <div class="ws-cat-heading">
                    <h2>
                        <i class="<?= $categoryIcons[$cat] ?? 'bi-tag' ?>"></i>
                        <?= htmlspecialchars($cat) ?>
                    </h2>
                    <span class="ws-cat-count"><?= count($items) ?> Artikel</span>
                </div>

                <div class="row g-3 g-lg-4">
                    <?php foreach ($items as $a): ?>
                        <?php
                        $nr          = (string)$a['Artikelnummer'];
                        $name        = (string)$a['Bezeichnung'];
                        $description = trim((string)($a['Beschreibung'] ?? ''));
                        $currency    = (string)($a['Waehrung'] ?? 'EUR');
                        $stock       = (int)$a['Stueckzahl'];
                        $priceRaw    = (float)$a['Preis'];
                        $price       = number_format($priceRaw, 2, ',', '.');

                        $bildPfad = (string)($a['bild_pfad'] ?? '');
                        $media = getProductImage($bildPfad, $cat, $categoryIcons);

                        $shortDescription = $description !== ''
                            ? mb_strimwidth($description, 0, 90, '…')
                            : 'Keine Beschreibung vorhanden.';
                        ?>
                        <div class="col-12 col-sm-6 col-lg-4 product-item"
                             data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
                             data-sku="<?= htmlspecialchars(mb_strtolower($nr)) ?>"
                             data-category="<?= htmlspecialchars($cat) ?>">

                            <div class="ws-card product-card"
                                 role="button"
                                 tabindex="0"
                                 data-bs-toggle="modal"
                                 data-bs-target="#productDetailModal"
                                 data-sku="<?= htmlspecialchars($nr) ?>"
                                 data-name="<?= htmlspecialchars($name) ?>"
                                 data-description="<?= htmlspecialchars($description !== '' ? $description : 'Keine Beschreibung vorhanden.') ?>"
                                 data-price="<?= $priceRaw ?>"
                                 data-price-formatted="<?= htmlspecialchars($price . ' ' . $currency) ?>"
                                 data-currency="<?= htmlspecialchars($currency) ?>"
                                 data-stock="<?= $stock ?>"
                                 data-category-name="<?= htmlspecialchars($cat) ?>"
                                 data-image-type="<?= htmlspecialchars($media['type']) ?>"
                                 data-image-src="<?= htmlspecialchars($media['type'] === 'img' ? $media['src'] : '') ?>"
                                 data-image-icon="<?= htmlspecialchars($media['type'] === 'icon' ? $media['icon'] : '') ?>"
                            >
                                <div class="ws-card__media <?= $media['type'] === 'icon' ? 'ws-card__media--placeholder' : '' ?>">
                                    <?php if ($media['type'] === 'img'): ?>
                                        <img src="<?= htmlspecialchars($media['src']) ?>"
                                             alt="<?= htmlspecialchars($name) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <i class="<?= $media['icon'] ?>"></i>
                                        <span><?= htmlspecialchars($cat) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="ws-card__body">
                                    <!--<div class="ws-card__sku">#<?= htmlspecialchars($nr) ?></div>-->
                                    <h3 class="ws-card__name"><?= htmlspecialchars($name) ?></h3>
                                    

                                    <div class="ws-card__footer">
                                        <div class="ws-card__price">
                                            <?= $price ?> <?= htmlspecialchars($currency) ?>
                                        </div>
                                        <div class="mb-2">
                                        <?php if ($stock <= 0): ?>
                                            <span class="ws-stock-badge out-of-stock">
                                                <i class="bi bi-x-circle"></i> Ausverkauft
                                            </span>
                                        <?php else: ?>
                                            <span class="ws-stock-badge in-stock">
                                                <i class="bi bi-check-circle"></i> Lagernd
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    </div>

                                    
                                    <!--<button
                                        type="button"
                                        class="ws-btn-cart add-to-cart"
                                        <?= $stock <= 0 ? 'disabled' : '' ?>
                                        data-sku="<?= htmlspecialchars($nr) ?>"
                                        data-name="<?= htmlspecialchars($name) ?>"
                                        data-price="<?= $priceRaw ?>"
                                        data-currency="<?= htmlspecialchars($currency) ?>"
                                        data-stock="<?= $stock ?>"
                                    >
                                        <?php if ($stock <= 0): ?>
                                            <i class="bi bi-x-circle"></i> Ausverkauft
                                        <?php else: ?>
                                            <i class="bi bi-bag-plus"></i> In den Warenkorb
                                        <?php endif; ?>
                                    </button>-->
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </section>
        <?php endforeach; ?>

    <?php endif; ?>

</main>

<div class="modal fade ws-detail-modal" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-6">
                        <div id="detailMedia" class="ws-detail-media"></div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ws-detail-sku" id="detailSku">#0000000000</div>
                        <h2 class="ws-detail-title" id="productDetailModalLabel">Artikelname</h2>
                        <div class="ws-detail-price" id="detailPrice">0,00 EUR</div>

                        <div class="ws-detail-meta">
                            <span class="ws-detail-pill" id="detailCategory">
                                <i class="bi bi-tag"></i>
                                Kategorie
                            </span>
                            <span class="ws-detail-pill" id="detailStock">
                                <i class="bi bi-box-seam"></i>
                                Lagerstand
                            </span>
                        </div>

                        <div class="ws-detail-description" id="detailDescription">
                            Keine Beschreibung vorhanden.
                        </div>

                        <div class="ws-detail-actions">
                            <button type="button" id="detailAddToCart" class="ws-detail-btn ws-detail-btn-primary">
                                <i class="bi bi-bag-plus"></i> In den Warenkorb
                            </button>
                            <button type="button" class="ws-detail-btn ws-detail-btn-secondary" data-bs-dismiss="modal">
                                Schließen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer style="background:#1e293b; color:rgba(255,255,255,.5); padding:2.5rem 0; text-align:center; margin-top:4rem;">
    <div class="container">
        <p style="font-size:.85rem; margin:0;">
            &copy; 2026 BookIT. Alle Rechte vorbehalten.
            &nbsp;·&nbsp; <a href="about.php" style="color:rgba(255,255,255,.5);">Über uns</a>
            &nbsp;·&nbsp; <a href="impressum.php" style="color:rgba(255,255,255,.5);">Impressum</a>
        </p>
    </div>
</footer>

<script>
function getCart() {
    return JSON.parse(localStorage.getItem('bookit_cart') || '[]');
}

function saveCart(c) {
    localStorage.setItem('bookit_cart', JSON.stringify(c));
}

function updateCartBadge() {
    const cart  = getCart();
    const total = cart.reduce((s, i) => s + Number(i.qty || 0), 0);
    const b = document.getElementById('cart-badge');
    if (!b) return;

    if (total > 0) {
        b.style.display = 'inline-block';
        b.textContent = total;
    } else {
        b.style.display = 'none';
    }
}

function addToCart(item) {
    const cart = getCart();
    const existing = cart.find(x => x.sku === item.sku);
    const stock = Number(item.stock || 0);

    if (stock <= 0) {
        alert('Dieser Artikel ist ausverkauft.');
        return false;
    }

    if (existing) {
        const currentQty = Number(existing.qty || 0);
        if (currentQty >= stock) {
            alert(`Es sind nur ${stock} Stück lagernd.`);
            return false;
        }
        existing.qty = currentQty + 1;
        existing.stock = stock;
        existing.price = Number(item.price || existing.price || 0);
        existing.name = item.name || existing.name || 'Artikel';
        existing.currency = item.currency || existing.currency || 'EUR';
    } else {
        cart.push({
            sku: item.sku,
            name: item.name || 'Artikel',
            price: Number(item.price || 0),
            currency: item.currency || 'EUR',
            stock: stock,
            qty: 1
        });
    }

    saveCart(cart);
    updateCartBadge();
    return true;
}

function showAddedFeedback(button) {
    const oldHtml = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check-lg"></i> Hinzugefügt';
    button.classList.add('added');

    setTimeout(() => {
        button.innerHTML = oldHtml;
        button.classList.remove('added');
    }, 1000);
}

document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.add-to-cart');
    if (addBtn) {
        e.preventDefault();
        e.stopPropagation();

        if (addBtn.disabled) return;

        const added = addToCart({
            sku: addBtn.dataset.sku,
            name: addBtn.dataset.name,
            price: Number(addBtn.dataset.price || 0),
            currency: addBtn.dataset.currency || 'EUR',
            stock: Number(addBtn.dataset.stock || 0)
        });

        if (added) {
            showAddedFeedback(addBtn);
        }
        return;
    }

    const card = e.target.closest('.product-card');
    if (!card) return;
});

document.addEventListener('keydown', (e) => {
    const card = e.target.closest('.product-card');
    if (!card) return;

    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        card.click();
    }
});

const productDetailModal = document.getElementById('productDetailModal');
const detailMedia = document.getElementById('detailMedia');
const detailSku = document.getElementById('detailSku');
const detailTitle = document.getElementById('productDetailModalLabel');
const detailPrice = document.getElementById('detailPrice');
const detailCategory = document.getElementById('detailCategory');
const detailStock = document.getElementById('detailStock');
const detailDescription = document.getElementById('detailDescription');
const detailAddToCart = document.getElementById('detailAddToCart');

if (productDetailModal) {
    productDetailModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const sku = trigger.dataset.sku || '';
        const name = trigger.dataset.name || 'Artikel';
        const description = trigger.dataset.description || 'Keine Beschreibung vorhanden.';
        const priceFormatted = trigger.dataset.priceFormatted || '0,00 EUR';
        const stock = Number(trigger.dataset.stock || 0);
        const categoryName = trigger.dataset.categoryName || 'Kategorie';
        const imageType = trigger.dataset.imageType || 'icon';
        const imageSrc = trigger.dataset.imageSrc || '';
        const imageIcon = trigger.dataset.imageIcon || 'bi-box';

        detailSku.textContent = '#' + sku;
        detailTitle.textContent = name;
        detailPrice.textContent = priceFormatted;
        detailDescription.textContent = description;

        detailCategory.innerHTML = `<i class="bi bi-tag"></i> ${categoryName}`;

        if (stock > 0) {
            detailStock.innerHTML = `<i class="bi bi-check-circle"></i> Lagernd`;
        } else {
            detailStock.innerHTML = `<i class="bi bi-x-circle"></i> Ausverkauft`;
        }

        if (imageType === 'img' && imageSrc) {
            detailMedia.className = 'ws-detail-media';
            detailMedia.innerHTML = `<img src="${imageSrc}" alt="${name}">`;
        } else {
            detailMedia.className = 'ws-detail-media ws-detail-media--placeholder';
            detailMedia.innerHTML = `<i class="${imageIcon}"></i><span>${categoryName}</span>`;
        }

        detailAddToCart.dataset.sku = sku;
        detailAddToCart.dataset.name = name;
        detailAddToCart.dataset.price = trigger.dataset.price || '0';
        detailAddToCart.dataset.currency = trigger.dataset.currency || 'EUR';
        detailAddToCart.dataset.stock = String(stock);

        if (stock <= 0) {
            detailAddToCart.disabled = true;
            detailAddToCart.innerHTML = '<i class="bi bi-x-circle"></i> Ausverkauft';
        } else {
            detailAddToCart.disabled = false;
            detailAddToCart.innerHTML = '<i class="bi bi-bag-plus"></i> In den Warenkorb';
        }
    });
}

detailAddToCart.addEventListener('click', function () {
    if (detailAddToCart.disabled) return;

    const added = addToCart({
        sku: detailAddToCart.dataset.sku,
        name: detailAddToCart.dataset.name,
        price: Number(detailAddToCart.dataset.price || 0),
        currency: detailAddToCart.dataset.currency || 'EUR',
        stock: Number(detailAddToCart.dataset.stock || 0)
    });

    if (added) {
        showAddedFeedback(detailAddToCart);
    }
});

updateCartBadge();

let activeCategory = 'all';

function applyFilters() {
    const q = (document.getElementById('search').value || '').trim().toLowerCase();
    const items = document.querySelectorAll('.product-item');

    items.forEach(el => {
        const name = el.dataset.name || '';
        const sku = el.dataset.sku || '';
        const cat = el.dataset.category || '';
        const matchText = !q || name.includes(q) || sku.includes(q);
        const matchCat = (activeCategory === 'all') || (cat === activeCategory);
        el.style.display = (matchText && matchCat) ? '' : 'none';
    });

    let anyVisible = false;
    document.querySelectorAll('.category-section').forEach(sec => {
        const secCat = sec.dataset.category;
        const hasVisible = Array.from(sec.querySelectorAll('.product-item'))
            .some(x => x.style.display !== 'none');
        const show = hasVisible && (activeCategory === 'all' || secCat === activeCategory);
        sec.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
    });

    document.getElementById('ws-no-results').style.display = anyVisible ? 'none' : 'block';
}

document.getElementById('search').addEventListener('input', applyFilters);

document.querySelectorAll('[data-filter]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeCategory = btn.dataset.filter;
        applyFilters();
    });
});

applyFilters();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>