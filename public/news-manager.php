<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

/* ── Nur Admins ─────────────────────────────────────────── */
$userId = (int) ($_SESSION['user_id'] ?? 0);

try {
    $pdo = db();
    $roleCheck = $pdo->prepare("
        SELECT CASE
            WHEN EXISTS (
                SELECT 1 FROM users_has_Rollen uhr
                INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                WHERE uhr.users_idusers = ? AND r.Rollenname = 'admin'
            ) THEN 'admin' ELSE 'other' END AS role
    ");
    $roleCheck->execute([$userId]);
    $liveRole = (string) ($roleCheck->fetchColumn() ?? 'other');
} catch (Throwable) {
    $liveRole = 'other';
}

if ($liveRole !== 'admin') {
    header('Location: /index.php?error=kein_zugriff');
    exit;
}

/* ── Hilfsfunktionen ────────────────────────────────────── */
function slugify(string $text): string {
    $text = mb_strtolower($text);
    $text = str_replace(['ä','ö','ü','ß'], ['ae','oe','ue','ss'], $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') . '-' . substr(md5((string)time()), 0, 6);
}

/* ── POST-Aktionen ──────────────────────────────────────── */
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Erstellen / Aktualisieren ── */
    if (in_array($action, ['create', 'update'], true)) {
        $id        = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $title     = trim($_POST['title']   ?? '');
        $content   = trim($_POST['content'] ?? '');
        $type      = in_array($_POST['type'] ?? '', ['public','internal']) ? $_POST['type'] : 'public';
        $status    = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
        $publishAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

        if ($title === '' || $content === '') {
            $flash = ['type' => 'error', 'msg' => 'Titel und Inhalt dürfen nicht leer sein.'];
        } else {
            try {
                if ($id === null) {
                    $slug = slugify($title);
                    $stmt = $pdo->prepare("
                        INSERT INTO news_posts (title, slug, content, type, status, author_user_id, published_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $slug, $content, $type, $status, $userId, $publishAt]);
                    $flash = ['type' => 'success', 'msg' => 'Beitrag erfolgreich erstellt.'];
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE news_posts
                        SET title = ?, content = ?, type = ?, status = ?,
                            published_at = COALESCE(published_at, ?),
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $content, $type, $status, $publishAt, $id]);
                    $flash = ['type' => 'success', 'msg' => 'Beitrag erfolgreich aktualisiert.'];
                }
            } catch (Throwable $e) {
                $flash = ['type' => 'error', 'msg' => 'Fehler: ' . $e->getMessage()];
            }
        }
    }

    /* ── Löschen ── */
    if ($action === 'delete' && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM news_posts WHERE id = ?");
            $stmt->execute([(int) $_POST['id']]);
            $flash = ['type' => 'success', 'msg' => 'Beitrag gelöscht.'];
        } catch (Throwable $e) {
            $flash = ['type' => 'error', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }

    /* ── Status-Toggle (publish / unpublish) ── */
    if ($action === 'toggle_status' && isset($_POST['id'])) {
        try {
            $cur = $pdo->prepare("SELECT status FROM news_posts WHERE id = ?");
            $cur->execute([(int) $_POST['id']]);
            $curStatus = (string) ($cur->fetchColumn() ?? 'draft');
            $newStatus = $curStatus === 'published' ? 'draft' : 'published';
            $pubAt     = $newStatus === 'published' ? 'NOW()' : 'published_at';

            $upd = $pdo->prepare("
                UPDATE news_posts
                SET status = ?, published_at = IF(? = 'published', NOW(), published_at), updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$newStatus, $newStatus, (int) $_POST['id']]);
            $flash = ['type' => 'success', 'msg' => $newStatus === 'published' ? 'Beitrag veröffentlicht.' : 'Beitrag auf Entwurf gesetzt.'];
        } catch (Throwable $e) {
            $flash = ['type' => 'error', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── Daten laden ────────────────────────────────────────── */
$editPost = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE id = ?");
        $stmt->execute([(int) $_GET['edit']]);
        $editPost = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable) {}
}

try {
    $filter = $_GET['filter'] ?? 'all';
    $typeFilter = $_GET['type'] ?? 'all';

    $where = [];
    $params = [];

    if ($filter === 'published') { $where[] = "status = 'published'"; }
    elseif ($filter === 'draft') { $where[] = "status = 'draft'"; }

    if ($typeFilter === 'public')   { $where[] = "type = 'public'"; }
    elseif ($typeFilter === 'internal') { $where[] = "type = 'internal'"; }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $posts = $pdo->query("
        SELECT id, title, slug, type, status, published_at, created_at,
               LEFT(content, 100) AS excerpt
        FROM news_posts
        $whereSQL
        ORDER BY id DESC
        LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);

    $counts = $pdo->query("
        SELECT
            COUNT(*) AS gesamt,
            SUM(status='published') AS pub,
            SUM(status='draft') AS draft,
            SUM(type='public') AS pub_type,
            SUM(type='internal') AS int_type
        FROM news_posts
    ")->fetch(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $posts  = [];
    $counts = ['gesamt'=>0,'pub'=>0,'draft'=>0,'pub_type'=>0,'int_type'=>0];
}

function fmtDate(string $d): string {
    return date('d.m.Y', strtotime($d));
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News verwalten – BookIT Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">

    <style>
        :root {
            --db-green:      #118075;
            --db-green-pale: #e6f4f2;
            --db-blue:       #4D8496;
            --db-blue-pale:  #e8f2f6;
            --db-red:        #80111B;
            --db-red-pale:   #fdf2f3;
            --db-amber:      #d97706;
            --db-amber-pale: #fef3c7;
            --db-ink:        #1e293b;
            --db-ink-2:      #475569;
            --db-ink-3:      #94a3b8;
            --db-bg:         #f8fafc;
            --db-white:      #fff;
            --db-border:     #e2e8f0;
            --db-radius:     14px;
            --db-shadow:     0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
            --db-shadow-md:  0 4px 20px rgba(15,23,42,.08);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: var(--db-bg);
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--db-ink);
        }

        /* ── Page header ─────────────────────────────────── */
        .nm-header {
            background: linear-gradient(135deg, #118075, #4D8496);
            padding: 40px 0 48px;
            position: relative;
            overflow: hidden;
        }

        .nm-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }

        .nm-header .container { position: relative; z-index: 1; }

        .nm-header__label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: rgba(255,255,255,.55);
            margin-bottom: .3rem;
        }

        .nm-header__title {
            font-size: 1.9rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
            margin-bottom: .25rem;
        }

        .nm-header__sub {
            font-size: .9rem;
            color: rgba(255,255,255,.65);
        }

        .nm-header__actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        /* ── Stat pills ──────────────────────────────────── */
        .nm-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin: 2rem 0 1.5rem;
        }

        .nm-stat {
            background: var(--db-white);
            border: 1px solid var(--db-border);
            border-radius: 10px;
            padding: .65rem 1.2rem;
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: .82rem;
            font-weight: 600;
            color: var(--db-ink-2);
            box-shadow: var(--db-shadow);
        }

        .nm-stat__num {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--db-ink);
        }

        /* ── Card ────────────────────────────────────────── */
        .nm-card {
            background: var(--db-white);
            border-radius: var(--db-radius);
            box-shadow: var(--db-shadow-md);
            border: 1px solid var(--db-border);
            overflow: hidden;
        }

        .nm-card__head {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--db-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nm-card__title {
            font-size: .9rem;
            font-weight: 700;
            color: var(--db-ink);
            display: flex;
            align-items: center;
            gap: .45rem;
            margin: 0;
        }

        /* ── Filter bar ──────────────────────────────────── */
        .nm-filters {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .nm-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .8rem;
            border-radius: 8px;
            border: 1.5px solid var(--db-border);
            background: transparent;
            font-size: .78rem;
            font-weight: 600;
            color: var(--db-ink-2);
            text-decoration: none;
            transition: all .18s ease;
        }

        .nm-filter-btn:hover, .nm-filter-btn.active {
            border-color: var(--db-green);
            color: var(--db-green);
            background: var(--db-green-pale);
        }

        /* ── Table ───────────────────────────────────────── */
        .nm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .nm-table thead tr {
            background: #f8fafc;
            border-bottom: 1px solid var(--db-border);
        }

        .nm-table th {
            padding: .75rem 1.25rem;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--db-ink-3);
            text-align: left;
            white-space: nowrap;
        }

        .nm-table td {
            padding: .9rem 1.25rem;
            border-bottom: 1px solid var(--db-border);
            vertical-align: middle;
        }

        .nm-table tbody tr:last-child td { border-bottom: none; }

        .nm-table tbody tr:hover { background: #fafafa; }

        .nm-table__title {
            font-weight: 600;
            color: var(--db-ink);
            margin-bottom: .2rem;
        }

        .nm-table__excerpt {
            font-size: .78rem;
            color: var(--db-ink-3);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        /* ── Badges ──────────────────────────────────────── */
        .db-badge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .22rem .65rem;
            border-radius: 6px;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .db-badge--green  { background: var(--db-green-pale);  color: var(--db-green); }
        .db-badge--amber  { background: var(--db-amber-pale);  color: var(--db-amber); }
        .db-badge--blue   { background: var(--db-blue-pale);   color: var(--db-blue);  }
        .db-badge--red    { background: var(--db-red-pale);    color: var(--db-red);   }

        /* ── Action buttons ──────────────────────────────── */
        .nm-actions { display: flex; gap: .4rem; align-items: center; }

        .nm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: 1.5px solid var(--db-border);
            background: var(--db-white);
            color: var(--db-ink-2);
            font-size: .85rem;
            text-decoration: none;
            transition: all .18s ease;
            cursor: pointer;
        }

        .nm-btn:hover { color: var(--db-ink); border-color: var(--db-ink-2); background: #f1f5f9; }
        .nm-btn--danger:hover { color: var(--db-red); border-color: var(--db-red); background: var(--db-red-pale); }
        .nm-btn--success:hover { color: var(--db-green); border-color: var(--db-green); background: var(--db-green-pale); }

        /* ── Editor form ─────────────────────────────────── */
        .nm-form-card {
            background: var(--db-white);
            border-radius: var(--db-radius);
            box-shadow: var(--db-shadow-md);
            border: 1px solid var(--db-border);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .nm-form-head {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--db-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nm-form-head__title {
            font-size: .95rem;
            font-weight: 700;
            color: var(--db-ink);
            display: flex;
            align-items: center;
            gap: .5rem;
            margin: 0;
        }

        .nm-form-body { padding: 1.75rem; }

        .nm-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: var(--db-ink-2);
            margin-bottom: .4rem;
            letter-spacing: .03em;
        }

        .nm-input, .nm-textarea, .nm-select {
            width: 100%;
            padding: .65rem .9rem;
            border: 1.5px solid var(--db-border);
            border-radius: 9px;
            font-family: inherit;
            font-size: .88rem;
            color: var(--db-ink);
            background: var(--db-white);
            transition: border-color .18s ease, box-shadow .18s ease;
            outline: none;
        }

        .nm-input:focus, .nm-textarea:focus, .nm-select:focus {
            border-color: var(--db-green);
            box-shadow: 0 0 0 3px rgba(17,128,117,.12);
        }

        .nm-textarea { resize: vertical; min-height: 200px; line-height: 1.6; }

        .nm-row { display: grid; gap: 1.25rem; }
        .nm-row--2 { grid-template-columns: 1fr 1fr; }

        @media (max-width: 640px) { .nm-row--2 { grid-template-columns: 1fr; } }

        .nm-form-footer {
            padding: 1.1rem 1.75rem;
            background: #f8fafc;
            border-top: 1px solid var(--db-border);
            display: flex;
            gap: .75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-nm {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem 1.35rem;
            border-radius: 9px;
            font-size: .85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s ease;
        }

        .btn-nm--primary { background: var(--db-green); color: #fff; }
        .btn-nm--primary:hover { background: #0e6b62; color: #fff; }
        .btn-nm--ghost { background: transparent; border: 1.5px solid var(--db-border); color: var(--db-ink-2); }
        .btn-nm--ghost:hover { border-color: var(--db-ink-2); color: var(--db-ink); }
        .btn-nm--white { background: rgba(255,255,255,.15); color: #fff; border: 1.5px solid rgba(255,255,255,.3); }
        .btn-nm--white:hover { background: rgba(255,255,255,.25); color: #fff; }

        /* ── Flash ───────────────────────────────────────── */
        .nm-flash {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .9rem 1.2rem;
            border-radius: 10px;
            font-size: .87rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .nm-flash--success { background: var(--db-green-pale); color: var(--db-green); border: 1px solid #b6dcd8; }
        .nm-flash--error   { background: var(--db-red-pale);   color: var(--db-red);   border: 1px solid #f5cdd0; }

        /* ── Empty state ─────────────────────────────────── */
        .nm-empty {
            text-align: center;
            padding: 3.5rem 2rem;
            color: var(--db-ink-3);
        }

        .nm-empty i { font-size: 2.5rem; display: block; margin-bottom: .75rem; }

        /* ── Responsive table ────────────────────────────── */
        .nm-table-wrap { overflow-x: auto; }

        /* ── Back link ───────────────────────────────────── */
        .nm-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .8rem;
            font-weight: 600;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            margin-bottom: 1.2rem;
            transition: color .18s;
        }

        .nm-back:hover { color: #fff; }

        footer {
            background: var(--db-ink);
            color: rgba(255,255,255,.35);
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
            font-size: .8rem;
        }

        footer a { color: rgba(255,255,255,.4); text-decoration: none; }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

    <!-- ── Header ─────────────────────────────────────────── -->
    <div class="nm-header">
        <div class="container">
            <a href="dashboard.php" class="nm-back"><i class="bi bi-arrow-left"></i> Zurück zum Dashboard</a>
            <div class="nm-header__label">Admin · News</div>
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;">
                <div>
                    <div class="nm-header__title">News verwalten</div>
                    <div class="nm-header__sub">Beiträge erstellen, bearbeiten und veröffentlichen.</div>
                </div>
                <div class="nm-header__actions">
                    <a href="?new=1" class="btn-nm btn-nm--white">
                        <i class="bi bi-plus-lg"></i> Neuer Beitrag
                    </a>
                    <a href="dashboard.php" class="btn-nm btn-nm--white">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <main class="container" style="padding-top:2rem; padding-bottom:3rem;">

        <!-- Flash -->
        <?php if ($flash): ?>
            <div class="nm-flash nm-flash--<?= $flash['type'] ?>">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- ── Stats ──────────────────────────────────────── -->
        <div class="nm-stats">
            <div class="nm-stat">
                <i class="bi bi-newspaper" style="color:var(--db-ink-3);"></i>
                <span class="nm-stat__num"><?= (int)($counts['gesamt']??0) ?></span>
                <span>Gesamt</span>
            </div>
            <div class="nm-stat">
                <i class="bi bi-check-circle-fill" style="color:var(--db-green);"></i>
                <span class="nm-stat__num"><?= (int)($counts['pub']??0) ?></span>
                <span>Veröffentlicht</span>
            </div>
            <div class="nm-stat">
                <i class="bi bi-pencil-fill" style="color:var(--db-amber);"></i>
                <span class="nm-stat__num"><?= (int)($counts['draft']??0) ?></span>
                <span>Entwürfe</span>
            </div>
            <div class="nm-stat">
                <i class="bi bi-globe" style="color:var(--db-blue);"></i>
                <span class="nm-stat__num"><?= (int)($counts['pub_type']??0) ?></span>
                <span>Öffentlich</span>
            </div>
            <div class="nm-stat">
                <i class="bi bi-lock-fill" style="color:var(--db-red);"></i>
                <span class="nm-stat__num"><?= (int)($counts['int_type']??0) ?></span>
                <span>Intern</span>
            </div>
        </div>

        <!-- ── Editor ─────────────────────────────────────── -->
        <?php if (isset($_GET['new']) || $editPost !== null): ?>
        <div class="nm-form-card" id="editor">
            <div class="nm-form-head">
                <h2 class="nm-form-head__title">
                    <i class="bi bi-<?= $editPost ? 'pencil-square' : 'plus-circle-fill' ?>"
                       style="color:var(--db-green);"></i>
                    <?= $editPost ? 'Beitrag bearbeiten' : 'Neuer Beitrag' ?>
                </h2>
                <a href="news-manager.php" class="btn-nm btn-nm--ghost" style="padding:.35rem .8rem; font-size:.78rem;">
                    <i class="bi bi-x-lg"></i> Abbrechen
                </a>
            </div>

            <form method="POST" action="news-manager.php">
                <input type="hidden" name="action" value="<?= $editPost ? 'update' : 'create' ?>">
                <?php if ($editPost): ?>
                    <input type="hidden" name="id" value="<?= (int)$editPost['id'] ?>">
                <?php endif; ?>

                <div class="nm-form-body">

                    <!-- Titel -->
                    <div style="margin-bottom:1.25rem;">
                        <label class="nm-label" for="title">Titel *</label>
                        <input class="nm-input" type="text" id="title" name="title"
                               placeholder="z. B. Neue Funktion verfügbar"
                               value="<?= htmlspecialchars($editPost['title'] ?? '') ?>" required>
                    </div>

                    <!-- Typ & Status -->
                    <div class="nm-row nm-row--2" style="margin-bottom:1.25rem;">
                        <div>
                            <label class="nm-label" for="type">Zielgruppe</label>
                            <select class="nm-select" id="type" name="type">
                                <option value="public"   <?= ($editPost['type'] ?? 'public')   === 'public'   ? 'selected' : '' ?>>
                                    🌐 Öffentlich (Kunden)
                                </option>
                                <option value="internal" <?= ($editPost['type'] ?? '')         === 'internal' ? 'selected' : '' ?>>
                                    🔒 Intern (Mitarbeiter)
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="nm-label" for="status">Status</label>
                            <select class="nm-select" id="status" name="status">
                                <option value="draft"     <?= ($editPost['status'] ?? 'draft') === 'draft'     ? 'selected' : '' ?>>
                                    ✏️ Entwurf
                                </option>
                                <option value="published" <?= ($editPost['status'] ?? '')      === 'published' ? 'selected' : '' ?>>
                                    ✅ Veröffentlicht
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Inhalt -->
                    <div>
                        <label class="nm-label" for="content">Inhalt *</label>
                        <textarea class="nm-textarea" id="content" name="content"
                                  placeholder="Schreiben Sie hier den Beitragsinhalt…" required><?= htmlspecialchars($editPost['content'] ?? '') ?></textarea>
                    </div>

                </div>

                <div class="nm-form-footer">
                    <button type="submit" name="status_submit" value="published"
                            onclick="document.getElementById('status').value='published';"
                            class="btn-nm btn-nm--primary">
                        <i class="bi bi-send-fill"></i>
                        <?= $editPost ? 'Speichern & veröffentlichen' : 'Erstellen & veröffentlichen' ?>
                    </button>
                    <button type="submit" name="status_submit" value="draft"
                            onclick="document.getElementById('status').value='draft';"
                            class="btn-nm btn-nm--ghost">
                        <i class="bi bi-floppy"></i>
                        Als Entwurf speichern
                    </button>
                    <span style="font-size:.75rem; color:var(--db-ink-3); margin-left:auto;">
                        * Pflichtfelder
                    </span>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- ── Liste ──────────────────────────────────────── -->
        <div class="nm-card">
            <div class="nm-card__head">
                <h2 class="nm-card__title">
                    <i class="bi bi-list-ul" style="color:var(--db-green);"></i>
                    Alle Beiträge
                </h2>
                <!-- Filter -->
                <div class="nm-filters">
                    <?php
                    $f = $_GET['filter'] ?? 'all';
                    $t = $_GET['type']   ?? 'all';
                    ?>
                    <a href="?filter=all&type=<?= $t ?>"
                       class="nm-filter-btn <?= $f==='all' ? 'active' : '' ?>">Alle</a>
                    <a href="?filter=published&type=<?= $t ?>"
                       class="nm-filter-btn <?= $f==='published' ? 'active' : '' ?>">
                        <i class="bi bi-check-circle"></i> Online
                    </a>
                    <a href="?filter=draft&type=<?= $t ?>"
                       class="nm-filter-btn <?= $f==='draft' ? 'active' : '' ?>">
                        <i class="bi bi-pencil"></i> Entwurf
                    </a>
                    <span style="width:1px; background:var(--db-border); margin:0 .2rem;"></span>
                    <a href="?filter=<?= $f ?>&type=all"
                       class="nm-filter-btn <?= $t==='all' ? 'active' : '' ?>">
                        <i class="bi bi-list"></i> Alle Typen
                    </a>
                    <a href="?filter=<?= $f ?>&type=public"
                       class="nm-filter-btn <?= $t==='public' ? 'active' : '' ?>">
                        <i class="bi bi-globe"></i> Öffentlich
                    </a>
                    <a href="?filter=<?= $f ?>&type=internal"
                       class="nm-filter-btn <?= $t==='internal' ? 'active' : '' ?>">
                        <i class="bi bi-lock"></i> Intern
                    </a>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <div class="nm-empty">
                    <i class="bi bi-inbox"></i>
                    <strong>Keine Beiträge gefunden.</strong>
                    <p style="font-size:.85rem; margin-top:.35rem;">
                        <a href="?new=1" style="color:var(--db-green); font-weight:600;">Ersten Beitrag erstellen →</a>
                    </p>
                </div>

            <?php else: ?>
                <div class="nm-table-wrap">
                    <table class="nm-table">
                        <thead>
                            <tr>
                                <th style="width:40%;">Titel</th>
                                <th>Typ</th>
                                <th>Status</th>
                                <th>Datum</th>
                                <th style="text-align:right;">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $p): ?>
                            <tr>
                                <td>
                                    <div class="nm-table__title"><?= htmlspecialchars($p['title']) ?></div>
                                    <div class="nm-table__excerpt"><?= htmlspecialchars($p['excerpt'] ?? '') ?></div>
                                </td>
                                <td>
                                    <?php if ($p['type'] === 'internal'): ?>
                                        <span class="db-badge db-badge--red">
                                            <i class="bi bi-lock-fill"></i> Intern
                                        </span>
                                    <?php else: ?>
                                        <span class="db-badge db-badge--blue">
                                            <i class="bi bi-globe"></i> Öffentlich
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status'] === 'published'): ?>
                                        <span class="db-badge db-badge--green">
                                            <i class="bi bi-check-circle-fill"></i> Online
                                        </span>
                                    <?php else: ?>
                                        <span class="db-badge db-badge--amber">
                                            <i class="bi bi-pencil-fill"></i> Entwurf
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:.8rem; color:var(--db-ink-3); white-space:nowrap;">
                                    <i class="bi bi-calendar3"></i>
                                    <?= fmtDate($p['published_at'] ?? $p['created_at']) ?>
                                </td>
                                <td>
                                    <div class="nm-actions" style="justify-content:flex-end;">

                                        <!-- Bearbeiten -->
                                        <a href="?edit=<?= (int)$p['id'] ?>#editor"
                                           class="nm-btn" title="Bearbeiten">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Publish/Unpublish toggle -->
                                        <form method="POST" style="display:contents;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit"
                                                    class="nm-btn nm-btn--success"
                                                    title="<?= $p['status']==='published' ? 'Offline stellen' : 'Veröffentlichen' ?>">
                                                <i class="bi bi-<?= $p['status']==='published' ? 'eye-slash' : 'send-check' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Löschen -->
                                        <form method="POST" style="display:contents;"
                                              onsubmit="return confirm('Beitrag wirklich löschen?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" class="nm-btn nm-btn--danger" title="Löschen">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <footer>
        <div class="container">
            &copy; 2026 BookIT Admin &nbsp;·&nbsp;
            <a href="/impressum.php">Impressum</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll zum Editor nach Redirect (z.B. nach Bearbeiten-Klick)
        if (window.location.hash === '#editor') {
            setTimeout(() => {
                document.getElementById('editor')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 80);
        }
    </script>

</body>
</html>