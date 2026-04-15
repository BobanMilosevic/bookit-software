<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

$userId   = (int) $_SESSION['user_id'];

/* ── Admin-Check ───────────────────────────────────────── */
try {
    $pdo = db();
    $roleCheck = $pdo->prepare("
        SELECT CASE
            WHEN EXISTS (
                SELECT 1 FROM users_has_Rollen uhr
                INNER JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
                WHERE uhr.users_idusers = ? AND r.Rollenname = 'admin'
            ) THEN 'admin' ELSE 'other'
        END AS role
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

$pdo = db();
$feedback = [];

/* ══════════════════════════════════════════════════════════
   ACTION HANDLER
   ══════════════════════════════════════════════════════════ */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ── Artikel hinzufügen ─────────────────────────────────── */
if ($action === 'artikel_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $artikelnr   = trim($_POST['artikelnummer'] ?? '');
    $bezeichnung = trim($_POST['bezeichnung'] ?? '');
    $katId       = (int) ($_POST['kategorie_id'] ?? 0);
    $preis       = str_replace(',', '.', trim($_POST['preis'] ?? '0'));
    $waehrung    = trim($_POST['waehrung'] ?? 'EUR');
    $stueckzahl  = (int) ($_POST['stueckzahl'] ?? 0);
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    $bildPfad    = trim($_POST['bild_pfad'] ?? '');

    if (!$artikelnr || !$bezeichnung || !$katId) {
        $feedback = ['type' => 'danger', 'msg' => 'Artikelnummer, Bezeichnung und Kategorie sind Pflichtfelder.'];
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Artikel (Artikelnummer, Bezeichnung, kategorie_id, Preis, Waehrung, Stueckzahl, Beschreibung, bild_pfad)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$artikelnr, $bezeichnung, $katId, $preis ?: null, $waehrung, $stueckzahl, $beschreibung ?: null, $bildPfad ?: null]);
            $feedback = ['type' => 'success', 'msg' => "Artikel {$bezeichnung} wurde erfolgreich hinzugefügt."];
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                $feedback = ['type' => 'danger', 'msg' => 'Artikelnummer bereits vorhanden.'];
            } else {
                $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
            }
        }
    }
}

/* ── Artikel bearbeiten ─────────────────────────────────── */
if ($action === 'artikel_edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $origNr      = trim($_POST['orig_artikelnummer'] ?? '');
    $bezeichnung = trim($_POST['bezeichnung'] ?? '');
    $katId       = (int) ($_POST['kategorie_id'] ?? 0);
    $preis       = str_replace(',', '.', trim($_POST['preis'] ?? '0'));
    $waehrung    = trim($_POST['waehrung'] ?? 'EUR');
    $stueckzahl  = (int) ($_POST['stueckzahl'] ?? 0);
    $beschreibung = trim($_POST['beschreibung'] ?? '');
    $bildPfad    = trim($_POST['bild_pfad'] ?? '');

    if (!$origNr || !$bezeichnung || !$katId) {
        $feedback = ['type' => 'danger', 'msg' => 'Pflichtfelder fehlen.'];
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE Artikel
                SET Bezeichnung=?, kategorie_id=?, Preis=?, Waehrung=?, Stueckzahl=?, Beschreibung=?, bild_pfad=?
                WHERE Artikelnummer=?
            ");
            $stmt->execute([$bezeichnung, $katId, $preis ?: null, $waehrung, $stueckzahl, $beschreibung ?: null, $bildPfad ?: null, $origNr]);
            $feedback = ['type' => 'success', 'msg' => "Artikel wurde erfolgreich aktualisiert."];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── Artikel löschen ────────────────────────────────────── */
if ($action === 'artikel_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $artikelnr = trim($_POST['artikelnummer'] ?? '');
    if ($artikelnr) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Artikel WHERE Artikelnummer = ?");
            $stmt->execute([$artikelnr]);
            $feedback = ['type' => 'success', 'msg' => "Artikel wurde gelöscht."];
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'foreign key')) {
                $feedback = ['type' => 'danger', 'msg' => 'Artikel kann nicht gelöscht werden – er ist noch in Bestellungen vorhanden.'];
            } else {
                $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
            }
        }
    }
}

