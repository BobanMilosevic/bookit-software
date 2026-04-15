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
                        $nr       = (string)$a['Artikelnummer'];
                        $name     = (string)$a['Bezeichnung'];
                        $currency = (string)($a['Waehrung'] ?? 'EUR');
                        $stock    = (int)$a['Stueckzahl'];
                        $priceRaw = (float)$a['Preis'];
                        $price    = number_format($priceRaw, 2, ',', '.');

                        $bildPfad = (string)($a['bild_pfad'] ?? '');
                        $media = getProductImage($bildPfad, $cat, $categoryIcons);
                        ?>
                        <div class="col-12 col-sm-6 col-lg-4 product-item"
                             data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
                             data-sku="<?= htmlspecialchars(mb_strtolower($nr)) ?>"
                             data-category="<?= htmlspecialchars($cat) ?>">

                            <div class="ws-card">
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
                                    <div class="ws-card__sku">#<?= htmlspecialchars($nr) ?></div>
                                    <h3 class="ws-card__name"><?= htmlspecialchars($name) ?></h3>

                                    <div class="ws-card__footer">
                                        <div class="ws-card__price">
                                            <?= $price ?> <?= htmlspecialchars($currency) ?>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <?php if ($stock <= 0): ?>
                                            <span class="badge bg-danger">Ausverkauft</span>
                                        <?php endif; ?>
                                    </div>

                                    <button
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
                                    </button>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </section>
        <?php endforeach; ?>

    <?php endif; ?>

</main>

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

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.add-to-cart');
    if (!btn || btn.disabled) return;

    const added = addToCart({
        sku: btn.dataset.sku,
        name: btn.dataset.name,
        price: Number(btn.dataset.price || 0),
        currency: btn.dataset.currency || 'EUR',
        stock: Number(btn.dataset.stock || 0)
    });

    if (!added) return;

    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-lg"></i> Hinzugefügt';
    btn.classList.add('added');

    setTimeout(() => {
        btn.innerHTML = oldHtml;
        btn.classList.remove('added');
    }, 1000);
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