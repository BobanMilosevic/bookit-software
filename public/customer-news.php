<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/bootstrap.php';
#require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

/* ── News aus DB laden ─────────────────────────────────── */
$posts = [];
$error = null;

try {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, title, slug, content, published_at, created_at
        FROM news_posts
        WHERE status = 'published'
          AND type = 'public'
        ORDER BY COALESCE(published_at, created_at) DESC
        LIMIT 20
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = $e->getMessage();
}

function formatDate(string $dateStr): string {
    $months = ['Januar','Februar','März','April','Mai','Juni',
               'Juli','August','September','Oktober','November','Dezember'];
    $ts = strtotime($dateStr);
    return intval(date('j', $ts)) . '. ' . $months[intval(date('n', $ts)) - 1] . ' ' . date('Y', $ts);
}

function excerpt(string $content, int $words = 22): string {
    $plain = strip_tags($content);
    $arr   = preg_split('/\s+/', trim($plain));
    if (count($arr) <= $words) return $plain;
    return implode(' ', array_slice($arr, 0, $words)) . ' …';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News – BookIT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">

    <!-- Display font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,700;9..144,900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Tokens ──────────────────────────────────────── */
        :root {
            --green:     #118075;
            --green-mid: #0e6b62;
            --blue:      #4D8496;
            --red:       #80111B;
            --ink:       #0f1c2e;
            --ink-60:    #4a5568;
            --ink-30:    #94a3b8;
            --bg:        #f5f4f0;
            --white:     #ffffff;
            --border:    #e2e0da;
            --radius:    16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Page header ─────────────────────────────────── */
        .news-header {
            background: var(--green);
            color: var(--white);
            padding: 72px 0 56px;
            position: relative;
            overflow: hidden;
        }

        .news-header__noise {
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(ellipse 80% 60% at 80% -10%, rgba(77,132,150,.55) 0%, transparent 70%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='.04'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .news-header .container { position: relative; z-index: 1; }

        .news-header__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-family: 'DM Sans', sans-serif;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.65);
            margin-bottom: 1rem;
        }

        .news-header h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.6rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -.025em;
            margin-bottom: 1rem;
        }

        .news-header p {
            font-size: 1.05rem;
            color: rgba(255,255,255,.75);
            max-width: 520px;
            line-height: 1.65;
        }

        /* ── Layout ──────────────────────────────────────── */
        .news-body { padding: 56px 0 96px; }

        /* ── Featured (first post) ───────────────────────── */
        .news-featured {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(15,28,46,.07), 0 12px 32px rgba(15,28,46,.06);
            margin-bottom: 2.5rem;
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .news-featured.visible { opacity: 1; transform: none; }

        .news-featured__visual {
            background: linear-gradient(135deg, var(--green), var(--blue));
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 280px;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .news-featured__visual::after {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Ccircle cx='60' cy='60' r='56' stroke='rgba(255,255,255,.07)' stroke-width='1.5' fill='none'/%3E%3Ccircle cx='60' cy='60' r='38' stroke='rgba(255,255,255,.05)' stroke-width='1.5' fill='none'/%3E%3Ccircle cx='60' cy='60' r='20' stroke='rgba(255,255,255,.04)' stroke-width='1.5' fill='none'/%3E%3C/svg%3E") center/200px repeat;
            opacity: .4;
        }

        .news-featured__icon {
            font-size: 4rem;
            color: rgba(255,255,255,.8);
            position: relative;
            z-index: 1;
        }

        .news-featured__body {
            padding: 2.5rem 2.8rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .news-featured__label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: .75rem;
        }

        .news-featured__title {
            font-family: 'Fraunces', serif;
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--ink);
            margin-bottom: .85rem;
        }

        .news-featured__excerpt {
            font-size: .935rem;
            color: var(--ink-60);
            line-height: 1.7;
            margin-bottom: 1.6rem;
        }

        .news-featured__meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: .78rem;
            color: var(--ink-30);
        }

        /* ── Grid ────────────────────────────────────────── */
        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .news-grid { grid-template-columns: repeat(2, 1fr); }
            .news-featured { grid-template-columns: 1fr; }
            .news-featured__visual { min-height: 180px; }
        }

        @media (max-width: 580px) {
            .news-grid { grid-template-columns: 1fr; }
        }

        /* ── Card ────────────────────────────────────────── */
        .nc {
            background: var(--white);
            border-radius: var(--radius);
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: .75rem;
            box-shadow: 0 1px 3px rgba(15,28,46,.06), 0 4px 16px rgba(15,28,46,.04);
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .55s ease, transform .55s ease, box-shadow .25s ease;
            cursor: default;
        }

        .nc.visible { opacity: 1; transform: none; }

        .nc:hover {
            box-shadow: 0 4px 12px rgba(15,28,46,.1), 0 16px 40px rgba(15,28,46,.08);
            transform: translateY(-3px);
        }

        .nc__date {
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            color: var(--ink-30);
        }

        .nc__title {
            font-family: 'Fraunces', serif;
            font-size: 1.12rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--ink);
        }

        .nc__excerpt {
            font-size: .875rem;
            color: var(--ink-60);
            line-height: 1.65;
            flex: 1;
        }

        .nc__footer {
            display: flex;
            align-items: center;
            padding-top: .5rem;
            border-top: 1px solid var(--border);
        }

        .nc__btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .8rem;
            font-weight: 600;
            color: var(--green);
            text-decoration: none;
            transition: gap .2s ease, color .2s ease;
        }

        .nc__btn:hover { gap: .65rem; color: var(--green-mid); }

        /* ── Number accent ───────────────────────────────── */
        .nc__num {
            display: flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e8f4f2;
            font-size: .75rem;
            font-weight: 700;
            color: var(--green);
            flex-shrink: 0;
        }

        /* ── Empty / Error ───────────────────────────────── */
        .news-empty {
            text-align: center;
            padding: 5rem 2rem;
            background: var(--white);
            border-radius: var(--radius);
            color: var(--ink-60);
        }

        .news-empty i {
            font-size: 3rem;
            color: var(--ink-30);
            display: block;
            margin-bottom: 1rem;
        }

        /* ── Footer ──────────────────────────────────────── */
        .news-footer {
            background: var(--ink);
            color: rgba(255,255,255,.4);
            text-align: center;
            padding: 2.5rem 0;
            font-size: .82rem;
        }

        .news-footer a { color: rgba(255,255,255,.5); text-decoration: none; }
        .news-footer a:hover { color: rgba(255,255,255,.8); }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

    <!-- ── Header ─────────────────────────────────────────── -->
    <header class="news-header">
        <div class="news-header__noise"></div>
        <div class="container">
            <div class="news-header__eyebrow">
                <i class="bi bi-newspaper"></i>
                BookIT Aktuell
            </div>
            <h1>Was gibt es Neues?</h1>
            <p>Die neuesten Entwicklungen, Updates und Ankündigungen direkt von uns.</p>
        </div>
    </header>

    <!-- ── Content ────────────────────────────────────────── -->
    <main class="news-body">
        <div class="container">

            <?php if ($error): ?>
                <div class="news-empty">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Datenbankfehler</strong>
                    <p style="margin-top:.5rem; font-size:.85rem;"><?= htmlspecialchars($error) ?></p>
                </div>

            <?php elseif (empty($posts)): ?>
                <div class="news-empty">
                    <i class="bi bi-newspaper"></i>
                    <strong>Noch keine News vorhanden.</strong>
                    <p style="margin-top:.5rem;">Schauen Sie bald wieder vorbei!</p>
                </div>

            <?php else: ?>

                <?php $featured = array_shift($posts); ?>
                <?php $featDate = formatDate($featured['published_at'] ?? $featured['created_at']); ?>

                <!-- Featured Post -->
                <div class="news-featured reveal-item">
                    <div class="news-featured__visual">
                        <i class="bi bi-megaphone-fill news-featured__icon"></i>
                    </div>
                    <div class="news-featured__body">
                        <div class="news-featured__label">Neueste Meldung</div>
                        <h2 class="news-featured__title"><?= htmlspecialchars($featured['title']) ?></h2>
                        <p class="news-featured__excerpt"><?= htmlspecialchars(excerpt($featured['content'], 35)) ?></p>
                        <div class="news-featured__meta">
                            <span><i class="bi bi-calendar3"></i> <?= $featDate ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($posts)): ?>
                <div class="news-grid">
                    <?php foreach ($posts as $i => $post): ?>
                        <?php $date = formatDate($post['published_at'] ?? $post['created_at']); ?>
                        <article class="nc reveal-item" style="transition-delay: <?= ($i % 3) * 80 ?>ms;">
                            <div style="display:flex; align-items:flex-start; gap:.75rem;">
                                <div class="nc__num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                                <div class="nc__date"><?= $date ?></div>
                            </div>
                            <h3 class="nc__title"><?= htmlspecialchars($post['title']) ?></h3>
                            <p class="nc__excerpt"><?= htmlspecialchars(excerpt($post['content'])) ?></p>
                            <div class="nc__footer">
                                <a href="news-detail.php?slug=<?= urlencode($post['slug']) ?>" class="nc__btn">
                                    Weiterlesen <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </main>

    <footer class="news-footer">
        <div class="container">
            &copy; 2026 BookIT. Alle Rechte vorbehalten.
            &nbsp;·&nbsp;<a href="impressum.php">Impressum</a>
            &nbsp;·&nbsp;<a href="#contact">Kontakt</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

        document.querySelectorAll('.reveal-item').forEach(el => io.observe(el));
    </script>

</body>
</html>