<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
$articles = [];
$grouped = [];
$allCategories = [];
try {
  $pdo = db();


  $stmt = $pdo->query("
    SELECT
      a.Artikelnummer,
      a.Bezeichnung,
      a.Preis,
      a.Waehrung,
      a.Stueckzahl,
      k.name AS Kategorie
    FROM Artikel a
    JOIN Kategorien k ON k.id = a.kategorie_id
    ORDER BY k.name, a.Bezeichnung
  ");

} catch (Throwable $e) {
  echo "<pre>FEHLER:\n" . $e->getMessage() . "\n\n" . $e->getFile() . ":" . $e->getLine() . "</pre>";
  exit;
}

// Gruppieren
$grouped = [];
foreach ($articles as $a) {
  $cat = (string)$a['Kategorie'];
  $grouped[$cat][] = $a;
}
$allCategories = array_keys($grouped);
sort($allCategories);
$articles = $stmt->fetchAll();

// Kategorien vorbereiten
$grouped = [];

foreach ($articles as $a) {
    $cat = (string)$a['Kategorie'];   // ← aus DB
    $grouped[$cat][] = $a;
}

$allCategories = array_keys($grouped);
sort($allCategories);

// Sortierung der Kategorien (optional)
$preferredOrder = ['Abos', 'Hardware', 'Merch', 'Sonstiges'];
uksort($grouped, function($a, $b) use ($preferredOrder) {
    $pa = array_search($a, $preferredOrder, true);
    $pb = array_search($b, $preferredOrder, true);
    $pa = $pa === false ? 999 : $pa;
    $pb = $pb === false ? 999 : $pb;
    return $pa <=> $pb ?: strcmp($a, $b);
});

$allCategories = array_keys($grouped);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Webshop</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <style>
    :root{
      --primary:#118075;
      --muted:#64748b;
      --bg:#f8fafc;
      --card:#ffffff;
      --border:#e2e8f0;
    }
    body{ background: var(--bg); }
    .page-header{
      background: linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
      border-bottom: 1px solid var(--border);
    }
    .brand-badge{
      background: rgba(17,128,117,.08);
      color: var(--primary);
      border: 1px solid rgba(17,128,117,.18);
      font-weight: 600;
    }
    .filterbar{
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    }
    .product-card{
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
      transition: transform .12s ease, box-shadow .12s ease;
      height: 100%;
    }
    .product-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.10);
    }
    .sku{
      font-size: .85rem;
      color: var(--muted);
    }
    .price{
      font-size: 1.1rem;
      font-weight: 700;
    }
    .stock-low{ background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.25); }
    .stock-ok{ background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.25); }

    .cat-title{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      margin: 28px 0 14px;
    }
    .cat-title h2{
      font-size: 1.25rem;
      margin:0;
    }
    .cat-count{
      color: var(--muted);
      font-weight: 600;
      font-size: .95rem;
    }
    .btn-primary{
      background: var(--primary);
      border-color: var(--primary);
      border-radius: 12px;
      font-weight: 700;
    }
    .btn-outline-primary{
      border-radius: 999px;
      font-weight: 700;
    }
    .btn-outline-primary.active{
      background: rgba(17,128,117,.10);
      border-color: rgba(17,128,117,.45);
      color: var(--primary);
    }
    .search-input{
      border-radius: 999px;
      border: 1px solid var(--border);
      padding-left: 42px;
    }
    .search-wrap{
      position: relative;
    }
    .search-wrap i{
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
    }
    .muted{ color: var(--muted); }
  </style>
</head>

<body>

<?php require __DIR__ . '/partials/navbar.php'; ?>

<header class="page-header py-4">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h1 class="h3 mb-1">Webshop</h1>
        <div class="muted">Filtere nach Kategorie oder suche nach Artikeln.</div>
      </div>
      <div class="text-end muted">
        <div class="fw-semibold"><?= count($articles) ?> Artikel</div>
      </div>
    </div>
  </div>
</header>

