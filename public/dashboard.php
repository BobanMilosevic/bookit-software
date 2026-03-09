<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);  // = bookit-software/
require $appRoot . '/app/auth/require_login.php';
require $appRoot . '/app/db.php';

$userId      = (int)$_SESSION['user_id'];
$userName    = $_SESSION['user_name']    ?? '';
$userEmail   = $_SESSION['user_email']   ?? '';
$userRole    = $_SESSION['user_role']    ?? 'customer';
$displayName = $userName !== '' ? $userName : $userEmail;

/* ── Daten aus der Datenbank ────────────────────────────── */
try {
    $pdo = db();

    $stats = $pdo->prepare("
        SELECT
            COUNT(*)                                              AS gesamt,
            SUM(status IN ('ausstehend','bestaetigt')
                AND start_zeit > NOW())                          AS anstehend,
            SUM(status = 'abgeschlossen')                        AS abgeschlossen,
            SUM(status = 'storniert')                            AS storniert
        FROM Buchungen
        WHERE user_id = ?
    ");
    $stats->execute([$userId]);
    $s = $stats->fetch();

    $buchungen = $pdo->prepare("
        SELECT
            b.id, b.start_zeit, b.end_zeit, b.status, b.notiz,
            r.name AS raum_name, r.standort AS raum_standort
        FROM Buchungen b
        JOIN Raeume r ON r.id = b.raum_id
        WHERE b.user_id = ?
        ORDER BY
            CASE WHEN b.start_zeit >= NOW() THEN 0 ELSE 1 END,
            b.start_zeit ASC
        LIMIT 10
    ");
    $buchungen->execute([$userId]);
    $buchungsliste = $buchungen->fetchAll();

    $raeume = $pdo->query("
        SELECT id, name, standort, kapazitaet
        FROM Raeume WHERE aktiv = 1 ORDER BY name
    ")->fetchAll();

} catch (Throwable $e) {
    $s             = ['gesamt'=>0,'anstehend'=>0,'abgeschlossen'=>0,'storniert'=>0];
    $buchungsliste = [];
    $raeume        = [];
    $dbError       = true;
}

function statusBadge(string $status): string {
    return match($status) {
        'bestaetigt'    => '<span class="db-badge db-badge--green">Bestätigt</span>',
        'ausstehend'    => '<span class="db-badge db-badge--yellow">Ausstehend</span>',
        'abgeschlossen' => '<span class="db-badge db-badge--gray">Abgeschlossen</span>',
        'storniert'     => '<span class="db-badge db-badge--red">Storniert</span>',
        default         => '<span class="db-badge db-badge--gray">'.htmlspecialchars($status).'</span>',
    };
}

function formatDt(string $dt): string {
    $d = new DateTimeImmutable($dt);
    $w = ['So','Mo','Di','Mi','Do','Fr','Sa'];
    return $w[(int)$d->format('w')].', '.$d->format('d.m.Y').' · '.$d->format('H:i').' Uhr';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    :root{--db-green:#118075;--db-green-pale:#e6f4f2;--db-blue:#4D8496;--db-ink:#1e293b;--db-ink-2:#475569;--db-ink-3:#94a3b8;--db-bg:#f8fafc;--db-white:#fff;--db-border:#e2e8f0;--db-radius:14px;--db-shadow:0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04);--db-shadow-md:0 4px 20px rgba(15,23,42,.08);}
    body{background:var(--db-bg);font-family:system-ui,-apple-system,'Segoe UI',sans-serif;color:var(--db-ink);}

    .db-header{background:linear-gradient(135deg,var(--db-green),var(--db-blue));padding:48px 0 56px;position:relative;overflow:hidden;}
    .db-header::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;pointer-events:none;}
    .db-header .container{position:relative;z-index:1;}
    .db-header__greeting{font-size:.78rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.65);margin-bottom:.4rem;}
    .db-header h1{font-size:clamp(1.6rem,3vw,2.2rem);font-weight:700;color:#fff;letter-spacing:-.025em;margin:0 0 .4rem;}
    .db-header p{color:rgba(255,255,255,.72);margin:0;font-size:.92rem;}
    .db-header__role{display:inline-flex;align-items:center;gap:.35rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);color:#fff;font-size:.75rem;font-weight:600;padding:.3rem .8rem;border-radius:999px;letter-spacing:.04em;margin-top:.75rem;}
    .db-new-booking-btn{display:inline-flex;align-items:center;gap:.4rem;background:#fff;color:var(--db-green);font-weight:700;font-size:.88rem;padding:.65rem 1.4rem;border-radius:9px;text-decoration:none;transition:all .2s;box-shadow:0 2px 12px rgba(0,0,0,.12);}
    .db-new-booking-btn:hover{background:var(--db-green-pale);color:var(--db-green);transform:translateY(-1px);}

    .db-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin:-28px 0 2rem;position:relative;z-index:2;}
    .db-stat{background:var(--db-white);border:1px solid var(--db-border);border-radius:var(--db-radius);padding:1.25rem 1.4rem;box-shadow:var(--db-shadow-md);display:flex;align-items:center;gap:1rem;}
    .db-stat__icon{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
    .db-stat__icon--green{background:var(--db-green-pale);color:var(--db-green);}
    .db-stat__icon--blue{background:#e8f2f6;color:var(--db-blue);}
    .db-stat__icon--teal{background:#d1fae5;color:#059669;}
    .db-stat__icon--red{background:#fef2f2;color:#dc2626;}
    .db-stat__num{font-size:1.75rem;font-weight:700;letter-spacing:-.04em;line-height:1;color:var(--db-ink);}
    .db-stat__label{font-size:.75rem;color:var(--db-ink-3);font-weight:500;margin-top:.15rem;}

    .db-card{background:var(--db-white);border:1px solid var(--db-border);border-radius:var(--db-radius);box-shadow:var(--db-shadow);overflow:hidden;}
    .db-card__head{padding:1.1rem 1.4rem;border-bottom:1px solid var(--db-border);display:flex;align-items:center;justify-content:space-between;gap:.75rem;}
    .db-card__title{font-size:.95rem;font-weight:700;color:var(--db-ink);margin:0;display:flex;align-items:center;gap:.5rem;}
    .db-card__title i{color:var(--db-green);font-size:.9rem;}

    .db-booking{padding:1.1rem 1.4rem;border-bottom:1px solid var(--db-border);display:grid;grid-template-columns:1fr auto;gap:.5rem 1rem;align-items:start;transition:background .15s;}
    .db-booking:last-child{border-bottom:none;}
    .db-booking:hover{background:#fafcff;}
    .db-booking__room{font-size:.95rem;font-weight:700;color:var(--db-ink);margin-bottom:.2rem;}
    .db-booking__meta{font-size:.8rem;color:var(--db-ink-3);display:flex;flex-wrap:wrap;gap:.5rem .9rem;}
    .db-booking__meta span{display:flex;align-items:center;gap:.25rem;}
    .db-booking__actions{display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;}

    .db-badge{display:inline-block;font-size:.7rem;font-weight:700;padding:.2rem .65rem;border-radius:999px;border:1px solid transparent;letter-spacing:.03em;white-space:nowrap;}
    .db-badge--green{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#16a34a;}
    .db-badge--yellow{background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3);color:#d97706;}
    .db-badge--gray{background:rgba(100,116,139,.1);border-color:rgba(100,116,139,.25);color:#64748b;}
    .db-badge--red{background:rgba(220,38,38,.08);border-color:rgba(220,38,38,.2);color:#dc2626;}

    .db-action{display:flex;align-items:center;gap:.9rem;padding:1rem 1.4rem;border-bottom:1px solid var(--db-border);text-decoration:none;color:var(--db-ink);transition:background .15s;}
    .db-action:last-child{border-bottom:none;}
    .db-action:hover{background:var(--db-green-pale);color:var(--db-ink);}
    .db-action__icon{width:40px;height:40px;background:var(--db-green-pale);color:var(--db-green);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;transition:background .15s,color .15s;}
    .db-action:hover .db-action__icon{background:var(--db-green);color:#fff;}
    .db-action__label{font-size:.88rem;font-weight:600;}
    .db-action__sub{font-size:.75rem;color:var(--db-ink-3);margin-top:.1rem;}
    .db-action__arrow{margin-left:auto;color:var(--db-ink-3);font-size:.85rem;}

    .db-room{display:flex;align-items:center;gap:.85rem;padding:.9rem 1.4rem;border-bottom:1px solid var(--db-border);}
    .db-room:last-child{border-bottom:none;}
    .db-room__icon{width:36px;height:36px;background:var(--db-green-pale);color:var(--db-green);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;}
    .db-room__name{font-size:.88rem;font-weight:600;color:var(--db-ink);}
    .db-room__sub{font-size:.75rem;color:var(--db-ink-3);margin-top:.1rem;}
    .db-room__cap{margin-left:auto;font-size:.75rem;font-weight:600;color:var(--db-ink-3);display:flex;align-items:center;gap:.25rem;}

    .db-empty{padding:3rem 1.5rem;text-align:center;color:var(--db-ink-3);}
    .db-empty i{font-size:2rem;display:block;margin-bottom:.6rem;}
    .db-empty p{font-size:.85rem;margin:0;}

    .db-btn-sm{font-size:.75rem;font-weight:600;padding:.28rem .75rem;border-radius:6px;border:1.5px solid var(--db-border);background:transparent;color:var(--db-ink-2);cursor:pointer;text-decoration:none;display:inline-block;transition:all .15s;white-space:nowrap;}
    .db-btn-sm:hover{border-color:var(--db-green);color:var(--db-green);}
    .db-btn-sm--danger:hover{border-color:#dc2626;color:#dc2626;}

    @media(max-width:900px){.db-stats{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:576px){.db-stats{grid-template-columns:1fr 1fr;}.db-header{padding:36px 0 56px;}.db-booking{grid-template-columns:1fr;}.db-booking__actions{flex-direction:row;}}
    </style>
</head>
<body>

<?php require __DIR__ . '/../views/partials/navbar.php'; ?>

<header class="db-header">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="db-header__greeting">Mein Dashboard</div>
                <h1>Willkommen, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>!</h1>
                <p>Verwalten Sie Ihre Raumreservierungen und Einstellungen.</p>
                <div class="db-header__role">
                    <i class="bi bi-person-badge"></i>
                    <?= $userRole === 'employee' ? 'Mitarbeiter' : 'Kunde' ?>
                </div>
            </div>
            <a href="/mock_booking.php" class="db-new-booking-btn">
                <i class="bi bi-plus-circle-fill"></i> Neue Buchung
            </a>
        </div>
    </div>
</header>

<main class="container py-4">

    <?php if (isset($dbError)): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" style="border-radius:10px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>Datenbank nicht verbunden.</strong>
            Führe <code>migration_buchungen.sql</code> aus um echte Daten zu sehen.</div>
        </div>
    <?php endif; ?>

    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--green"><i class="bi bi-calendar-check"></i></div>
            <div><div class="db-stat__num"><?= (int)($s['gesamt']??0) ?></div><div class="db-stat__label">Gesamt</div></div>
        </div>
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--blue"><i class="bi bi-clock"></i></div>
            <div><div class="db-stat__num"><?= (int)($s['anstehend']??0) ?></div><div class="db-stat__label">Anstehend</div></div>
        </div>
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--teal"><i class="bi bi-check-circle"></i></div>
            <div><div class="db-stat__num"><?= (int)($s['abgeschlossen']??0) ?></div><div class="db-stat__label">Abgeschlossen</div></div>
        </div>
        <div class="db-stat">
            <div class="db-stat__icon db-stat__icon--red"><i class="bi bi-x-circle"></i></div>
            <div><div class="db-stat__num"><?= (int)($s['storniert']??0) ?></div><div class="db-stat__label">Storniert</div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="db-card">
                <div class="db-card__head">
                    <h2 class="db-card__title"><i class="bi bi-calendar3"></i> Meine Buchungen</h2>
                    <a href="/mock_booking.php" class="db-btn-sm"><i class="bi bi-plus"></i> Neu</a>
                </div>
                <?php if (empty($buchungsliste)): ?>
                    <div class="db-empty">
                        <i class="bi bi-calendar-x"></i>
                        <p>Keine Buchungen vorhanden.<br>
                           <a href="/mock_booking.php" style="color:var(--db-green);font-weight:600;">Jetzt ersten Raum buchen →</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($buchungsliste as $b): ?>
                        <div class="db-booking">
                            <div>
                                <div class="db-booking__room"><?= htmlspecialchars($b['raum_name']) ?></div>
                                <div class="db-booking__meta">
                                    <span><i class="bi bi-calendar"></i> <?= htmlspecialchars(formatDt($b['start_zeit'])) ?></span>
                                    <?php if ($b['raum_standort']): ?>
                                        <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($b['raum_standort']) ?></span>
                                    <?php endif; ?>
                                    <?php
                                        $start = new DateTimeImmutable($b['start_zeit']);
                                        $end   = new DateTimeImmutable($b['end_zeit']);
                                        $diff  = $start->diff($end);
                                        $dur   = ($diff->h ? $diff->h.' Std.' : '').($diff->i ? ' '.$diff->i.' Min.' : '');
                                    ?>
                                    <?php if (trim($dur)): ?>
                                        <span><i class="bi bi-hourglass-split"></i> <?= trim($dur) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="db-booking__actions">
                                <?= statusBadge($b['status']) ?>
                                <?php if (in_array($b['status'],['ausstehend','bestaetigt']) && $b['start_zeit'] > date('Y-m-d H:i:s')): ?>
                                    <a href="/mock_booking.php?edit=<?= $b['id'] ?>" class="db-btn-sm">Ändern</a>
                                    <a href="#" class="db-btn-sm db-btn-sm--danger">Stornieren</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="db-card">
                <div class="db-card__head">
                    <h2 class="db-card__title"><i class="bi bi-lightning-charge"></i> Schnellzugriff</h2>
                </div>
                <a href="/mock_booking.php" class="db-action">
                    <div class="db-action__icon"><i class="bi bi-calendar-plus"></i></div>
                    <div><div class="db-action__label">Neue Buchung</div><div class="db-action__sub">Raum suchen &amp; reservieren</div></div>
                    <i class="bi bi-chevron-right db-action__arrow"></i>
                </a>
                <a href="/mock_checkin.php" class="db-action">
                    <div class="db-action__icon"><i class="bi bi-qr-code-scan"></i></div>
                    <div><div class="db-action__label">Check-in</div><div class="db-action__sub">QR-Code scannen</div></div>
                    <i class="bi bi-chevron-right db-action__arrow"></i>
                </a>
                <a href="/customer-news.php" class="db-action">
                    <div class="db-action__icon"><i class="bi bi-newspaper"></i></div>
                    <div><div class="db-action__label">News</div><div class="db-action__sub">Aktuelle Mitteilungen</div></div>
                    <i class="bi bi-chevron-right db-action__arrow"></i>
                </a>
                <?php if ($userRole === 'employee'): ?>
                <a href="/internal-news.php" class="db-action">
                    <div class="db-action__icon"><i class="bi bi-shield-lock"></i></div>
                    <div><div class="db-action__label">Interne News</div><div class="db-action__sub">Nur für Mitarbeiter</div></div>
                    <i class="bi bi-chevron-right db-action__arrow"></i>
                </a>
                <?php endif; ?>
            </div>

            <div class="db-card">
                <div class="db-card__head">
                    <h2 class="db-card__title"><i class="bi bi-door-open"></i> Verfügbare Räume</h2>
                </div>
                <?php if (empty($raeume)): ?>
                    <div class="db-empty"><i class="bi bi-building"></i><p>Noch keine Räume angelegt.</p></div>
                <?php else: ?>
                    <?php foreach ($raeume as $r): ?>
                        <div class="db-room">
                            <div class="db-room__icon"><i class="bi bi-building"></i></div>
                            <div>
                                <div class="db-room__name"><?= htmlspecialchars($r['name']) ?></div>
                                <?php if ($r['standort']): ?>
                                    <div class="db-room__sub"><?= htmlspecialchars($r['standort']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="db-room__cap"><i class="bi bi-people"></i> <?= (int)$r['kapazitaet'] ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<footer style="background:#1e293b;color:rgba(255,255,255,.45);padding:2rem 0;text-align:center;margin-top:3rem;">
    <div class="container">
        <p style="font-size:.82rem;margin:0;">&copy; 2026 BookIT &nbsp;·&nbsp;
            <a href="/about.php" style="color:rgba(255,255,255,.45);">Über uns</a> &nbsp;·&nbsp;
            <a href="/impressum.php" style="color:rgba(255,255,255,.45);">Impressum</a></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>