/* ── Kategorie hinzufügen ───────────────────────────────── */
if ($action === 'kat_add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $katName = trim($_POST['kat_name'] ?? '');
    if (!$katName) {
        $feedback = ['type' => 'danger', 'msg' => 'Kategoriename ist ein Pflichtfeld.'];
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO Kategorien (name) VALUES (?)");
            $stmt->execute([$katName]);
            $feedback = ['type' => 'success', 'msg' => "Kategorie {$katName} wurde hinzugefügt."];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => str_contains($e->getMessage(), 'Duplicate') ? 'Kategorie existiert bereits.' : 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── Kategorie löschen ──────────────────────────────────── */
if ($action === 'kat_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $katId = (int) ($_POST['kat_id'] ?? 0);
    if ($katId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Kategorien WHERE id = ?");
            $stmt->execute([$katId]);
            $feedback = ['type' => 'success', 'msg' => "Kategorie wurde gelöscht."];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => 'Kategorie kann nicht gelöscht werden – es sind noch Artikel zugewiesen.'];
        }
    }
}

/* ══════════════════════════════════════════════════════════
   DATEN LADEN
   ══════════════════════════════════════════════════════════ */
try {
    $artikel = $pdo->query("
        SELECT a.*, k.name AS kategorie_name
        FROM Artikel a
        LEFT JOIN Kategorien k ON k.id = a.kategorie_id
        ORDER BY a.kategorie_id, a.Artikelnummer
    ")->fetchAll();

    $kategorien = $pdo->query("SELECT * FROM Kategorien ORDER BY id")->fetchAll();

    $shopStats = $pdo->query("
        SELECT
            COUNT(*) AS artikel_gesamt,
            SUM(Stueckzahl) AS stueck_gesamt,
            SUM(Preis * LEAST(Stueckzahl,999999)) AS lagerwert
        FROM Artikel
    ")->fetch();

} catch (Throwable $e) {
    $artikel = [];
    $kategorien = [];
    $shopStats = ['artikel_gesamt' => 0, 'stueck_gesamt' => 0, 'lagerwert' => 0];
    $dbError = $e->getMessage();
}

/* Artikel für Bearbeitungs-Modal vorladen */
$editArtikel = null;
if (isset($_GET['edit'])) {
    foreach ($artikel as $a) {
        if ($a['Artikelnummer'] === $_GET['edit']) {
            $editArtikel = $a;
            break;
        }
    }
}

/* Artikel nach Kategorie gruppieren */
$byKat = [];
foreach ($artikel as $a) {
    $byKat[$a['kategorie_name'] ?? 'Sonstiges'][] = $a;
}

$displayName = ($_SESSION['user_name'] ?? '') ?: ($_SESSION['user_email'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop-Verwaltung – BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <style>
        :root {
            --db-green:      #118075;
            --db-green-pale: #e6f4f2;
            --db-blue:       #4D8496;
            --db-blue-pale:  #e8f2f6;
            --db-red:        #80111B;
            --db-amber:      #d97706;
            --db-ink:        #1e293b;
            --db-ink-2:      #475569;
            --db-ink-3:      #94a3b8;
            --db-bg:         #f8fafc;
            --db-white:      #fff;
            --db-border:     #e2e8f0;
            --db-radius:     14px;
            --db-shadow:     0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04);
            --db-shadow-md:  0 4px 20px rgba(15,23,42,.08);
        }
        body { background:var(--db-bg); font-family:system-ui,-apple-system,'Segoe UI',sans-serif; color:var(--db-ink); }

        /* ── Header ── */
        .db-header {
            background: linear-gradient(135deg, var(--db-green), var(--db-blue));
            padding: 44px 0 52px;
            position: relative; overflow: hidden;
        }
        .db-header::before {
            content:''; position:absolute; inset:0;
            background-image: linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),
                              linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
            background-size:48px 48px; pointer-events:none;
        }
        .db-header .container { position:relative; z-index:1; }
        .db-header__label { font-size:.72rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.6); margin-bottom:.4rem; }
        .db-header h1 { font-size:clamp(1.6rem,3vw,2.2rem); font-weight:700; color:#fff; letter-spacing:-.025em; margin:0 0 .4rem; }
        .db-header p { color:rgba(255,255,255,.7); margin:0; font-size:.9rem; }
        .db-header__badge {
            display:inline-flex; align-items:center; gap:.35rem;
            background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2);
            color:#fff; font-size:.72rem; font-weight:700;
            padding:.3rem .8rem; border-radius:99px; margin-top:1rem;
        }
        .db-header__actions { margin-top:1.25rem; display:flex; gap:.6rem; flex-wrap:wrap; }
        .db-header__btn {
            display:inline-flex; align-items:center; gap:.4rem;
            background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
            color:#fff; font-size:.8rem; font-weight:600;
            padding:.45rem 1rem; border-radius:8px; text-decoration:none;
            transition:background .2s;
        }
        .db-header__btn:hover { background:rgba(255,255,255,.25); color:#fff; }
        .db-header__btn--primary { background:rgba(255,255,255,.9); color:var(--db-green); }
        .db-header__btn--primary:hover { background:#fff; color:var(--db-green); }

        /* ── Stat Cards ── */
        .db-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2rem; }
        .db-stat {
            background:var(--db-white); border:1px solid var(--db-border);
            border-radius:var(--db-radius); padding:1.2rem 1.4rem;
            display:flex; align-items:center; gap:1rem;
            box-shadow:var(--db-shadow);
        }
        .db-stat__icon {
            width:44px; height:44px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.2rem; flex-shrink:0;
        }
        .db-stat__icon--green { background:var(--db-green-pale); color:var(--db-green); }
        .db-stat__icon--blue  { background:var(--db-blue-pale);  color:var(--db-blue); }
        .db-stat__icon--amber { background:#fef3c7; color:var(--db-amber); }
        .db-stat__num { font-size:1.55rem; font-weight:800; color:var(--db-ink); letter-spacing:-.03em; }
        .db-stat__label { font-size:.75rem; color:var(--db-ink-3); font-weight:600; text-transform:uppercase; letter-spacing:.06em; margin-top:.15rem; }

        /* ── Card ── */
        .db-card {
            background:var(--db-white); border:1px solid var(--db-border);
            border-radius:var(--db-radius); box-shadow:var(--db-shadow);
            overflow:hidden; margin-bottom:1.5rem;
        }
        .db-card__head {
            display:flex; align-items:center; justify-content:space-between;
            padding:1rem 1.4rem; border-bottom:1px solid var(--db-border);
        }
        .db-card__title { font-size:.95rem; font-weight:700; margin:0; display:flex; align-items:center; gap:.5rem; }

        /* ── Artikel-Tabelle ── */
        .art-table { width:100%; border-collapse:collapse; font-size:.875rem; }
        .art-table th { padding:.65rem 1rem; background:#f8fafc; border-bottom:2px solid var(--db-border); font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; color:var(--db-ink-3); font-weight:700; white-space:nowrap; }
        .art-table td { padding:.75rem 1rem; border-bottom:1px solid var(--db-border); vertical-align:middle; }
        .art-table tr:last-child td { border-bottom:none; }
        .art-table tr:hover td { background:#f8fafc; }
        .art-thumb { width:44px; height:44px; object-fit:contain; border-radius:6px; border:1px solid var(--db-border); background:#f8fafc; }
        .art-thumb-placeholder { width:44px; height:44px; border-radius:6px; background:var(--db-green-pale); display:flex; align-items:center; justify-content:center; color:var(--db-green); font-size:1.1rem; }

        /* ── Kategorie-Header ── */
        .kat-header { background:linear-gradient(90deg,var(--db-green-pale),transparent); padding:.55rem 1.2rem; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--db-green); border-bottom:1px solid var(--db-border); }

        /* ── Badges ── */
        .db-badge { display:inline-flex; align-items:center; gap:.25rem; font-size:.68rem; font-weight:700; padding:.25rem .6rem; border-radius:6px; letter-spacing:.04em; }
        .db-badge--green { background:var(--db-green-pale); color:var(--db-green); }
        .db-badge--blue  { background:var(--db-blue-pale);  color:var(--db-blue); }
        .db-badge--amber { background:#fef3c7; color:#92400e; }
        .db-badge--red   { background:#fee2e2; color:var(--db-red); }
        .db-badge--gray  { background:#f1f5f9; color:#64748b; }

        /* ── Action Buttons ── */
        .btn-icon { border:none; background:transparent; padding:.35rem .45rem; border-radius:7px; font-size:.95rem; cursor:pointer; transition:background .15s,color .15s; line-height:1; }
        .btn-icon--edit   { color:var(--db-blue);  } .btn-icon--edit:hover   { background:var(--db-blue-pale); }
        .btn-icon--delete { color:var(--db-red);   } .btn-icon--delete:hover { background:#fee2e2; }

        /* ── Modal ── */
        .modal-header { border-bottom:1px solid var(--db-border); }
        .modal-footer { border-top:1px solid var(--db-border); }
        .form-label { font-size:.8rem; font-weight:700; color:var(--db-ink-2); margin-bottom:.3rem; }
        .form-control, .form-select { font-size:.875rem; border-color:var(--db-border); border-radius:8px; }
        .form-control:focus, .form-select:focus { border-color:var(--db-green); box-shadow:0 0 0 3px rgba(17,128,117,.12); }
        .form-text { font-size:.72rem; color:var(--db-ink-3); }

        /* ── Kategorie-Liste ── */
        .kat-list-item {
            display:flex; align-items:center; justify-content:space-between;
            padding:.7rem 1.2rem; border-bottom:1px solid var(--db-border);
        }
        .kat-list-item:last-child { border-bottom:none; }

        /* ── Feedback ── */
        .feedback-bar { border-radius:10px; font-size:.88rem; }

        /* ── Responsive ── */
        @media(max-width:768px) {
            .db-stats { grid-template-columns:1fr 1fr; }
            .art-table .hide-sm { display:none; }
        }
        @media(max-width:480px) {
            .db-stats { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<?php require $appRoot . '/views/partials/navbar.php'; ?>

<!-- Header -->
<header class="db-header">
    <div class="container">
        <div class="db-header__label">Administration</div>
        <h1><i class="bi bi-shop me-2"></i>Webshop-Verwaltung</h1>
        <p>Artikel und Kategorien verwalten — hinzufügen, bearbeiten, löschen.</p>
        <div class="db-header__badge">
            <i class="bi bi-shield-fill-check"></i> <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="db-header__actions">
            <a href="dashboard.php" class="db-header__btn"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <button class="db-header__btn db-header__btn--primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-plus-lg"></i> Neuer Artikel
            </button>
            <button class="db-header__btn" data-bs-toggle="modal" data-bs-target="#modalKat">
                <i class="bi bi-tags"></i> Kategorien
            </button>
        </div>
    </div>
</header>

<main class="container py-4">

    <?php if ($feedback): ?>
        <div class="alert alert-<?= $feedback['type'] ?> feedback-bar d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-<?= $feedback['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
            <?= htmlspecialchars($feedback['msg']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($dbError)): ?>
        <div class="alert alert-warning feedback-bar d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>DB-Fehler:</strong> <?= htmlspecialchars($dbError) ?>
        </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--green"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <div class="db-stat__num"><?= (int) ($shopStats['artikel_gesamt'] ?? 0) ?></div>
                <div class="db-stat__label">Artikel gesamt</div>
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--blue"><i class="bi bi-stack"></i></div>
            <div>
                <div class="db-stat__num"><?= number_format((int) ($shopStats['stueck_gesamt'] ?? 0)) ?></div>
                <div class="db-stat__label">Stück auf Lager</div>
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--amber"><i class="bi bi-tags-fill"></i></div>
            <div>
                <div class="db-stat__num"><?= count($kategorien) ?></div>
                <div class="db-stat__label">Kategorien</div>
            </div>
        </div>
    </div>

    <!-- Artikel-Tabelle -->
    <div class="db-card">
        <div class="db-card__head">
            <h2 class="db-card__title"><i class="bi bi-box-seam-fill" style="color:var(--db-green);"></i> Alle Artikel</h2>
            <span style="font-size:.75rem;color:var(--db-ink-3);font-weight:600;"><?= count($artikel) ?> Einträge</span>
        </div>

        <?php if (empty($artikel)): ?>
            <div style="padding:3rem;text-align:center;color:var(--db-ink-3);">
                <i class="bi bi-box-seam" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
                <p>Noch keine Artikel vorhanden.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="art-table">
                    <thead>
                        <tr>
                            <th>Bild</th>
                            <th>Art.-Nr.</th>
                            <th>Bezeichnung</th>
                            <th class="hide-sm">Kategorie</th>
                            <th>Preis</th>
                            <th class="hide-sm">Stk.</th>
                            <th style="text-align:right;">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($artikel as $a):
                            $imgSrc = htmlspecialchars($a['bild_pfad'] ?? '');
                        ?>
                        <tr>
                            <td>
                                <?php if ($imgSrc): ?>
                                    <img src="/<?= $imgSrc ?>" alt="" class="art-thumb">
                                <?php else: ?>
                                    <div class="art-thumb-placeholder"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </td>
                            <td><code style="font-size:.75rem;color:var(--db-ink-2);"><?= htmlspecialchars($a['Artikelnummer']) ?></code></td>
                            <td>
                                <div style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($a['Bezeichnung'] ?? '—') ?></div>
                                <?php if (!empty($a['Beschreibung'])): ?>
                                    <div style="font-size:.72rem;color:var(--db-ink-3);margin-top:.15rem;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        <?= htmlspecialchars($a['Beschreibung']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="hide-sm">
                                <span class="db-badge db-badge--gray"><?= htmlspecialchars($a['kategorie_name'] ?? '—') ?></span>
                            </td>
                            <td style="font-weight:700;white-space:nowrap;">
                                <?= number_format((float)($a['Preis'] ?? 0), 2, ',', '.') ?>
                                <span style="font-size:.7rem;color:var(--db-ink-3);font-weight:400;"><?= htmlspecialchars($a['Waehrung'] ?? 'EUR') ?></span>
                            </td>
                            <td class="hide-sm">
                                <?php
                                $stk = (int)($a['Stueckzahl'] ?? 0);
                                if ($stk === 0):
                                ?><span class="db-badge db-badge--red">Ausverkauft</span>
                                <?php elseif ($stk >= 999999): ?>
                                    <span style="color:var(--db-ink-3);font-size:.8rem;">∞</span>
                                <?php else: ?>
                                    <span class="db-badge db-badge--<?= $stk < 5 ? 'amber' : 'green' ?>"><?= $stk ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                <!-- Edit -->
                                <button class="btn-icon btn-icon--edit"
                                    title="Bearbeiten"
                                    onclick='openEditModal(<?= json_encode($a, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <!-- Delete -->
                                <button class="btn-icon btn-icon--delete"
                                    title="Löschen"
                                    onclick='openDeleteModal(<?= json_encode($a['Artikelnummer']) ?>, <?= json_encode($a['Bezeichnung'] ?? '') ?>)'>
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ══════════════════════════════════════════════════════
     MODAL: Artikel hinzufügen
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="artikel_add">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2" style="color:var(--db-green);"></i>Neuer Artikel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Artikelnummer *</label>
                            <input type="text" name="artikelnummer" class="form-control" placeholder="0000000011" maxlength="10" required>
                            <div class="form-text">Eindeutige 10-stellige Nummer</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Bezeichnung *</label>
                            <input type="text" name="bezeichnung" class="form-control" placeholder="z.B. BookIT USB-Stick 256GB" maxlength="45" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategorie *</label>
                            <select name="kategorie_id" class="form-select" required>
                                <option value="">— Bitte wählen —</option>
                                <?php foreach ($kategorien as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preis</label>
                            <input type="number" name="preis" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Währung</label>
                            <select name="waehrung" class="form-select">
                                <option value="EUR" selected>EUR</option>
                                <option value="USD">USD</option>
                                <option value="CHF">CHF</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stückzahl</label>
                            <input type="number" name="stueckzahl" class="form-control" min="0" value="0">
                            <div class="form-text">999999 = unbegrenzt</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Bildpfad</label>
                            <input type="text" name="bild_pfad" class="form-control" placeholder="assets/img/produktbilder/dateiname.png" maxlength="255">
                            <div class="form-text">Relativer Pfad ab public/ – Bild muss bereits hochgeladen sein</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Beschreibung</label>
                            <textarea name="beschreibung" class="form-control" rows="2" maxlength="150" placeholder="Kurzbeschreibung (max. 150 Zeichen)"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-success" style="background:var(--db-green);border-color:var(--db-green);">
                        <i class="bi bi-plus-lg me-1"></i> Artikel erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Artikel bearbeiten
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="formEdit">
                <input type="hidden" name="action" value="artikel_edit">
                <input type="hidden" name="orig_artikelnummer" id="edit_orig">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2" style="color:var(--db-blue);"></i>Artikel bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Artikelnummer</label>
                            <input type="text" id="edit_anr_display" class="form-control" disabled style="background:#f1f5f9;color:var(--db-ink-3);">
                            <div class="form-text">Artikelnummer kann nicht geändert werden</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Bezeichnung *</label>
                            <input type="text" name="bezeichnung" id="edit_bez" class="form-control" maxlength="45" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategorie *</label>
                            <select name="kategorie_id" id="edit_kat" class="form-select" required>
                                <?php foreach ($kategorien as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Preis</label>
                            <input type="number" name="preis" id="edit_preis" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Währung</label>
                            <select name="waehrung" id="edit_waehr" class="form-select">
                                <option value="EUR">EUR</option>
                                <option value="USD">USD</option>
                                <option value="CHF">CHF</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stückzahl</label>
                            <input type="number" name="stueckzahl" id="edit_stk" class="form-control" min="0">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Bildpfad</label>
                            <input type="text" name="bild_pfad" id="edit_bild" class="form-control" maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Beschreibung</label>
                            <textarea name="beschreibung" id="edit_desc" class="form-control" rows="2" maxlength="150"></textarea>
                        </div>
                        <!-- Bildvorschau -->
                        <div class="col-12" id="edit_preview_wrap" style="display:none;">
                            <label class="form-label">Aktuelle Vorschau</label><br>
                            <img id="edit_preview_img" src="" alt="Vorschau" style="height:80px;object-fit:contain;border:1px solid var(--db-border);border-radius:8px;background:#f8fafc;padding:4px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary" style="background:var(--db-blue);border-color:var(--db-blue);">
                        <i class="bi bi-check-lg me-1"></i> Änderungen speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Artikel löschen
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="artikel_delete">
                <input type="hidden" name="artikelnummer" id="del_anr">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color:var(--db-red);"><i class="bi bi-trash3-fill me-2"></i>Artikel löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:2rem;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:2.5rem;color:#f59e0b;display:block;margin-bottom:1rem;"></i>
                    <p style="font-size:.95rem;">Möchten Sie den Artikel <strong id="del_name"></strong> wirklich löschen?</p>
                    <p style="font-size:.8rem;color:var(--db-ink-3);">Diese Aktion kann nicht rückgängig gemacht werden. Artikel in bestehenden Bestellungen können nicht gelöscht werden.</p>
                </div>
                <div class="modal-footer justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-danger" style="background:var(--db-red);border-color:var(--db-red);">
                        <i class="bi bi-trash3-fill me-1"></i> Endgültig löschen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Kategorien verwalten
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalKat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-tags-fill me-2" style="color:var(--db-amber);"></i>Kategorien verwalten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <!-- Neue Kategorie -->
                <form method="post" style="padding:1.2rem;border-bottom:1px solid var(--db-border);">
                    <input type="hidden" name="action" value="kat_add">
                    <label class="form-label">Neue Kategorie</label>
                    <div class="input-group">
                        <input type="text" name="kat_name" class="form-control" placeholder="z.B. Zubehör" maxlength="45" required>
                        <button type="submit" class="btn btn-success" style="background:var(--db-green);border-color:var(--db-green);">
                            <i class="bi bi-plus-lg"></i> Hinzufügen
                        </button>
                    </div>
                </form>
                <!-- Kategorie-Liste -->
                <div>
                    <?php if (empty($kategorien)): ?>
                        <p style="padding:1.5rem;text-align:center;color:var(--db-ink-3);font-size:.875rem;">Keine Kategorien vorhanden.</p>
                    <?php else: ?>
                        <?php foreach ($kategorien as $k): ?>
                            <div class="kat-list-item">
                                <div style="display:flex;align-items:center;gap:.6rem;">
                                    <span class="db-badge db-badge--amber" style="font-size:.7rem;">#<?= $k['id'] ?></span>
                                    <span style="font-weight:600;font-size:.875rem;"><?= htmlspecialchars($k['name']) ?></span>
                                </div>
                                <form method="post" onsubmit="return confirm('Kategorie „<?= htmlspecialchars($k['name'], ENT_QUOTES) ?>" wirklich löschen?');">
                                    <input type="hidden" name="action" value="kat_delete">
                                    <input type="hidden" name="kat_id" value="<?= $k['id'] ?>">
                                    <button type="submit" class="btn-icon btn-icon--delete" title="Löschen"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>

<footer style="background:#1e293b;color:rgba(255,255,255,.4);padding:2rem 0;text-align:center;margin-top:3rem;">
    <div class="container">
        <p style="font-size:.8rem;margin:0;">&copy; 2026 BookIT Admin &nbsp;·&nbsp;
            <a href="dashboard.php" style="color:rgba(255,255,255,.4);">Dashboard</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openEditModal(a) {
        document.getElementById('edit_orig').value        = a.Artikelnummer;
        document.getElementById('edit_anr_display').value = a.Artikelnummer;
        document.getElementById('edit_bez').value         = a.Bezeichnung   ?? '';
        document.getElementById('edit_preis').value       = a.Preis         ?? '';
        document.getElementById('edit_stk').value         = a.Stueckzahl    ?? 0;
        document.getElementById('edit_desc').value        = a.Beschreibung  ?? '';
        document.getElementById('edit_bild').value        = a.bild_pfad     ?? '';

        // Kategorie auswählen
        const katSel = document.getElementById('edit_kat');
        for (let opt of katSel.options) {
            opt.selected = (opt.value == a.kategorie_id);
        }

        // Währung auswählen
        const waehrSel = document.getElementById('edit_waehr');
        for (let opt of waehrSel.options) {
            opt.selected = (opt.value === (a.Waehrung || 'EUR'));
        }

        // Bildvorschau
        const wrap = document.getElementById('edit_preview_wrap');
        const img  = document.getElementById('edit_preview_img');
        if (a.bild_pfad) {
            img.src = '/' + a.bild_pfad;
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('modalEdit')).show();
    }

    function openDeleteModal(artikelnummer, bezeichnung) {
        document.getElementById('del_anr').value  = artikelnummer;
        document.getElementById('del_name').textContent = bezeichnung;
        new bootstrap.Modal(document.getElementById('modalDelete')).show();
    }

    // Bildpfad im Edit-Modal → Vorschau live aktualisieren
    document.getElementById('edit_bild')?.addEventListener('input', function() {
        const wrap = document.getElementById('edit_preview_wrap');
        const img  = document.getElementById('edit_preview_img');
        if (this.value) {
            img.src = '/' + this.value;
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
        }
    });

    <?php if ($editArtikel): ?>
    // Direkt per URL-Parameter geöffnet
    document.addEventListener('DOMContentLoaded', () => {
        openEditModal(<?= json_encode($editArtikel, JSON_HEX_APOS|JSON_HEX_QUOT) ?>);
    });
    <?php endif; ?>
</script>

</body>
</html>