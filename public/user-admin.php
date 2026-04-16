<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

$sessionUserId = (int) $_SESSION['user_id'];

/* ── Admin-Check ─────────────────────────────────────────── */
try {
    $pdo = db();
    $rc  = $pdo->prepare("
        SELECT CASE WHEN EXISTS (
            SELECT 1 FROM users_has_Rollen uhr
            JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
            WHERE uhr.users_idusers = ? AND r.Rollenname = 'admin'
        ) THEN 'admin' ELSE 'other' END AS role
    ");
    $rc->execute([$sessionUserId]);
    if ($rc->fetchColumn() !== 'admin') {
        header('Location: /index.php?error=kein_zugriff'); exit;
    }
} catch (Throwable) {
    header('Location: /index.php?error=kein_zugriff'); exit;
}

$pdo      = db();
$feedback = null;
$action   = $_POST['action'] ?? '';

/* ══════════════════════════════════════════════════════════
   AKTIONEN
   ══════════════════════════════════════════════════════════ */

/* ── Neuen User anlegen ─────────────────────────────────── */
if ($action === 'user_add') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $roleName = $_POST['role']          ?? 'Kunde';
    $pw       = $_POST['password']      ?? '';

    if (!$username || !$email || !$pw) {
        $feedback = ['type' => 'danger', 'msg' => 'Benutzername, E-Mail und Passwort sind Pflichtfelder.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = ['type' => 'danger', 'msg' => 'Ungültige E-Mail-Adresse.'];
    } elseif (strlen($pw) < 8) {
        $feedback = ['type' => 'danger', 'msg' => 'Passwort muss mindestens 8 Zeichen haben.'];
    } else {
        try {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare("INSERT INTO users (email, password_hash, username) VALUES (?,?,?)");
            $ins->execute([$email, $hash, $username]);
            $newId = (int) $pdo->lastInsertId();

            // Rolle zuweisen
            $rolRow = $pdo->prepare("SELECT idRollen FROM Rollen WHERE Rollenname = ?");
            $rolRow->execute([$roleName]);
            $rolId = $rolRow->fetchColumn();
            if ($rolId) {
                $pdo->prepare("INSERT INTO users_has_Rollen (users_idusers, Rollen_idRollen) VALUES (?,?)")
                    ->execute([$newId, $rolId]);
            }
            $feedback = ['type' => 'success', 'msg' => "Benutzer {$username} wurde angelegt."];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => str_contains($e->getMessage(), 'Duplicate')
                ? 'E-Mail-Adresse ist bereits vergeben.'
                : 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── Rolle ändern ───────────────────────────────────────── */
if ($action === 'role_change') {
    $targetId = (int) ($_POST['user_id'] ?? 0);
    $roleName = $_POST['role'] ?? '';

    if ($targetId === $sessionUserId) {
        $feedback = ['type' => 'danger', 'msg' => 'Sie können Ihre eigene Rolle nicht ändern.'];
    } elseif ($targetId && $roleName) {
        try {
            $rolRow = $pdo->prepare("SELECT idRollen FROM Rollen WHERE Rollenname = ?");
            $rolRow->execute([$roleName]);
            $rolId = $rolRow->fetchColumn();

            $pdo->prepare("DELETE FROM users_has_Rollen WHERE users_idusers = ?")->execute([$targetId]);
            if ($rolId && $roleName !== 'Keine') {
                $pdo->prepare("INSERT INTO users_has_Rollen (users_idusers, Rollen_idRollen) VALUES (?,?)")
                    ->execute([$targetId, $rolId]);
            }
            $feedback = ['type' => 'success', 'msg' => 'Rolle wurde erfolgreich geändert.'];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── Passwort zurücksetzen ──────────────────────────────── */
if ($action === 'pw_reset') {
    $targetId = (int) ($_POST['user_id'] ?? 0);
    $newPw    = $_POST['new_password'] ?? '';

    if (!$targetId || strlen($newPw) < 8) {
        $feedback = ['type' => 'danger', 'msg' => 'Passwort muss mindestens 8 Zeichen haben.'];
    } else {
        try {
            $hash = password_hash($newPw, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE idusers = ?")->execute([$hash, $targetId]);
            $feedback = ['type' => 'success', 'msg' => 'Passwort wurde zurückgesetzt.'];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ── User löschen ───────────────────────────────────────── */
if ($action === 'user_delete') {
    $targetId = (int) ($_POST['user_id'] ?? 0);

    if ($targetId === $sessionUserId) {
        $feedback = ['type' => 'danger', 'msg' => 'Sie können sich nicht selbst löschen.'];
    } elseif ($targetId) {
        try {
            $pdo->prepare("DELETE FROM users WHERE idusers = ?")->execute([$targetId]);
            $feedback = ['type' => 'success', 'msg' => 'Benutzer wurde gelöscht.'];
        } catch (Throwable $e) {
            $feedback = ['type' => 'danger', 'msg' => 'Fehler: ' . $e->getMessage()];
        }
    }
}

/* ══════════════════════════════════════════════════════════
   DATEN LADEN
   ══════════════════════════════════════════════════════════ */
$search  = trim($_GET['q'] ?? '');
$roleFilter = $_GET['rolle'] ?? '';

try {
    $where  = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[]  = '(u.username LIKE ? OR u.email LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    if ($roleFilter !== '') {
        if ($roleFilter === 'Keine') {
            $where[] = 'r.Rollenname IS NULL';
        } else {
            $where[]  = 'r.Rollenname = ?';
            $params[] = $roleFilter;
        }
    }

    $whereSQL = implode(' AND ', $where);

    $users = $pdo->prepare("
        SELECT u.idusers, u.username, u.email,
               COALESCE(r.Rollenname, 'Keine') AS role,
               COALESCE(r.idRollen, 0)          AS role_id
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers = u.idusers
        LEFT JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
        WHERE {$whereSQL}
        ORDER BY u.idusers DESC
    ");
    $users->execute($params);
    $users = $users->fetchAll();

    $rollen = $pdo->query("SELECT * FROM Rollen ORDER BY idRollen")->fetchAll();

    $stats = $pdo->query("
        SELECT
            COUNT(DISTINCT u.idusers)                                       AS gesamt,
            SUM(r.Rollenname = 'admin')                                     AS admins,
            SUM(r.Rollenname = 'IT MA' OR r.Rollenname = 'employee')        AS mitarbeiter,
            SUM(r.Rollenname = 'Kunde')                                     AS kunden
        FROM users u
        LEFT JOIN users_has_Rollen uhr ON uhr.users_idusers = u.idusers
        LEFT JOIN Rollen r ON r.idRollen = uhr.Rollen_idRollen
    ")->fetch();

} catch (Throwable $e) {
    $users  = [];
    $rollen = [];
    $stats  = ['gesamt' => 0, 'admins' => 0, 'mitarbeiter' => 0, 'kunden' => 0];
    $dbErr  = $e->getMessage();
}

/* Hilfsfunktionen */
function roleBadgeU(string $role): string {
    return match (strtolower($role)) {
        'admin'  => '<span class="ub-badge ub-badge--red"><i class="bi bi-shield-fill-check"></i>Admin</span>',
        'it ma','employee' => '<span class="ub-badge ub-badge--blue"><i class="bi bi-person-badge-fill"></i>Mitarbeiter</span>',
        'kunde'  => '<span class="ub-badge ub-badge--gray"><i class="bi bi-person-fill"></i>Kunde</span>',
        'webshop'=> '<span class="ub-badge ub-badge--green"><i class="bi bi-shop"></i>Webshop</span>',
        default  => '<span class="ub-badge ub-badge--amber"><i class="bi bi-question-circle"></i>'.htmlspecialchars($role).'</span>',
    };
}

$displayName = ($_SESSION['user_name'] ?? '') ?: ($_SESSION['user_email'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Userverwaltung – BookIT Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <style>
        :root {
            --g:   #118075; --gp:  #e6f4f2;
            --b:   #4D8496; --bp:  #e8f2f6;
            --r:   #80111B; --rp:  #fee2e2;
            --am:  #d97706; --amp: #fef3c7;
            --ink: #1e293b; --i2:  #475569; --i3: #94a3b8;
            --bg:  #f8fafc; --wh:  #fff; --br: #e2e8f0;
            --rad: 14px;
            --sh:  0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04);
            --shm: 0 4px 20px rgba(15,23,42,.09);
        }
        body { background:var(--bg); font-family:system-ui,-apple-system,'Segoe UI',sans-serif; color:var(--ink); }

        /* Header */
        .ub-hero { background:linear-gradient(135deg,var(--r),var(--b)); padding:44px 0 52px; position:relative; overflow:hidden; }
        .ub-hero::before { content:''; position:absolute; inset:0; background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px); background-size:48px 48px; pointer-events:none; }
        .ub-hero .container { position:relative; z-index:1; }
        .ub-hero__label { font-size:.7rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,.55); margin-bottom:.4rem; }
        .ub-hero h1 { font-size:clamp(1.7rem,3.5vw,2.4rem); font-weight:800; color:#fff; letter-spacing:-.03em; margin:0 0 .5rem; }
        .ub-hero p { color:rgba(255,255,255,.72); font-size:.92rem; margin:0; }
        .ub-hero__meta { display:flex; gap:.6rem; flex-wrap:wrap; margin-top:1.25rem; }
        .ub-hero__chip { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2); color:#fff; font-size:.75rem; font-weight:600; padding:.35rem .85rem; border-radius:99px; }
        .ub-hero__btn { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.9); color:var(--r); font-size:.8rem; font-weight:700; padding:.42rem 1.1rem; border-radius:8px; text-decoration:none; transition:background .2s; }
        .ub-hero__btn:hover { background:#fff; color:var(--r); }
        .ub-hero__btn--ghost { background:rgba(255,255,255,.15); color:#fff; }
        .ub-hero__btn--ghost:hover { background:rgba(255,255,255,.25); color:#fff; }

        /* Stats */
        .ub-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.75rem; }
        .ub-stat { background:var(--wh); border:1px solid var(--br); border-radius:var(--rad); padding:1.1rem 1.3rem; display:flex; align-items:center; gap:.85rem; box-shadow:var(--sh); }
        .ub-stat__icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; flex-shrink:0; }
        .ub-stat__icon--blue  { background:var(--bp); color:var(--b); }
        .ub-stat__icon--red   { background:var(--rp); color:var(--r); }
        .ub-stat__icon--green { background:var(--gp); color:var(--g); }
        .ub-stat__icon--gray  { background:#f1f5f9; color:#64748b; }
        .ub-stat__num   { font-size:1.55rem; font-weight:800; color:var(--ink); letter-spacing:-.03em; line-height:1; }
        .ub-stat__label { font-size:.72rem; color:var(--i3); font-weight:600; text-transform:uppercase; letter-spacing:.06em; margin-top:.15rem; }

        /* Toolbar */
        .ub-toolbar { background:var(--wh); border:1px solid var(--br); border-radius:var(--rad); box-shadow:var(--sh); padding:1rem 1.25rem; display:flex; gap:.75rem; align-items:center; flex-wrap:wrap; margin-bottom:1.25rem; }
        .ub-search { position:relative; flex:1; min-width:200px; }
        .ub-search i { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--i3); font-size:.9rem; pointer-events:none; }
        .ub-search input { width:100%; padding:.5rem .85rem .5rem 2.4rem; border:1px solid var(--br); border-radius:8px; font-size:.875rem; color:var(--ink); background:#f8fafc; transition:border-color .2s,box-shadow .2s; }
        .ub-search input:focus { outline:none; border-color:var(--g); box-shadow:0 0 0 3px rgba(17,128,117,.12); background:#fff; }
        .ub-filter select { padding:.5rem .85rem; border:1px solid var(--br); border-radius:8px; font-size:.875rem; color:var(--ink); background:#f8fafc; cursor:pointer; }
        .ub-filter select:focus { outline:none; border-color:var(--g); }
        .ub-toolbar__count { font-size:.78rem; color:var(--i3); font-weight:600; white-space:nowrap; }

        /* Table card */
        .ub-card { background:var(--wh); border:1px solid var(--br); border-radius:var(--rad); box-shadow:var(--sh); overflow:hidden; }
        .ub-card__head { display:flex; align-items:center; justify-content:space-between; padding:1rem 1.4rem; border-bottom:1px solid var(--br); }
        .ub-card__title { font-size:.95rem; font-weight:700; margin:0; display:flex; align-items:center; gap:.5rem; }

        table.ub-table { width:100%; border-collapse:collapse; font-size:.875rem; }
        .ub-table th { padding:.65rem 1rem; background:#f8fafc; border-bottom:2px solid var(--br); font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; color:var(--i3); font-weight:700; white-space:nowrap; }
        .ub-table td { padding:.8rem 1rem; border-bottom:1px solid var(--br); vertical-align:middle; }
        .ub-table tr:last-child td { border-bottom:none; }
        .ub-table tr:hover td { background:#fafcff; }

        .ub-avatar { width:34px; height:34px; border-radius:9px; background:var(--bp); color:var(--b); display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:700; flex-shrink:0; }
        .ub-avatar--admin { background:var(--rp); color:var(--r); }
        .ub-avatar--green { background:var(--gp); color:var(--g); }

        /* Badges */
        .ub-badge { display:inline-flex; align-items:center; gap:.3rem; font-size:.68rem; font-weight:700; padding:.25rem .65rem; border-radius:6px; white-space:nowrap; }
        .ub-badge--red   { background:var(--rp); color:var(--r); }
        .ub-badge--blue  { background:var(--bp); color:var(--b); }
        .ub-badge--green { background:var(--gp); color:var(--g); }
        .ub-badge--gray  { background:#f1f5f9; color:#64748b; }
        .ub-badge--amber { background:var(--amp); color:#92400e; }

        /* Action btns */
        .ub-action { border:none; background:transparent; padding:.32rem .42rem; border-radius:7px; font-size:.95rem; cursor:pointer; transition:background .15s,color .15s; line-height:1; }
        .ub-action--edit   { color:var(--b); } .ub-action--edit:hover   { background:var(--bp); }
        .ub-action--pw     { color:var(--am); } .ub-action--pw:hover     { background:var(--amp); }
        .ub-action--delete { color:var(--r); } .ub-action--delete:hover { background:var(--rp); }
        .ub-action:disabled { opacity:.35; cursor:not-allowed; }

        /* You-chip */
        .ub-you { display:inline-flex; align-items:center; gap:.3rem; font-size:.65rem; font-weight:700; background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; padding:.15rem .5rem; border-radius:5px; margin-left:.4rem; }

        /* Modal */
        .modal-header { border-bottom:1px solid var(--br); }
        .modal-footer { border-top:1px solid var(--br); }
        .form-label { font-size:.8rem; font-weight:700; color:var(--i2); margin-bottom:.3rem; }
        .form-control, .form-select { font-size:.875rem; border-color:var(--br); border-radius:8px; }
        .form-control:focus, .form-select:focus { border-color:var(--g); box-shadow:0 0 0 3px rgba(17,128,117,.12); }
        .pw-toggle { position:relative; }
        .pw-toggle input { padding-right:2.8rem; }
        .pw-toggle__btn { position:absolute; right:.7rem; top:50%; transform:translateY(-50%); border:none; background:none; color:var(--i3); cursor:pointer; font-size:.95rem; padding:0; }

        /* Empty */
        .ub-empty { padding:3rem; text-align:center; color:var(--i3); }
        .ub-empty i { font-size:2.5rem; display:block; margin-bottom:.75rem; }
        .ub-empty p { font-size:.875rem; margin:0; }

        @media(max-width:768px) {
            .ub-stats { grid-template-columns:1fr 1fr; }
            .ub-table .hide-sm { display:none; }
        }
        @media(max-width:480px) { .ub-stats { grid-template-columns:1fr; } }
    </style>
</head>
<body>

<?php require $appRoot . '/views/partials/navbar.php'; ?>

<!-- Hero -->
<header class="ub-hero">
    <div class="container">
        <div class="ub-hero__label">Administration</div>
        <h1><i class="bi bi-people-fill me-2"></i>Userverwaltung</h1>
        <p>Benutzer anlegen, Rollen vergeben, Passwörter zurücksetzen und Accounts verwalten.</p>
        <div class="ub-hero__meta">
            <a href="dashboard.php" class="ub-hero__btn ub-hero__btn--ghost"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <button class="ub-hero__btn" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-person-plus-fill"></i> Neuer Benutzer
            </button>
        </div>
    </div>
</header>

<main class="container py-4">

    <?php if ($feedback): ?>
        <div class="alert alert-<?= $feedback['type'] ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;" role="alert">
            <i class="bi bi-<?= $feedback['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
            <?= htmlspecialchars($feedback['msg']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($dbErr)): ?>
        <div class="alert alert-warning d-flex gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>DB-Fehler:</strong> <?= htmlspecialchars($dbErr) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="ub-stats">
        <div class="ub-stat">
            <div class="ub-stat__icon ub-stat__icon--blue"><i class="bi bi-people-fill"></i></div>
            <div><div class="ub-stat__num"><?= (int)($stats['gesamt'] ?? 0) ?></div><div class="ub-stat__label">Gesamt</div></div>
        </div>
        <div class="ub-stat">
            <div class="ub-stat__icon ub-stat__icon--red"><i class="bi bi-shield-fill-check"></i></div>
            <div><div class="ub-stat__num"><?= (int)($stats['admins'] ?? 0) ?></div><div class="ub-stat__label">Admins</div></div>
        </div>
        <div class="ub-stat">
            <div class="ub-stat__icon ub-stat__icon--blue"><i class="bi bi-person-badge-fill"></i></div>
            <div><div class="ub-stat__num"><?= (int)($stats['mitarbeiter'] ?? 0) ?></div><div class="ub-stat__label">Mitarbeiter</div></div>
        </div>
        <div class="ub-stat">
            <div class="ub-stat__icon ub-stat__icon--gray"><i class="bi bi-person-fill"></i></div>
            <div><div class="ub-stat__num"><?= (int)($stats['kunden'] ?? 0) ?></div><div class="ub-stat__label">Kunden</div></div>
        </div>
    </div>

    <!-- Toolbar -->
    <form method="get" class="ub-toolbar">
        <div class="ub-search">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Name oder E-Mail suchen…" autocomplete="off">
        </div>
        <div class="ub-filter">
            <select name="rolle" onchange="this.form.submit()">
                <option value="">Alle Rollen</option>
                <?php foreach ($rollen as $r): ?>
                    <option value="<?= htmlspecialchars($r['Rollenname']) ?>"
                            <?= $roleFilter === $r['Rollenname'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['Rollenname']) ?>
                    </option>
                <?php endforeach; ?>
                <option value="Keine" <?= $roleFilter === 'Keine' ? 'selected' : '' ?>>Keine Rolle</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm" style="background:var(--g);color:#fff;border-radius:8px;font-weight:600;padding:.5rem 1rem;">
            <i class="bi bi-search me-1"></i>Suchen
        </button>
        <?php if ($search || $roleFilter): ?>
            <a href="user-admin.php" class="btn btn-sm btn-light" style="border-radius:8px;">
                <i class="bi bi-x"></i> Zurücksetzen
            </a>
        <?php endif; ?>
        <span class="ub-toolbar__count"><?= count($users) ?> Treffer</span>
    </form>

    <!-- User-Tabelle -->
    <div class="ub-card">
        <div class="ub-card__head">
            <h2 class="ub-card__title">
                <i class="bi bi-people-fill" style="color:var(--b);"></i> Alle Benutzer
            </h2>
            <button class="btn btn-sm" style="background:var(--g);color:#fff;border-radius:8px;font-size:.8rem;font-weight:600;"
                    data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="bi bi-person-plus-fill me-1"></i>Neuer Benutzer
            </button>
        </div>

        <?php if (empty($users)): ?>
            <div class="ub-empty">
                <i class="bi bi-person-x"></i>
                <p>Keine Benutzer gefunden<?= $search ? ' für „'.htmlspecialchars($search).'„' : '' ?>.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="ub-table">
                    <thead>
                        <tr>
                            <th>Benutzer</th>
                            <th class="hide-sm">E-Mail</th>
                            <th>Rolle</th>
                            <th style="text-align:right;">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
                            $isMe   = ((int)$u['idusers'] === $sessionUserId);
                            $roleLC = strtolower($u['role'] ?? '');
                            $avatarCls = match($roleLC) {
                                'admin'        => 'ub-avatar--admin',
                                'it ma','employee' => '',
                                default        => 'ub-avatar--green',
                            };
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:.7rem;">
                                    <div class="ub-avatar <?= $avatarCls ?>">
                                        <?= strtoupper(mb_substr($u['username'] ?: $u['email'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:.875rem;">
                                            <?= htmlspecialchars($u['username'] ?: '—') ?>
                                            <?php if ($isMe): ?><span class="ub-you"><i class="bi bi-person-check"></i>Sie</span><?php endif; ?>
                                        </div>
                                        <div style="font-size:.72rem;color:var(--i3);">ID #<?= $u['idusers'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-sm" style="color:var(--i2);font-size:.83rem;">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td><?= roleBadgeU($u['role'] ?? 'Keine') ?></td>
                            <td style="text-align:right;white-space:nowrap;">
                                <!-- Rolle ändern -->
                                <button class="ub-action ub-action--edit" title="Rolle ändern"
                                    <?= $isMe ? 'disabled' : '' ?>
                                    onclick='openRoleModal(<?= $u["idusers"] ?>, <?= json_encode($u["username"] ?: $u["email"]) ?>, <?= json_encode($u["role"]) ?>)'>
                                    <i class="bi bi-person-gear"></i>
                                </button>
                                <!-- Passwort zurücksetzen -->
                                <button class="ub-action ub-action--pw" title="Passwort zurücksetzen"
                                    onclick='openPwModal(<?= $u["idusers"] ?>, <?= json_encode($u["username"] ?: $u["email"]) ?>)'>
                                    <i class="bi bi-key-fill"></i>
                                </button>
                                <!-- Löschen -->
                                <button class="ub-action ub-action--delete" title="Löschen"
                                    <?= $isMe ? 'disabled' : '' ?>
                                    onclick='openDelModal(<?= $u["idusers"] ?>, <?= json_encode($u["username"] ?: $u["email"]) ?>)'>
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

<!-- ════════════════ MODAL: Neuer Benutzer ════════════════ -->
<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="user_add">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus-fill me-2" style="color:var(--g);"></i>Neuer Benutzer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Benutzername *</label>
                            <input type="text" name="username" class="form-control" placeholder="Max Mustermann" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">E-Mail *</label>
                            <input type="email" name="email" class="form-control" placeholder="max@firma.at" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Rolle</label>
                            <select name="role" class="form-select">
                                <?php foreach ($rollen as $r): ?>
                                    <option value="<?= htmlspecialchars($r['Rollenname']) ?>">
                                        <?= htmlspecialchars($r['Rollenname']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Passwort * <small style="color:var(--i3);font-weight:400;">(min. 8 Zeichen)</small></label>
                            <div class="pw-toggle">
                                <input type="password" name="password" id="add_pw" class="form-control" placeholder="••••••••" required minlength="8">
                                <button type="button" class="pw-toggle__btn" onclick="togglePw('add_pw',this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn" style="background:var(--g);color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-person-plus-fill me-1"></i>Benutzer anlegen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════ MODAL: Rolle ändern ════════════════ -->
<div class="modal fade" id="modalRole" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="role_change">
                <input type="hidden" name="user_id" id="role_uid">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-gear me-2" style="color:var(--b);"></i>Rolle ändern
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:.875rem;color:var(--i2);margin-bottom:1rem;">
                        Benutzer: <strong id="role_name"></strong>
                    </p>
                    <label class="form-label">Neue Rolle</label>
                    <select name="role" id="role_select" class="form-select">
                        <?php foreach ($rollen as $r): ?>
                            <option value="<?= htmlspecialchars($r['Rollenname']) ?>">
                                <?= htmlspecialchars($r['Rollenname']) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Keine">— Keine Rolle —</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn" style="background:var(--b);color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-check-lg me-1"></i>Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════ MODAL: Passwort zurücksetzen ════════════════ -->
<div class="modal fade" id="modalPw" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="pw_reset">
                <input type="hidden" name="user_id" id="pw_uid">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-key-fill me-2" style="color:var(--am);"></i>Passwort zurücksetzen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:.875rem;color:var(--i2);margin-bottom:1rem;">
                        Benutzer: <strong id="pw_name"></strong>
                    </p>
                    <label class="form-label">Neues Passwort * <small style="color:var(--i3);font-weight:400;">(min. 8 Zeichen)</small></label>
                    <div class="pw-toggle">
                        <input type="password" name="new_password" id="pw_new" class="form-control" placeholder="••••••••" required minlength="8">
                        <button type="button" class="pw-toggle__btn" onclick="togglePw('pw_new',this)"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn" style="background:var(--am);color:#fff;border-radius:8px;font-weight:600;">
                        <i class="bi bi-key-fill me-1"></i>Passwort setzen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════ MODAL: Löschen ════════════════ -->
<div class="modal fade" id="modalDel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="action" value="user_delete">
                <input type="hidden" name="user_id" id="del_uid">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="color:var(--r);">
                        <i class="bi bi-trash3-fill me-2"></i>Benutzer löschen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center" style="padding:2rem;">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:2.5rem;color:#f59e0b;display:block;margin-bottom:1rem;"></i>
                    <p style="font-size:.95rem;">Benutzer <strong id="del_name"></strong> wirklich löschen?</p>
                    <p style="font-size:.8rem;color:var(--i3);">Alle Rollenzuweisungen werden ebenfalls entfernt. Diese Aktion ist unwiderruflich.</p>
                </div>
                <div class="modal-footer justify-content-center gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-danger" style="background:var(--r);border-color:var(--r);border-radius:8px;font-weight:600;">
                        <i class="bi bi-trash3-fill me-1"></i>Endgültig löschen
                    </button>
                </div>
            </form>
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
function openRoleModal(id, name, currentRole) {
    document.getElementById('role_uid').value  = id;
    document.getElementById('role_name').textContent = name;
    const sel = document.getElementById('role_select');
    for (let o of sel.options) o.selected = (o.value === currentRole);
    new bootstrap.Modal(document.getElementById('modalRole')).show();
}
function openPwModal(id, name) {
    document.getElementById('pw_uid').value  = id;
    document.getElementById('pw_name').textContent = name;
    document.getElementById('pw_new').value  = '';
    new bootstrap.Modal(document.getElementById('modalPw')).show();
}
function openDelModal(id, name) {
    document.getElementById('del_uid').value  = id;
    document.getElementById('del_name').textContent = name;
    new bootstrap.Modal(document.getElementById('modalDel')).show();
}
function togglePw(inputId, btn) {
    const inp = document.getElementById(inputId);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

// Live-Suche bei Enter
document.querySelector('.ub-search input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') e.target.closest('form').submit();
});
</script>
</body>
</html>