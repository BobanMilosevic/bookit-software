<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? 'Kunde';
$displayName = ($_SESSION['user_name'] ?? '') !== '' ? $_SESSION['user_name'] : ($_SESSION['user_email'] ?? '');

/* ── Nur Admins dürfen rein ─────────────────────────────── */
// Rolle direkt aus DB prüfen (nicht nur Session) — sicherer
try {
    $pdo = db();

    $roleCheck = $pdo->prepare("
        SELECT
            CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM users_has_Rollen uhr
                    INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                    WHERE uhr.users_idusers = ?
                      AND r.Rollenname = 'admin'
                ) THEN 'admin'
                WHEN EXISTS (
                    SELECT 1
                    FROM users_has_Rollen uhr
                    INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                    WHERE uhr.users_idusers = ?
                      AND r.Rollenname = 'employee'
                ) THEN 'employee'
                ELSE 'Kunde'
            END AS role
    ");
    $roleCheck->execute([$userId, $userId]);
    $liveRole = (string) ($roleCheck->fetchColumn() ?? 'Kunde');
} catch (Throwable) {
    $liveRole = (string) $userRole;
}

if ($liveRole !== 'admin') {
    header('Location: /index.php?error=kein_zugriff');
    exit;
}

/* ── Daten aus DB ────────────────────────────────────────── */
try {
    $pdo = db();

    // User-Statistiken
    $userStats = $pdo->query("
        SELECT
            COUNT(DISTINCT u.idusers)                                          AS gesamt,
            SUM(r.Rollenname = 'admin')                                        AS admins,
            SUM(r.Rollenname = 'employee')                                     AS mitarbeiter,
            SUM(r.Rollenname = 'Kunde' OR r.Rollenname IS NULL)                AS kunden
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers = u.idusers
        LEFT JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
    ")->fetch();

    // Letzte 10 User
    $users = $pdo->query("
        SELECT u.idusers, u.username, u.email,
               LOWER(COALESCE(r.Rollenname, 'Kunde')) AS role
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers = u.idusers
        LEFT JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
        ORDER BY u.idusers DESC
        LIMIT 10
    ")->fetchAll();

    // News-Statistiken
    $newsStats = $pdo->query("
    SELECT
        COUNT(*)                        AS gesamt,
        SUM(status = 'published')       AS veroeffentlicht,
        SUM(status = 'draft')           AS entwurf
    FROM news_posts
")->fetch();

    // Letzte 8 News-Beiträge
    $newsList = $pdo->query("
    SELECT id, title, slug, status,
           LEFT(content, 120)   AS excerpt,
           published_at,
           created_at
    FROM news_posts
    ORDER BY id DESC
    LIMIT 8
")->fetchAll();

    // Webshop-Statistiken
    $shopStats = $pdo->query("
        SELECT
            COUNT(*)        AS artikel_gesamt,
            SUM(Stueckzahl) AS stueck_gesamt,
            COUNT(DISTINCT kategorie_id) AS kategorien
        FROM Artikel
    ")->fetch();

    // Letzte 5 Artikel
    $shopArtikel = $pdo->query("
        SELECT a.Artikelnummer, a.Bezeichnung, a.Preis, a.Waehrung, a.Stueckzahl, a.bild_pfad, k.name AS kat
        FROM Artikel a
        LEFT JOIN Kategorien k ON k.id = a.kategorie_id
        ORDER BY a.Artikelnummer DESC
        LIMIT 5
    ")->fetchAll();

} catch (Throwable $e) {
    $userStats = ['gesamt' => 0, 'admins' => 0, 'mitarbeiter' => 0, 'kunden' => 0];
    $newsStats = ['gesamt' => 0, 'veroeffentlicht' => 0, 'entwurf' => 0];
    $shopStats = ['artikel_gesamt' => 0, 'stueck_gesamt' => 0, 'kategorien' => 0];
    $users = [];
    $newsList = [];
    $shopArtikel = [];
    $dbError = $e->getMessage();
}

function roleBadge(string $role): string
{
    return match ($role) {
        'admin' => '<span class="db-badge db-badge--red">Admin</span>',
        'employee' => '<span class="db-badge db-badge--blue">Mitarbeiter</span>',
        default => '<span class="db-badge db-badge--gray">Kunde</span>',
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
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --db-green: #118075;
            --db-green-pale: #e6f4f2;
            --db-blue: #4D8496;
            --db-blue-pale: #e8f2f6;
            --db-red: #80111B;
            --db-ink: #1e293b;
            --db-ink-2: #475569;
            --db-ink-3: #94a3b8;
            --db-bg: #f8fafc;
            --db-white: #fff;
            --db-border: #e2e8f0;
            --db-radius: 14px;
            --db-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 1px 2px rgba(15, 23, 42, .04);
            --db-shadow-md: 0 4px 20px rgba(15, 23, 42, .08);
        }

        body {
            background: var(--db-bg);
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--db-ink);
        }

        /* Header */
        .db-header {
            background: linear-gradient(135deg, #80111B, #4D8496);
            padding: 48px 0 56px;
            position: relative;
            overflow: hidden;
        }

        .db-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .db-header .container {
            position: relative;
            z-index: 1;
        }

        .db-header__label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .6);
            margin-bottom: .4rem;
        }

        .db-header h1 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 700;
            color: #fff;
            letter-spacing: -.025em;
            margin: 0 0 .4rem;
        }

        .db-header p {
            color: rgba(255, 255, 255, .7);
            margin: 0;
            font-size: .9rem;
        }

        .db-header__badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(255, 255, 255, .15);
            border: 1px solid rgba(255, 255, 255, .2);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            padding: .3rem .85rem;
            border-radius: 999px;
            letter-spacing: .05em;
            margin-top: .75rem;
        }

        /* Stat cards */
        .db-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin: -28px 0 2rem;
            position: relative;
            z-index: 2;
        }

        .db-stat {
            background: var(--db-white);
            border: 1px solid var(--db-border);
            border-radius: var(--db-radius);
            padding: 1.2rem 1.35rem;
            box-shadow: var(--db-shadow-md);
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .db-stat__icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .db-stat__icon--red {
            background: #fef2f2;
            color: #dc2626;
        }

        .db-stat__icon--green {
            background: var(--db-green-pale);
            color: var(--db-green);
        }

        .db-stat__icon--blue {
            background: var(--db-blue-pale);
            color: var(--db-blue);
        }

        .db-stat__icon--gray {
            background: #f1f5f9;
            color: #64748b;
        }

        .db-stat__icon--teal {
            background: #d1fae5;
            color: #059669;
        }

        .db-stat__icon--amber {
            background: #fffbeb;
            color: #d97706;
        }

        .db-stat__num {
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: -.04em;
            line-height: 1;
            color: var(--db-ink);
        }

        .db-stat__label {
            font-size: .73rem;
            color: var(--db-ink-3);
            font-weight: 500;
            margin-top: .12rem;
        }

        /* Card */
        .db-card {
            background: var(--db-white);
            border: 1px solid var(--db-border);
            border-radius: var(--db-radius);
            box-shadow: var(--db-shadow);
            overflow: hidden;
        }

        .db-card__head {
            padding: 1rem 1.35rem;
            border-bottom: 1px solid var(--db-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .db-card__title {
            font-size: .92rem;
            font-weight: 700;
            color: var(--db-ink);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .db-card__title i {
            font-size: .85rem;
        }

        .db-card__title i.icon-user {
            color: var(--db-blue);
        }

        .db-card__title i.icon-news {
            color: var(--db-green);
        }

        /* Table rows */
        .db-row {
            display: grid;
            align-items: center;
            padding: .85rem 1.35rem;
            border-bottom: 1px solid var(--db-border);
            transition: background .15s;
            gap: .75rem;
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
            background: var(--db-blue-pale);
            color: var(--db-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .db-row__name {
            font-size: .875rem;
            font-weight: 600;
            color: var(--db-ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .db-row__sub {
            font-size: .75rem;
            color: var(--db-ink-3);
            margin-top: .1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .db-row__news-title {
            font-size: .875rem;
            font-weight: 600;
            color: var(--db-ink);
        }

        .db-row__news-excerpt {
            font-size: .75rem;
            color: var(--db-ink-3);
            margin-top: .1rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Badges */
        .db-badge {
            display: inline-block;
            font-size: .68rem;
            font-weight: 700;
            padding: .18rem .6rem;
            border-radius: 999px;
            border: 1px solid transparent;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .db-badge--green {
            background: rgba(34, 197, 94, .1);
            border-color: rgba(34, 197, 94, .3);
            color: #16a34a;
        }

        .db-badge--blue {
            background: rgba(77, 132, 150, .12);
            border-color: rgba(77, 132, 150, .3);
            color: var(--db-blue);
        }

        .db-badge--gray {
            background: rgba(100, 116, 139, .1);
            border-color: rgba(100, 116, 139, .2);
            color: #64748b;
        }

        .db-badge--red {
            background: rgba(220, 38, 38, .08);
            border-color: rgba(220, 38, 38, .2);
            color: #dc2626;
        }

        .db-badge--amber {
            background: rgba(245, 158, 11, .1);
            border-color: rgba(245, 158, 11, .3);
            color: #d97706;
        }

        .db-empty {
            padding: 2.5rem;
            text-align: center;
            color: var(--db-ink-3);
        }

        .db-empty i {
            font-size: 1.8rem;
            display: block;
            margin-bottom: .5rem;
        }

        .db-empty p {
            font-size: .82rem;
            margin: 0;
        }

        @media(max-width:900px) {
            .db-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:576px) {
            .db-stats {
                grid-template-columns: 1fr 1fr;
            }

            .db-header {
                padding: 36px 0 56px;
            }
        }
    </style>
</head>

<body>

    <?php require $appRoot . '/views/partials/navbar.php'; ?>

    <!-- Header -->
    <header class="db-header">
        <div class="container">
            <div class="db-header__label">Administration</div>
            <h1>Admin Dashboard</h1>
            <p>Übersicht über alle Benutzer und News-Beiträge.</p>
            <div class="db-header__badge">
                <i class="bi bi-shield-fill-check"></i> Eingeloggt als
                <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </header>

    <main class="container py-4">

        <?php if (isset($dbError)): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4"
                style="border-radius:10px;font-size:.88rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><strong>DB-Fehler:</strong> <?= htmlspecialchars($dbError) ?></div>
            </div>
        <?php endif; ?>

        <!-- Stat cards -->
        <div class="db-stats">
            <div class="db-stat">
                <div class="db-stat__icon db-stat__icon--blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="db-stat__num"><?= (int) ($userStats['gesamt'] ?? 0) ?></div>
                    <div class="db-stat__label">Benutzer gesamt</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat__icon db-stat__icon--red"><i class="bi bi-shield-fill-check"></i></div>
                <div>
                    <div class="db-stat__num"><?= (int) ($userStats['admins'] ?? 0) ?></div>
                    <div class="db-stat__label">Admins</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat__icon db-stat__icon--green"><i class="bi bi-newspaper"></i></div>
                <div>
                    <div class="db-stat__num"><?= (int) ($newsStats['gesamt'] ?? 0) ?></div>
                    <div class="db-stat__label">News gesamt</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat__icon db-stat__icon--teal"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="db-stat__num"><?= (int) ($newsStats['veroeffentlicht'] ?? 0) ?></div>
                    <div class="db-stat__label">Veröffentlicht</div>
                </div>
            </div>
            <div class="db-stat">
                <div class="db-stat__icon db-stat__icon--green"><i class="bi bi-box-seam-fill"></i></div>
                <div>
                    <div class="db-stat__num"><?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?></div>
                    <div class="db-stat__label">Artikel im Shop</div>
                </div>
            </div>
        </div>

        <!-- Webshop-Widget -->
        <div class="db-card mb-4">
            <div class="db-card__head">
                <h2 class="db-card__title">
                    <i class="bi bi-shop-fill" style="color:var(--db-green);"></i> Webshop-Übersicht
                </h2>
                <a href="webshop-admin.php" class="db-badge db-badge--green" style="text-decoration:none;">
                    <i class="bi bi-pencil-square"></i> Verwalten
                </a>
            </div>
            <?php if (empty($shopArtikel)): ?>
                <div style="padding:2rem;text-align:center;color:var(--db-ink-3);">
                    <i class="bi bi-box-seam" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
                    <p style="font-size:.875rem;">Noch keine Artikel vorhanden.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:2px solid var(--db-border);">
                                <th style="padding:.6rem 1rem;text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--db-ink-3);font-weight:700;">Artikel</th>
                                <th style="padding:.6rem 1rem;text-align:left;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--db-ink-3);font-weight:700;">Kategorie</th>
                                <th style="padding:.6rem 1rem;text-align:right;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--db-ink-3);font-weight:700;">Preis</th>
                                <th style="padding:.6rem 1rem;text-align:right;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--db-ink-3);font-weight:700;">Bestand</th>
                                <th style="padding:.6rem 1rem;text-align:right;font-size:.7rem;text-transform:uppercase;letter-spacing:.07em;color:var(--db-ink-3);font-weight:700;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shopArtikel as $sa): ?>
                            <tr style="border-bottom:1px solid var(--db-border);">
                                <td style="padding:.7rem 1rem;">
                                    <div style="font-weight:600;"><?= htmlspecialchars($sa['Bezeichnung'] ?? '—') ?></div>
                                    <div style="font-size:.72rem;color:var(--db-ink-3);"><?= htmlspecialchars($sa['Artikelnummer']) ?></div>
                                </td>
                                <td style="padding:.7rem 1rem;">
                                    <span class="db-badge db-badge--gray"><?= htmlspecialchars($sa['kat'] ?? '—') ?></span>
                                </td>
                                <td style="padding:.7rem 1rem;text-align:right;font-weight:700;white-space:nowrap;">
                                    <?= number_format((float)($sa['Preis'] ?? 0), 2, ',', '.') ?> <?= htmlspecialchars($sa['Waehrung'] ?? 'EUR') ?>
                                </td>
                                <td style="padding:.7rem 1rem;text-align:right;">
                                    <?php $stk = (int)($sa['Stueckzahl'] ?? 0); ?>
                                    <?php if ($stk >= 999999): ?>
                                        <span style="color:var(--db-ink-3);font-size:.8rem;">∞</span>
                                    <?php elseif ($stk === 0): ?>
                                        <span class="db-badge db-badge--red">0</span>
                                    <?php else: ?>
                                        <span class="db-badge db-badge--<?= $stk < 5 ? 'amber' : 'green' ?>"><?= $stk ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:.7rem 1rem;text-align:right;">
                                    <a href="webshop-admin.php?edit=<?= urlencode($sa['Artikelnummer']) ?>"
                                       style="font-size:.8rem;color:var(--db-blue);text-decoration:none;font-weight:600;">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:.75rem 1.2rem;border-top:1px solid var(--db-border);text-align:right;">
                    <a href="webshop-admin.php" style="font-size:.8rem;color:var(--db-green);text-decoration:none;font-weight:700;">
                        Alle <?= (int)($shopStats['artikel_gesamt'] ?? 0) ?> Artikel ansehen <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="row g-4">

            <!-- Benutzer-Tabelle -->
            <div class="col-lg-6">
                <div class="db-card">
                    <div class="db-card__head">
                        <h2 class="db-card__title">
                            <i class="bi bi-people-fill icon-user"></i> Letzte Benutzer
                        </h2>
                        <span style="font-size:.75rem;color:var(--db-ink-3);font-weight:600;">
                            <?= (int) ($userStats['gesamt'] ?? 0) ?> gesamt
                        </span>
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="db-empty"><i class="bi bi-people"></i>
                            <p>Keine Benutzer gefunden.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <div class="db-row db-row--user">
                                <div class="db-avatar">
                                    <?= strtoupper(mb_substr($u['username'] ?: $u['email'], 0, 1)) ?>
                                </div>
                                <div style="min-width:0;">
                                    <div class="db-row__name">
                                        <?= htmlspecialchars($u['username'] ?: '—') ?>
                                    </div>
                                    <div class="db-row__sub"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                                <div style="flex-shrink:0;">
                                    <?= roleBadge($u['role'] ?? 'Kunde') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- User-Aufschlüsselung -->
                <div class="db-card mt-3">
                    <div class="db-card__head">
                        <h2 class="db-card__title"><i class="bi bi-bar-chart-fill icon-user"></i> Rollen-Aufschlüsselung
                        </h2>
                    </div>
                    <div class="db-row" style="grid-template-columns:1fr auto;padding:.9rem 1.35rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;">
                            <i class="bi bi-person-fill" style="color:#64748b;"></i> Kunden
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--db-ink);">
                                <?= (int) ($userStats['kunden'] ?? 0) ?>
                            </div>
                            <span class="db-badge db-badge--gray">Kunde</span>
                        </div>
                    </div>
                    <div class="db-row" style="grid-template-columns:1fr auto;padding:.9rem 1.35rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;">
                            <i class="bi bi-person-badge-fill" style="color:var(--db-blue);"></i> Mitarbeiter
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--db-ink);">
                                <?= (int) ($userStats['mitarbeiter'] ?? 0) ?>
                            </div>
                            <span class="db-badge db-badge--blue">employee</span>
                        </div>
                    </div>
                    <div class="db-row" style="grid-template-columns:1fr auto;padding:.9rem 1.35rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;">
                            <i class="bi bi-shield-fill-check" style="color:#dc2626;"></i> Admins
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--db-ink);">
                                <?= (int) ($userStats['admins'] ?? 0) ?>
                            </div>
                            <span class="db-badge db-badge--red">admin</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- News-Tabelle -->
            <div class="col-lg-6">
                <div class="db-card">
                    <div class="db-card__head">
                        <h2 class="db-card__title">
                            <i class="bi bi-newspaper icon-news"></i> News-Beiträge
                        </h2>
                        <span style="font-size:.75rem;color:var(--db-ink-3);font-weight:600;">
                            <?= (int) ($newsStats['gesamt'] ?? 0) ?> gesamt
                        </span>
                    </div>

                    <?php if (empty($newsList)): ?>
                        <div class="db-empty"><i class="bi bi-newspaper"></i>
                            <p>Keine News-Beiträge vorhanden.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($newsList as $n): ?>
                            <?php
                            $published = $n['status'] === 'published';
                            $dateStr = !empty($n['published_at'])
                                ? date('d.m.Y', strtotime($n['published_at']))
                                : date('d.m.Y', strtotime($n['created_at']));
                            ?>
                            <div class="db-row db-row--news">
                                <div style="min-width:0;">
                                    <div class="db-row__news-title"><?= htmlspecialchars($n['title']) ?></div>
                                    <?php if (!empty($n['excerpt'])): ?>
                                        <div class="db-row__news-excerpt"><?= htmlspecialchars($n['excerpt']) ?></div>
                                    <?php endif; ?>
                                    <div style="font-size:.72rem;color:var(--db-ink-3);margin-top:.25rem;">
                                        <i class="bi bi-calendar3"></i> <?= $dateStr ?>
                                    </div>
                                </div>
                                <div style="flex-shrink:0;">
                                    <?php if ($published): ?>
                                        <span class="db-badge db-badge--green">Online</span>
                                    <?php else: ?>
                                        <span class="db-badge db-badge--amber">Entwurf</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="news-manager.php" class="db-badge db-badge--green" style="text-decoration:none;">
                    <i class="bi bi-pencil-square"></i> Verwalten
                </a>

                <!-- News-Aufschlüsselung -->
                <div class="db-card mt-3">
                    <div class="db-card__head">
                        <h2 class="db-card__title"><i class="bi bi-bar-chart-fill icon-news"></i> News-Status</h2>
                    </div>
                    <div class="db-row" style="grid-template-columns:1fr auto;padding:.9rem 1.35rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i> Veröffentlicht
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--db-ink);">
                                <?= (int) ($newsStats['veroeffentlicht'] ?? 0) ?>
                            </div>
                            <span class="db-badge db-badge--green">Online</span>
                        </div>
                    </div>
                    <div class="db-row" style="grid-template-columns:1fr auto;padding:.9rem 1.35rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;font-size:.875rem;font-weight:600;">
                            <i class="bi bi-pencil-fill" style="color:#d97706;"></i> Entwürfe
                        </div>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="font-size:1.1rem;font-weight:700;color:var(--db-ink);">
                                <?= (int) ($newsStats['entwurf'] ?? 0) ?>
                            </div>
                            <span class="db-badge db-badge--amber">Entwurf</span>
                        </div>
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