<main class="container py-4">
  <div class="filterbar p-3 p-md-4 mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-12 col-lg-5">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input id="search" class="form-control search-input" type="text" placeholder="Suche nach Name oder Artikelnummer …">
        </div>
      </div>

      <div class="col-12 col-lg-7">
        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
          <button class="btn btn-outline-primary active" data-filter="all" type="button">Alle</button>
          <?php foreach ($allCategories as $cat): ?>
            <button class="btn btn-outline-primary" data-filter="<?= htmlspecialchars($cat) ?>" type="button">
              <?= htmlspecialchars($cat) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (empty($articles)): ?>
    <div class="alert alert-warning">Keine Artikel gefunden.</div>
  <?php else: ?>

    <?php foreach ($grouped as $cat => $items): ?>
      <section class="category-section" data-category="<?= htmlspecialchars($cat) ?>">
        <div class="cat-title">
          <h2><?= htmlspecialchars($cat) ?></h2>
          <div class="cat-count"><?= count($items) ?> Artikel</div>
        </div>

        <div class="row g-4">
          <?php foreach ($items as $a): ?>
            <?php
              $nr = (string)$a['Artikelnummer'];
              $name = (string)$a['Bezeichnung'];
              $currency = (string)($a['Waehrung'] ?? 'EUR');
              $stock = (int)$a['Stueckzahl'];
              $priceRaw = (float)$a['Preis'];
              $price = number_format($priceRaw, 2, ',', '.');

              $stockClass = $stock <= 0 ? '' : ($stock <= 5 ? 'stock-low' : 'stock-ok');
              $stockText  = $stock <= 0 ? 'Ausverkauft' : ('Lager: ' . $stock);
            ?>
            <div class="col-12 col-md-6 col-lg-4 product-item"
                 data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
                 data-sku="<?= htmlspecialchars(mb_strtolower($nr)) ?>"
                 data-category="<?= htmlspecialchars($cat) ?>">
              <div class="product-card p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <div>
                    <div class="sku">#<?= htmlspecialchars($nr) ?></div>
                    <h3 class="h5 mb-0"><?= htmlspecialchars($name) ?></h3>
                  </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3">
                  <div class="price"><?= $price ?> <?= htmlspecialchars($currency) ?></div>
                  <span class="badge text-bg-light border"><?= htmlspecialchars($cat) ?></span>
                </div>

                <div class="mt-3">
                  <button
                    class="btn btn-primary w-100 add-to-cart"
                    <?= $stock <= 0 ? 'disabled' : '' ?>
                    data-sku="<?= htmlspecialchars($nr) ?>"
                    data-name="<?= htmlspecialchars($name) ?>"
                    data-price="<?= $priceRaw ?>"
                    data-currency="<?= htmlspecialchars($currency) ?>"
                  >
                    <?= $stock <= 0 ? 'Nicht verfügbar' : 'In den Warenkorb' ?>
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

<script>
/* ===== Warenkorb (localStorage) ===== */
function getCart(){ return JSON.parse(localStorage.getItem('bookit_cart') || '[]'); }
function saveCart(c){ localStorage.setItem('bookit_cart', JSON.stringify(c)); }
function updateCartBadge(){
  const cart = getCart();
  const total = cart.reduce((s,i)=>s+(i.qty||0),0);
  const b = document.getElementById('cart-badge');
  if(!b) return;
  if(total>0){ b.style.display='inline-block'; b.textContent=total; }
  else{ b.style.display='none'; }
}
function addToCart(item){
  const cart = getCart();
  const ex = cart.find(x => x.sku === item.sku);
  if(ex) ex.qty += 1;
  else cart.push({...item, qty: 1});
  saveCart(cart);
  updateCartBadge();
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('.add-to-cart');
  if(!btn || btn.disabled) return;

  addToCart({
    sku: btn.dataset.sku,
    name: btn.dataset.name,
    price: Number(btn.dataset.price || 0),
    currency: btn.dataset.currency || 'EUR'
  });

  const old = btn.textContent;
  btn.textContent = 'Hinzugefügt ✓';
  setTimeout(()=>btn.textContent = old, 900);
});

updateCartBadge();

/* ===== Filter + Suche ===== */
let activeCategory = 'all';

function applyFilters(){
  const q = (document.getElementById('search').value || '').trim().toLowerCase();
  const items = document.querySelectorAll('.product-item');

  items.forEach(el => {
    const name = el.dataset.name || '';
    const sku = el.dataset.sku || '';
    const cat = el.dataset.category || '';
    const matchText = !q || name.includes(q) || sku.includes(q);
    const matchCat  = (activeCategory === 'all') || (cat === activeCategory);
    el.style.display = (matchText && matchCat) ? '' : 'none';
  });

  // Kategorien komplett verstecken, wenn darin nichts sichtbar ist
  document.querySelectorAll('.category-section').forEach(sec => {
    const secCat = sec.dataset.category;
    const anyVisible = Array.from(sec.querySelectorAll('.product-item'))
      .some(x => x.style.display !== 'none');
    sec.style.display = anyVisible && (activeCategory==='all' || secCat===activeCategory) ? '' : 'none';
  });
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