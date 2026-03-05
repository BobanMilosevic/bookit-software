<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if ($slug === '' || strlen($slug) > 180) {
  http_response_code(400);
  exit('Ungültiger Parameter.');
}

$stmt = $pdo->prepare("
  SELECT id, slug, title, excerpt, body, image_path, published_at
  FROM news
  WHERE slug = :slug AND published_at IS NOT NULL AND published_at <= NOW()
  LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$item = $stmt->fetch();

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

if (!$item) {
  http_response_code(404);
  exit('News nicht gefunden.');
}

// Wenn du HTML im body speichern willst, ist das hier ok.
// Wenn du nur Plaintext speicherst: nl2br(h($item['body'])) verwenden.
$bodyHtml = (string)$item['body'];
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($item['title']) ?> – News</title>
  <link rel="stylesheet" href="/news/assets/style.css" />
</head>
<body>
  <header class="site-header">
    <div class="container">
      <a class="back" href="/news/index.php">← Zurück</a>
      <h1 class="brand">News</h1>
    </div>
  </header>

  <main class="container">
    <article class="detail">
      <div class="detail-head">
        <div class="meta">
          <span class="date"><?= date('d.m.Y H:i', strtotime((string)$item['published_at'])) ?></span>
        </div>
        <h2 class="detail-title"><?= h($item['title']) ?></h2>
        <?php if (!empty($item['excerpt'])): ?>
          <p class="detail-excerpt"><?= h((string)$item['excerpt']) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($item['image_path'])): ?>
        <div class="detail-media">
          <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['title']) ?>">
        </div>
      <?php endif; ?>

      <div class="detail-body prose">
        <?= $bodyHtml ?>
      </div>
    </article>
  </main>

  <footer class="site-footer">
    <div class="container">
      <small>© <?= date('Y') ?> News</small>
    </div>
  </footer>
</body>
</html>