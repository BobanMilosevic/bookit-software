<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$stmt = $pdo->prepare("
  SELECT id, slug, title, excerpt, image_path, published_at
  FROM news
  WHERE published_at IS NOT NULL AND published_at <= NOW()
  ORDER BY published_at DESC
  LIMIT 50
");
$stmt->execute();
$news = $stmt->fetchAll();

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>News</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1 class="brand">News</h1>
      <p class="sub">Aktuelle Updates & Infos</p>
    </div>
  </header>

  <main class="container">
    <?php if (!$news): ?>
      <div class="empty">
        <h2>Keine News vorhanden</h2>
        <p>Lege Einträge in der Tabelle <code>news</code> an.</p>
      </div>
    <?php else: ?>
      <section class="grid">
        <?php foreach ($news as $item): ?>
          <article class="card">
            <a class="card-link" href="/news/news.php?slug=<?= h($item['slug']) ?>">
              <div class="card-media">
                <?php if (!empty($item['image_path'])): ?>
                  <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="placeholder">Kein Bild</div>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <div class="meta">
                  <span class="date">
                    <?= $item['published_at'] ? date('d.m.Y H:i', strtotime((string)$item['published_at'])) : '' ?>
                  </span>
                </div>
                <h2 class="title"><?= h($item['title']) ?></h2>
                <p class="excerpt"><?= h((string)($item['excerpt'] ?? '')) ?></p>
                <span class="readmore">Weiterlesen →</span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container">
      <small>© <?= date('Y') ?> News</small>
    </div>
  </footer>
</body>
</html>