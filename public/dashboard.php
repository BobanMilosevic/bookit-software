<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

$userId = (int) $_SESSION['user_id'];
$displayName = ($_SESSION['user_name'] ?? '') !== '' ? $_SESSION['user_name'] : ($_SESSION['user_email'] ?? '');

/* ── Admin-Check ─────────────────────────────────────────── */
try {
    $pdo = db();
    $rc = $pdo->prepare("
        SELECT CASE
            WHEN EXISTS (SELECT 1 FROM users_has_Rollen uhr JOIN Rollen r ON r.idRollen=uhr.Rollen_idRollen WHERE uhr.users_idusers=? AND r.Rollenname='admin') THEN 'admin'
            WHEN EXISTS (SELECT 1 FROM users_has_Rollen uhr JOIN Rollen r ON r.idRollen=uhr.Rollen_idRollen WHERE uhr.users_idusers=? AND r.Rollenname='employee') THEN 'employee'
            ELSE 'Kunde' END AS role
    ");
    $rc->execute([$userId, $userId]);
    $liveRole = (string) ($rc->fetchColumn() ?? 'Kunde');
} catch (Throwable) {
    $liveRole = 'Kunde';
}

if ($liveRole !== 'admin') {
    header('Location: /index.php?error=kein_zugriff');
    exit;
}

try {
    $pdo = db();

    $userStats = $pdo->query("
        SELECT COUNT(DISTINCT u.idusers) AS gesamt,
               SUM(r.Rollenname='admin') AS admins,
               SUM(r.Rollenname='IT MA' OR r.Rollenname='employee') AS mitarbeiter,
               SUM(r.Rollenname='Kunde') AS kunden
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers=u.idusers
        LEFT JOIN Rollen r ON r.idRollen=uhr.Rollen_idRollen
    ")->fetch();

    $recentUsers = $pdo->query("
        SELECT u.idusers, u.username, u.email,
               COALESCE(r.Rollenname,'Kunde') AS role
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers=u.idusers
        LEFT JOIN Rollen r ON r.idRollen=uhr.Rollen_idRollen
        ORDER BY u.idusers DESC LIMIT 6
    ")->fetchAll();

    $newsStats = $pdo->query("
        SELECT COUNT(*) AS gesamt,
               SUM(status='published') AS veroeffentlicht,
               SUM(status='draft') AS entwurf
        FROM news_posts
    ")->fetch();

    $newsList = $pdo->query("
        SELECT id, title, status, type,
               LEFT(content,100) AS excerpt,
               published_at, created_at
        FROM news_posts ORDER BY id DESC LIMIT 6
    ")->fetchAll();

    $shopStats = $pdo->query("
        SELECT COUNT(*) AS artikel_gesamt,
               SUM(Stueckzahl) AS stueck_gesamt,
               COUNT(DISTINCT kategorie_id) AS kategorien
        FROM Artikel
    ")->fetch();

    $shopArtikel = $pdo->query("
        SELECT a.Artikelnummer, a.Bezeichnung, a.Preis, a.Waehrung, a.Stueckzahl, k.name AS kat
        FROM Artikel a
        LEFT JOIN Kategorien k ON k.id=a.kategorie_id
        ORDER BY a.Artikelnummer DESC LIMIT 6
    ")->fetchAll();

    $belegeStats = $pdo->query("
        SELECT COUNT(*) AS gesamt, SUM(Betrag) AS umsatz FROM Belege
    ")->fetch();

} catch (Throwable $e) {
    $userStats = ['gesamt' => 0, 'admins' => 0, 'mitarbeiter' => 0, 'kunden' => 0];
    $newsStats = ['gesamt' => 0, 'veroeffentlicht' => 0, 'entwurf' => 0];
    $shopStats = ['artikel_gesamt' => 0, 'stueck_gesamt' => 0, 'kategorien' => 0];
    $belegeStats = ['gesamt' => 0, 'umsatz' => 0];
    $recentUsers = $newsList = $shopArtikel = [];
    $dbError = $e->getMessage();
}

function roleBadge(string $role): string
{
    return match (strtolower($role)) {
        'admin' => '<span class="db-badge db-badge--red"><i class="bi bi-shield-fill-check"></i> Admin</span>',
        'it ma', 'employee' => '<span class="db-badge db-badge--blue"><i class="bi bi-person-badge-fill"></i> MA</span>',
        'webshop' => '<span class="db-badge db-badge--green"><i class="bi bi-shop"></i> Webshop</span>',
        default => '<span class="db-badge db-badge--gray"><i class="bi bi-person-fill"></i> Kunde</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <style>
        :root {
            --g: #118075;
            --gp: #e6f4f2;
            --b: #4D8496;
            --bp: #e8f2f6;
            --r: #80111B;
            --rp: #fee2e2;
            --am: #d97706;
            --amp: #fef3c7;
            --ink: #1e293b;
            --i2: #475569;
            --i3: #94a3b8;
            --bg: #f8fafc;
            --wh: #fff;
            --br: #e2e8f0;
            --rad: 14px;
            --sh: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
            --shm: 0 4px 24px rgba(15, 23, 42, .09);
        }

        body {
            background: var(--bg);
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink);
        }

        .db-hero {
            background: linear-gradient(135deg, var(--r) 0%, #4D8496 100%);
            padding: 40px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .db-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .db-hero .container {
            position: relative;
            z-index: 1;
        }

        .db-hero__label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .55);
            margin-bottom: .4rem;
        }

        .db-hero h1 {
            font-size: clamp(1.6rem, 3vw, 2.3rem);
            font-weight: 800;
            color: #fff;
            letter-spacing: -.03em;
            margin: 0 0 .35rem;
        }

        .db-hero__sub {
            color: rgba(255, 255, 255, .7);
            font-size: .9rem;
            margin: 0;
        }

        .db-hero__chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            padding: .32rem .8rem;
            border-radius: 99px;
            margin-top: .9rem;
        }

        .db-kpi {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-top: -36px;
            position: relative;
            z-index: 2;
            margin-bottom: 2rem;
        }

        .db-kpi-card {
            background: var(--wh);
            border: 1px solid var(--br);
            border-radius: var(--rad);
            padding: 1.1rem 1.25rem;
            box-shadow: var(--shm);
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .db-kpi__icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .db-kpi__icon--blue {
            background: var(--bp);
            color: var(--b);
        }

        .db-kpi__icon--red {
            background: var(--rp);
            color: var(--r);
        }

        .db-kpi__icon--green {
            background: var(--gp);
            color: var(--g);
        }

        .db-kpi__icon--amber {
            background: var(--amp);
            color: var(--am);
        }

        .db-kpi__icon--gray {
            background: #f1f5f9;
            color: #64748b;
        }

        .db-kpi__num {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.04em;
            line-height: 1;
        }

        .db-kpi__label {
            font-size: .7rem;
            color: var(--i3);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-top: .15rem;
        }

        .db-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .db-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: 1.3rem 1rem;
            background: var(--wh);
            border: 1px solid var(--br);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            text-decoration: none;
            color: var(--ink);
            font-size: .82rem;
            font-weight: 700;
            text-align: center;
            transition: all .2s;
            position: relative;
            overflow: hidden;
        }

        .db-action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0;
            transition: opacity .2s;
        }

        .db-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shm);
            color: var(--ink);
        }

        .db-action-btn:hover::before {
            opacity: 1;
        }

        .db-action-btn--users::before {
            background: linear-gradient(90deg, var(--b), var(--g));
        }

        .db-action-btn--shop::before {
            background: linear-gradient(90deg, var(--g), #059669);
        }

        .db-action-btn--news::before {
            background: linear-gradient(90deg, var(--am), #f59e0b);
        }

        .db-action-btn--dl::before {
            background: linear-gradient(90deg, var(--r), var(--b));
        }

        .db-action-btn__icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .db-action-btn__icon--blue {
            background: var(--bp);
            color: var(--b);
        }

        .db-action-btn__icon--green {
            background: var(--gp);
            color: var(--g);
        }

        .db-action-btn__icon--amber {
            background: var(--amp);
            color: var(--am);
        }

        .db-action-btn__icon--red {
            background: var(--rp);
            color: var(--r);
        }

        .db-action-btn__label {
            color: var(--i2);
            font-weight: 400;
        }

        .db-section-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .db-section-hd h2 {
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: var(--ink);
        }

        .db-section-hd__link {
            font-size: .8rem;
            font-weight: 700;
            color: var(--g);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        .db-section-hd__link:hover {
            color: var(--b);
        }

        .db-card {
            background: var(--wh);
            border: 1px solid var(--br);
            border-radius: var(--rad);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .db-card__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.3rem;
            border-bottom: 1px solid var(--br);
        }

        .db-card__title {
            font-size: .88rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .db-row {
            display: grid;
            align-items: center;
            padding: .75rem 1.3rem;
            border-bottom: 1px solid var(--br);
            gap: .75rem;
            transition: background .15s;
        }

        .db-row:last-child {
            border-bottom: none;
        }

        .db-row:hover {
            background: #fafcff;
        }

        .db-row--user {
            grid-template-columns: 2rem 1fr auto;
        }

        .db-row--news {
            grid-template-columns: 1fr auto;
        }

        .db-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--bp);
            color: var(--b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .db-avatar--red {
            background: var(--rp);
            color: var(--r);
        }

        .db-row__name {
            font-size: .85rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .db-row__sub {
            font-size: .72rem;
            color: var(--i3);
            margin-top: .1rem;
        }

        .db-badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            font-size: .67rem;
            font-weight: 700;
            padding: .2rem .58rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .db-badge--red {
            background: var(--rp);
            color: var(--r);
        }

        .db-badge--blue {
            background: var(--bp);
            color: var(--b);
        }

        .db-badge--green {
            background: var(--gp);
            color: var(--g);
        }

        .db-badge--gray {
            background: #f1f5f9;
            color: #64748b;
        }

        .db-badge--amber {
            background: var(--amp);
            color: #92400e;
        }

        .db-breakdown {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .8rem 1.3rem;
            border-bottom: 1px solid var(--br);
            font-size: .85rem;
        }

        .db-breakdown:last-child {
            border-bottom: none;
        }

        .db-breakdown__label {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            color: var(--i2);
        }

        .db-breakdown__val {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--ink);
        }

        .db-empty {
            padding: 2.5rem;
            text-align: center;
            color: var(--i3);
            font-size: .85rem;
        }

        .db-empty i {
            font-size: 2rem;
            display: block;
            margin-bottom: .6rem;
        }

        .db-footer-link {
            display: block;
            text-align: center;
            padding: .65rem;
            font-size: .78rem;
            font-weight: 700;
            color: var(--g);
            border-top: 1px solid var(--br);
            text-decoration: none;
            transition: background .15s;
        }

        .db-footer-link:hover {
            background: var(--gp);
            color: var(--g);
        }

        @media(max-width:1024px) {
            .db-kpi {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width:768px) {
            .db-kpi {
                grid-template-columns: repeat(2, 1fr);
                margin-top: -24px;
            }

            .db-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:480px) {
            .db-kpi {
                grid-template-columns: 1fr 1fr;
            }

            .db-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>

    <?php require $appRoot . '/views/partials/navbar.php'; ?>

    <header class="db-hero">
        <div class="container">
            <div class="db-hero__label">Administration</div>
            <h1><i class="bi bi-grid-1x2-fill me-2"></i>Admin Dashboard</h1>
            <p class="db-hero__sub">Zentrale Verwaltung für Benutzer, Webshop, News und Downloads.</p>
            <div class="db-hero__chip">
                <i class="bi bi-shield-fill-check"></i>
                Eingeloggt als <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </header>

    <main class="container py-4">

        <?php if (isset($dbError)): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4"
                style="border-radius:10px;font-size:.88rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>DB-Fehler:</strong> <?= htmlspecialchars($dbError) ?>
            </div>
        <?php endif; ?>

        <!-- KPI -->
        <div class="db-kpi">
            <div class="db-kpi-card">
                <div class="db-kpi__icon db-kpi__icon--blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="db-kpi__num"><?= (int) ($userStats['gesamt'] ?? 0) ?></div>
                    <div class="db-kpi__label">Benutzer</div>
                </div>
            </div>
            <div class="db-kpi-card">
                <div class="db-kpi__icon db-kpi__icon--red"><i class="bi bi-shield-fill-check"></i></div>
                <div>
                    <div class="db-kpi__num"><?= (int) ($userStats['admins'] ?? 0) ?></div>
                    <div class="db-kpi__label">Admins</div>
                </div>
            </div>
            <div class="db-kpi-card">
                <div class="db-kpi__icon db-kpi__icon--green"><i class="bi bi-box-seam-fill"></i></div>
                <div>
                    <div class="db-kpi__num"><?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?></div>
                    <div class="db-kpi__label">Artikel</div>
                </div>
            </div>
            <div class="db-kpi-card">
                <div class="db-kpi__icon db-kpi__icon--amber"><i class="bi bi-newspaper"></i></div>
                <div>
                    <div class="db-kpi__num"><?= (int) ($newsStats['gesamt'] ?? 0) ?></div>
                    <div class="db-kpi__label">News</div>
                </div>
            </div>
            <div class="db-kpi-card">
                <div class="db-kpi__icon db-kpi__icon--gray"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="db-kpi__num"><?= (int) ($belegeStats['gesamt'] ?? 0) ?></div>
                    <div class="db-kpi__label">Belege</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="db-section-hd">
            <h2><i class="bi bi-lightning-charge-fill" style="color:var(--am);"></i> Schnellzugriff</h2>
        </div>
        <div class="db-actions mb-4">
            <a href="user-admin.php" class="db-action-btn db-action-btn--users">
                <div class="db-action-btn__icon db-action-btn__icon--blue"><i class="bi bi-people-fill"></i></div>
                <div>Userverwaltung</div>
                <div class="db-action-btn__label"><?= (int) ($userStats['gesamt'] ?? 0) ?> Benutzer</div>
            </a>
            <a href="webshop-admin.php" class="db-action-btn db-action-btn--shop">
                <div class="db-action-btn__icon db-action-btn__icon--green"><i class="bi bi-shop-fill"></i></div>
                <div>Webshop</div>
                <div class="db-action-btn__label"><?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?> Artikel</div>
            </a>
            <a href="news-manager.php" class="db-action-btn db-action-btn--news">
                <div class="db-action-btn__icon db-action-btn__icon--amber"><i class="bi bi-newspaper"></i></div>
                <div>News Manager</div>
                <div class="db-action-btn__label"><?= (int) ($newsStats['veroeffentlicht'] ?? 0) ?> online</div>
            </a>
            <a href="downloads.php" class="db-action-btn db-action-btn--dl">
                <div class="db-action-btn__icon db-action-btn__icon--red"><i class="bi bi-download"></i></div>
                <div>Downloads</div>
                <div class="db-action-btn__label">Kundenbereich</div>
            </a>
        </div>

        <!-- 3-Spalten -->
        <div class="row g-4">

            <!-- Benutzer -->
            <div class="col-lg-4">
                <div class="db-section-hd">
                    <h2><i class="bi bi-people-fill" style="color:var(--b);"></i> Benutzer</h2>
                    <a href="user-admin.php" class="db-section-hd__link">Alle verwalten <i
                            class="bi bi-arrow-right"></i></a>
                </div>
                <div class="db-card mb-3">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-clock-history" style="color:var(--b);"></i> Letzte
                            Zugänge</h3>
                        <span style="font-size:.72rem;color:var(--i3);font-weight:600;"><?= count($recentUsers) ?> von
                            <?= (int) ($userStats['gesamt'] ?? 0) ?></span>
                    </div>
                    <?php if (empty($recentUsers)): ?>
                        <div class="db-empty"><i class="bi bi-people"></i>Keine Benutzer.</div>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $u): ?>
                            <div class="db-row db-row--user">
                                <div class="db-avatar <?= strtolower($u['role'] ?? '') === 'admin' ? 'db-avatar--red' : '' ?>">
                                    <?= strtoupper(mb_substr($u['username'] ?: $u['email'], 0, 1)) ?>
                                </div>
                                <div style="min-width:0;">
                                    <div class="db-row__name"><?= htmlspecialchars($u['username'] ?: '—') ?></div>
                                    <div class="db-row__sub"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                                <?= roleBadge($u['role'] ?? 'Kunde') ?>
                            </div>
                        <?php endforeach; ?>
                        <a href="user-admin.php" class="db-footer-link">Alle <?= (int) ($userStats['gesamt'] ?? 0) ?> Benutzer
                            <i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <div class="db-card">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-bar-chart-fill" style="color:var(--b);"></i> Rollen
                        </h3>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-shield-fill-check" style="color:var(--r);"></i>
                            Admins</div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="db-breakdown__val"><?= (int) ($userStats['admins'] ?? 0) ?></div><span
                                class="db-badge db-badge--red">Admin</span>
                        </div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-person-badge-fill" style="color:var(--b);"></i>
                            Mitarbeiter</div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="db-breakdown__val"><?= (int) ($userStats['mitarbeiter'] ?? 0) ?></div><span
                                class="db-badge db-badge--blue">MA</span>
                        </div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-person-fill" style="color:#64748b;"></i> Kunden
                        </div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="db-breakdown__val"><?= (int) ($userStats['kunden'] ?? 0) ?></div><span
                                class="db-badge db-badge--gray">Kunde</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Webshop -->
            <div class="col-lg-4">
                <div class="db-section-hd">
                    <h2><i class="bi bi-shop-fill" style="color:var(--g);"></i> Webshop</h2>
                    <a href="webshop-admin.php" class="db-section-hd__link">Verwalten <i
                            class="bi bi-arrow-right"></i></a>
                </div>
                <div class="db-card mb-3">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-box-seam-fill" style="color:var(--g);"></i> Artikel
                        </h3>
                        <span
                            style="font-size:.72rem;color:var(--i3);font-weight:600;"><?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?>
                            gesamt</span>
                    </div>
                    <?php if (empty($shopArtikel)): ?>
                        <div class="db-empty"><i class="bi bi-box-seam"></i>Keine Artikel.</div>
                    <?php else: ?>
                        <?php foreach ($shopArtikel as $a):
                            $stk = (int) ($a['Stueckzahl'] ?? 0); ?>
                            <div class="db-row" style="grid-template-columns:1fr auto;">
                                <div style="min-width:0;">
                                    <div class="db-row__name"><?= htmlspecialchars($a['Bezeichnung'] ?? '—') ?></div>
                                    <div class="db-row__sub"><?= htmlspecialchars($a['kat'] ?? '—') ?> ·
                                        <?= number_format((float) ($a['Preis'] ?? 0), 2, ',', '.') ?>
                                        <?= htmlspecialchars($a['Waehrung'] ?? 'EUR') ?></div>
                                </div>
                                <?php if ($stk >= 999999): ?><span class="db-badge db-badge--green">∞</span>
                                <?php elseif ($stk === 0): ?><span class="db-badge db-badge--red">0</span>
                                <?php else: ?><span class="db-badge db-badge--<?= $stk < 5 ? 'amber' : 'green' ?>"><?= $stk ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <a href="webshop-admin.php" class="db-footer-link">Alle
                            <?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?> Artikel <i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <div class="db-card">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-bar-chart-fill" style="color:var(--g);"></i>
                            Überblick</h3>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-tags" style="color:var(--am);"></i> Kategorien
                        </div>
                        <div class="db-breakdown__val"><?= (int) ($shopStats['kategorien'] ?? 0) ?></div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-receipt" style="color:var(--b);"></i> Belege
                        </div>
                        <div class="db-breakdown__val"><?= (int) ($belegeStats['gesamt'] ?? 0) ?></div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-currency-euro" style="color:#16a34a;"></i>
                            Umsatz</div>
                        <div class="db-breakdown__val" style="color:#16a34a;">
                            <?= number_format((float) ($belegeStats['umsatz'] ?? 0), 2, ',', '.') ?> €</div>
                    </div>
                </div>
            </div>

            <!-- News -->
            <div class="col-lg-4">
                <div class="db-section-hd">
                    <h2><i class="bi bi-newspaper" style="color:var(--am);"></i> News</h2>
                    <a href="news-manager.php" class="db-section-hd__link">Verwalten <i
                            class="bi bi-arrow-right"></i></a>
                </div>
                <div class="db-card mb-3">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-clock-history" style="color:var(--am);"></i> Letzte
                            Beiträge</h3>
                        <span
                            style="font-size:.72rem;color:var(--i3);font-weight:600;"><?= (int) ($newsStats['gesamt'] ?? 0) ?>
                            gesamt</span>
                    </div>
                    <?php if (empty($newsList)): ?>
                        <div class="db-empty"><i class="bi bi-newspaper"></i>Keine Beiträge.</div>
                    <?php else: ?>
                        <?php foreach ($newsList as $n):
                            $pub = $n['status'] === 'published';
                            $d = !empty($n['published_at']) ? date('d.m.Y', strtotime($n['published_at'])) : date('d.m.Y', strtotime($n['created_at']));
                            ?>
                            <div class="db-row db-row--news">
                                <div style="min-width:0;">
                                    <div class="db-row__name"><?= htmlspecialchars($n['title']) ?></div>
                                    <div class="db-row__sub"><i class="bi bi-calendar3"></i> <?= $d ?> ·
                                        <?= $n['type'] === 'internal' ? 'Intern' : 'Öffentlich' ?></div>
                                </div>
                                <span
                                    class="db-badge db-badge--<?= $pub ? 'green' : 'amber' ?>"><?= $pub ? 'Online' : 'Entwurf' ?></span>
                            </div>
                        <?php endforeach; ?>
                        <a href="news-manager.php" class="db-footer-link">Alle <?= (int) ($newsStats['gesamt'] ?? 0) ?>
                            Beiträge <i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
                <div class="db-card">
                    <div class="db-card__head">
                        <h3 class="db-card__title"><i class="bi bi-bar-chart-fill" style="color:var(--am);"></i> Status
                        </h3>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-check-circle-fill" style="color:#16a34a;"></i>
                            Veröffentlicht</div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="db-breakdown__val"><?= (int) ($newsStats['veroeffentlicht'] ?? 0) ?></div><span
                                class="db-badge db-badge--green">Online</span>
                        </div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-pencil-fill" style="color:var(--am);"></i>
                            Entwürfe</div>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <div class="db-breakdown__val"><?= (int) ($newsStats['entwurf'] ?? 0) ?></div><span
                                class="db-badge db-badge--amber">Entwurf</span>
                        </div>
                    </div>
                    <div class="db-breakdown">
                        <div class="db-breakdown__label"><i class="bi bi-stack" style="color:var(--i3);"></i> Gesamt
                        </div>
                        <div class="db-breakdown__val"><?= (int) ($newsStats['gesamt'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer style="background:#1e293b;color:rgba(255,255,255,.4);padding:2rem 0;text-align:center;margin-top:3rem;">
        <div class="container">
            <p style="font-size:.8rem;margin:0;">&copy; 2026 BookIT Admin &nbsp;·&nbsp;
                <a href="/about.php" style="color:rgba(255,255,255,.4);">Über uns</a> &nbsp;·&nbsp;
                <a href="/impressum.php" style="color:rgba(255,255,255,.4);">Impressum</a>
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>