<?php
declare(strict_types=1);

$appRoot = dirname(__DIR__);
require $appRoot . '/app/auth/require_login.php';

$displayName = ($_SESSION['user_name'] ?? '') ?: ($_SESSION['user_email'] ?? 'Nutzer');

/* ══════════════════════════════════════════════════════════
   HARDCODED DOWNLOAD-KATALOG
   ══════════════════════════════════════════════════════════

   Struktur pro Eintrag:
     title      – Anzeigename
     version    – Versionsnummer (optional, '' wenn keins)
     desc       – Kurzbeschreibung
     size       – Dateigröße als Text
     date       – Stand / Datum
     url        – Downloadlink (relativer oder absoluter Pfad)
     ext        – Dateiendung für das Badge (ZIP, EXE, PDF, …)
     badge      – Badge-Farbe: green | blue | amber | red | gray
*/

$downloads = [

    /* ── Software / Installer ─────────────────────────── */
    'Software & Installer' => [
        'icon'  => 'bi-floppy2-fill',
        'color' => 'green',
        'items' => [
            [
                'title'   => 'BookIT Setup – Windows',
                'version' => 'v2.4.1',
                'desc'    => 'Installer für Windows 10/11 (64-Bit). Enthält BookIT Client, lokalen Agent und QR-Scanner-Treiber.',
                'size'    => '148 MB',
                'date'    => 'Apr 2026',
                'url'     => '/downloads/files/BookIT_Setup_Win_2.4.1.exe',
                'ext'     => 'EXE',
                'badge'   => 'green',
            ],
            [
                'title'   => 'BookIT Setup – macOS',
                'version' => 'v2.4.1',
                'desc'    => 'Universelles DMG-Paket für macOS 13 Ventura und neuer (Intel & Apple Silicon).',
                'size'    => '162 MB',
                'date'    => 'Apr 2026',
                'url'     => '/downloads/files/BookIT_Setup_macOS_2.4.1.dmg',
                'ext'     => 'DMG',
                'badge'   => 'green',
            ],
            [
                'title'   => 'BookIT Server Agent',
                'version' => 'v1.9.0',
                'desc'    => 'Leichtgewichtiger Hintergrund-Dienst für Self-Hosted-Installationen (Linux/Windows Server).',
                'size'    => '34 MB',
                'date'    => 'Mär 2026',
                'url'     => '/downloads/files/BookIT_ServerAgent_1.9.0.zip',
                'ext'     => 'ZIP',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'BookIT Mobile – APK (Android)',
                'version' => 'v2.1.3',
                'desc'    => 'Android-App für QR-Code-Check-in und Raumbuchungen. Für Geräte ab Android 11.',
                'size'    => '28 MB',
                'date'    => 'Feb 2026',
                'url'     => '/downloads/files/BookIT_Mobile_2.1.3.apk',
                'ext'     => 'APK',
                'badge'   => 'amber',
            ],
        ],
    ],

    /* ── Datenblätter / Produktinfos ──────────────────── */
    'Datenblätter & Produktinfos' => [
        'icon'  => 'bi-file-earmark-text-fill',
        'color' => 'blue',
        'items' => [
            [
                'title'   => 'Produktdatenblatt – BookIT Basic',
                'version' => '',
                'desc'    => 'Technische Spezifikationen, Systemvoraussetzungen und Feature-Übersicht für das Basic-Paket.',
                'size'    => '820 KB',
                'date'    => 'Jan 2026',
                'url'     => '/downloads/files/Datenblatt_BookIT_Basic.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'Produktdatenblatt – BookIT Pro',
                'version' => '',
                'desc'    => 'Erweiterte Spezifikationen für das Pro-Paket inkl. API-Dokumentation und SLA-Angaben.',
                'size'    => '1.1 MB',
                'date'    => 'Jan 2026',
                'url'     => '/downloads/files/Datenblatt_BookIT_Pro.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'Produktdatenblatt – BookIT Enterprise',
                'version' => '',
                'desc'    => 'Vollständige technische Dokumentation für Enterprise-Kunden inkl. Hochverfügbarkeit & Failover.',
                'size'    => '1.4 MB',
                'date'    => 'Feb 2026',
                'url'     => '/downloads/files/Datenblatt_BookIT_Enterprise.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'Server-Hardware Datenblatt',
                'version' => '',
                'desc'    => 'Technische Details zu den BookIT-Server-Modellen S, M und L (CPU, RAM, Laufwerke, Zertifikate).',
                'size'    => '2.3 MB',
                'date'    => 'Mär 2026',
                'url'     => '/downloads/files/Datenblatt_Server_SML.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'Preisübersicht 2026',
                'version' => '',
                'desc'    => 'Aktuelle Preisliste aller Pakete, Hardware-Bundles und Zusatzoptionen. Stand April 2026.',
                'size'    => '380 KB',
                'date'    => 'Apr 2026',
                'url'     => '/downloads/files/Preisuebersicht_2026.pdf',
                'ext'     => 'PDF',
                'badge'   => 'amber',
            ],
        ],
    ],

    /* ── Handbücher / Dokumentation ───────────────────── */
    'Handbücher & Dokumentation' => [
        'icon'  => 'bi-book-fill',
        'color' => 'amber',
        'items' => [
            [
                'title'   => 'Benutzerhandbuch',
                'version' => 'v2.4',
                'desc'    => 'Vollständiges Handbuch für Endnutzer: Buchung, Check-in, QR-Code-Verwaltung und Kontoeinstellungen.',
                'size'    => '4.2 MB',
                'date'    => 'Apr 2026',
                'url'     => '/downloads/files/BookIT_Benutzerhandbuch_v2.4.pdf',
                'ext'     => 'PDF',
                'badge'   => 'green',
            ],
            [
                'title'   => 'Administrator-Handbuch',
                'version' => 'v2.4',
                'desc'    => 'Setup, Benutzerverwaltung, Raumkonfiguration, Backup-Strategien und Systemwartung.',
                'size'    => '6.8 MB',
                'date'    => 'Apr 2026',
                'url'     => '/downloads/files/BookIT_Adminhandbuch_v2.4.pdf',
                'ext'     => 'PDF',
                'badge'   => 'green',
            ],
            [
                'title'   => 'Schnellstart-Guide',
                'version' => '',
                'desc'    => 'Kompakte Anleitung (8 Seiten) für den sofortigen Einstieg — ideal für neue Mitarbeiter.',
                'size'    => '1.1 MB',
                'date'    => 'Feb 2026',
                'url'     => '/downloads/files/BookIT_Schnellstart.pdf',
                'ext'     => 'PDF',
                'badge'   => 'green',
            ],
            [
                'title'   => 'API-Dokumentation',
                'version' => 'v1.3',
                'desc'    => 'REST-API Referenz für Enterprise-Kunden: Endpoints, Authentifizierung, Beispiel-Requests und Webhooks.',
                'size'    => '890 KB',
                'date'    => 'Mär 2026',
                'url'     => '/downloads/files/BookIT_API_Doku_v1.3.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
            [
                'title'   => 'Installationsanleitung – Self Hosted',
                'version' => 'v1.9',
                'desc'    => 'Schritt-für-Schritt-Anleitung für die eigene Serverinstallation mit XAMPP, Docker und Linux.',
                'size'    => '2.1 MB',
                'date'    => 'Mär 2026',
                'url'     => '/downloads/files/BookIT_Installation_SelfHosted_v1.9.pdf',
                'ext'     => 'PDF',
                'badge'   => 'blue',
            ],
        ],
    ],
];

/* Badge-Farb-Map */
$badgeClasses = [
    'green' => 'dl-badge--green',
    'blue'  => 'dl-badge--blue',
    'amber' => 'dl-badge--amber',
    'red'   => 'dl-badge--red',
    'gray'  => 'dl-badge--gray',
];

$sectionColors = [
    'green' => ['bg' => '#e6f4f2', 'fg' => '#118075', 'grad' => 'linear-gradient(135deg,#118075,#4D8496)'],
    'blue'  => ['bg' => '#e8f2f6', 'fg' => '#4D8496', 'grad' => 'linear-gradient(135deg,#4D8496,#2563eb)'],
    'amber' => ['bg' => '#fef3c7', 'fg' => '#92400e', 'grad' => 'linear-gradient(135deg,#d97706,#f59e0b)'],
];

$totalFiles = array_sum(array_map(fn($s) => count($s['items']), $downloads));
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads – BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/index.css">
    <style>
        :root {
            --green:      #118075;
            --green-pale: #e6f4f2;
            --blue:       #4D8496;
            --blue-pale:  #e8f2f6;
            --amber:      #d97706;
            --ink:        #1e293b;
            --ink-2:      #475569;
            --ink-3:      #94a3b8;
            --bg:         #f8fafc;
            --white:      #ffffff;
            --border:     #e2e8f0;
            --radius:     14px;
            --shadow:     0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
            --shadow-md:  0 4px 20px rgba(15,23,42,.09);
        }

        body {
            background: var(--bg);
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink);
        }

        /* ── Hero ───────────────────────────────────────── */
        .dl-hero {
            background: linear-gradient(135deg, var(--green), var(--blue));
            padding: 56px 0 64px;
            position: relative;
            overflow: hidden;
        }
        .dl-hero::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }
        .dl-hero .container { position: relative; z-index: 1; }
        .dl-hero__label {
            font-size: .7rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; color: rgba(255,255,255,.6);
            margin-bottom: .5rem;
        }
        .dl-hero h1 {
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 800; color: #fff;
            letter-spacing: -.03em; margin: 0 0 .6rem;
        }
        .dl-hero p {
            color: rgba(255,255,255,.75);
            font-size: 1rem; margin: 0; max-width: 520px;
        }
        .dl-hero__meta {
            display: flex; align-items: center; gap: 1.25rem;
            margin-top: 1.5rem; flex-wrap: wrap;
        }
        .dl-hero__chip {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff; font-size: .75rem; font-weight: 600;
            padding: .35rem .85rem; border-radius: 99px;
        }

        /* ── Section ────────────────────────────────────── */
        .dl-section { margin-bottom: 2.5rem; }

        .dl-section__head {
            display: flex; align-items: center; gap: .85rem;
            margin-bottom: 1rem;
        }
        .dl-section__icon {
            width: 42px; height: 42px; border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }
        .dl-section__title {
            font-size: 1.05rem; font-weight: 800;
            letter-spacing: -.02em; margin: 0;
        }
        .dl-section__count {
            font-size: .72rem; font-weight: 700;
            color: var(--ink-3); margin-left: auto;
            text-transform: uppercase; letter-spacing: .07em;
        }

        /* ── Download Card ──────────────────────────────── */
        .dl-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
        }

        .dl-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 1.25rem 1.35rem;
            display: flex; flex-direction: column; gap: .75rem;
            transition: box-shadow .2s, transform .2s, border-color .2s;
            position: relative;
            overflow: hidden;
        }
        .dl-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            opacity: 0;
            transition: opacity .2s;
        }
        .dl-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            border-color: #c7d9e0;
        }
        .dl-card:hover::before { opacity: 1; }

        .dl-card--green::before { background: linear-gradient(90deg, var(--green), var(--blue)); }
        .dl-card--blue::before  { background: linear-gradient(90deg, var(--blue), #2563eb); }
        .dl-card--amber::before { background: linear-gradient(90deg, var(--amber), #f59e0b); }

        .dl-card__top {
            display: flex; align-items: flex-start;
            justify-content: space-between; gap: .75rem;
        }
        .dl-card__title {
            font-size: .94rem; font-weight: 700;
            line-height: 1.35; margin: 0;
            color: var(--ink);
        }
        .dl-card__version {
            font-size: .68rem; font-weight: 700;
            color: var(--green); background: var(--green-pale);
            padding: .2rem .55rem; border-radius: 5px;
            white-space: nowrap; flex-shrink: 0; margin-top: .1rem;
        }
        .dl-card__desc {
            font-size: .825rem; color: var(--ink-2);
            line-height: 1.55; margin: 0;
            flex: 1;
        }
        .dl-card__foot {
            display: flex; align-items: center;
            justify-content: space-between; gap: .5rem;
            border-top: 1px solid var(--border);
            padding-top: .75rem; margin-top: auto;
        }
        .dl-card__meta {
            display: flex; align-items: center; gap: .9rem;
            font-size: .72rem; color: var(--ink-3); font-weight: 600;
        }
        .dl-card__meta span { display: flex; align-items: center; gap: .3rem; }

        /* ── Badges ─────────────────────────────────────── */
        .dl-badge {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .68rem; font-weight: 700; letter-spacing: .05em;
            padding: .25rem .65rem; border-radius: 6px;
            text-transform: uppercase;
        }
        .dl-badge--green { background: var(--green-pale); color: var(--green); }
        .dl-badge--blue  { background: var(--blue-pale);  color: var(--blue); }
        .dl-badge--amber { background: #fef3c7; color: #92400e; }
        .dl-badge--red   { background: #fee2e2; color: #80111B; }
        .dl-badge--gray  { background: #f1f5f9; color: #64748b; }

        /* ── Download Button ─────────────────────────────── */
        .dl-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            background: var(--green); color: #fff;
            font-size: .8rem; font-weight: 700;
            padding: .45rem 1.1rem; border-radius: 8px;
            text-decoration: none;
            transition: background .2s, transform .15s, box-shadow .2s;
            white-space: nowrap; flex-shrink: 0;
            border: none;
        }
        .dl-btn:hover {
            background: #0d6560; color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(17,128,117,.3);
        }
        .dl-btn:active { transform: none; }

        /* ── Info Banner ─────────────────────────────────── */
        .dl-notice {
            background: var(--green-pale);
            border: 1px solid rgba(17,128,117,.2);
            border-radius: 10px;
            padding: .9rem 1.2rem;
            display: flex; align-items: flex-start; gap: .75rem;
            font-size: .83rem; color: #0d5950;
            margin-bottom: 2rem;
        }
        .dl-notice i { font-size: 1rem; flex-shrink: 0; margin-top: .1rem; }

        /* ── Stats strip ─────────────────────────────────── */
        .dl-strip {
            display: flex; gap: 2rem; flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .dl-strip__item {
            display: flex; align-items: center; gap: .6rem;
        }
        .dl-strip__num {
            font-size: 1.4rem; font-weight: 800;
            color: var(--ink); letter-spacing: -.03em;
        }
        .dl-strip__label {
            font-size: .75rem; color: var(--ink-3);
            font-weight: 600; text-transform: uppercase; letter-spacing: .07em;
        }
        .dl-strip__divider {
            width: 1px; background: var(--border); align-self: stretch;
        }

        /* ── Responsive ──────────────────────────────────── */
        @media (max-width: 600px) {
            .dl-grid { grid-template-columns: 1fr; }
            .dl-hero { padding: 40px 0 48px; }
            .dl-strip { gap: 1rem; }
        }
    </style>
</head>
<body>

<?php require $appRoot . '/views/partials/navbar.php'; ?>

<!-- Hero -->
<header class="dl-hero">
    <div class="container">
        <div class="dl-hero__label">Kundenbereich</div>
        <h1><i class="bi bi-download me-2"></i>Downloads</h1>
        <p>Software, Datenblätter und Handbücher – alles an einem Ort. Nur für eingeloggte Nutzer.</p>
        <div class="dl-hero__meta">
            <div class="dl-hero__chip">
                <i class="bi bi-person-check-fill"></i>
                Eingeloggt als <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="dl-hero__chip">
                <i class="bi bi-files"></i>
                <?= $totalFiles ?> Dateien verfügbar
            </div>
            <div class="dl-hero__chip">
                <i class="bi bi-shield-lock-fill"></i>
                Gesicherter Bereich
            </div>
        </div>
    </div>
</header>

<main class="container py-5">

    <!-- Hinweis -->
    <div class="dl-notice">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Hinweis:</strong> Alle Downloads sind ausschließlich für Kunden und Mitarbeiter von BookIT bestimmt.
            Die Weitergabe an Dritte ist nicht gestattet. Bei Fragen wenden Sie sich an
            <a href="mailto:office@bookit.at" style="color:inherit;font-weight:700;">office@bookit.at</a>.
        </div>
    </div>

    <!-- Stats -->
    <div class="dl-strip">
        <?php
        $i = 0;
        foreach ($downloads as $sectionName => $section):
            if ($i > 0): ?><div class="dl-strip__divider"></div><?php endif; ?>
            <div class="dl-strip__item">
                <div>
                    <div class="dl-strip__num"><?= count($section['items']) ?></div>
                    <div class="dl-strip__label"><?= htmlspecialchars($sectionName) ?></div>
                </div>
            </div>
        <?php $i++; endforeach; ?>
    </div>

    <!-- Sektionen -->
    <?php foreach ($downloads as $sectionName => $section):
        $col   = $sectionColors[$section['color']] ?? $sectionColors['green'];
        $ckey  = $section['color'];
    ?>
    <div class="dl-section">

        <div class="dl-section__head">
            <div class="dl-section__icon"
                 style="background:<?= $col['bg'] ?>;color:<?= $col['fg'] ?>;">
                <i class="bi <?= htmlspecialchars($section['icon']) ?>"></i>
            </div>
            <h2 class="dl-section__title" style="color:<?= $col['fg'] ?>;">
                <?= htmlspecialchars($sectionName) ?>
            </h2>
            <span class="dl-section__count"><?= count($section['items']) ?> Dateien</span>
        </div>

        <div class="dl-grid">
            <?php foreach ($section['items'] as $item):
                $badgeCls = $badgeClasses[$item['badge']] ?? 'dl-badge--gray';
            ?>
            <div class="dl-card dl-card--<?= $ckey ?>">

                <div class="dl-card__top">
                    <h3 class="dl-card__title"><?= htmlspecialchars($item['title']) ?></h3>
                    <?php if ($item['version']): ?>
                        <span class="dl-card__version"><?= htmlspecialchars($item['version']) ?></span>
                    <?php endif; ?>
                </div>

                <p class="dl-card__desc"><?= htmlspecialchars($item['desc']) ?></p>

                <div class="dl-card__foot">
                    <div>
                        <div class="dl-card__meta">
                            <span><i class="bi bi-hdd"></i><?= htmlspecialchars($item['size']) ?></span>
                            <span><i class="bi bi-calendar3"></i><?= htmlspecialchars($item['date']) ?></span>
                        </div>
                        <div style="margin-top:.45rem;">
                            <span class="dl-badge <?= $badgeCls ?>">
                                <i class="bi bi-file-earmark-fill"></i>
                                <?= htmlspecialchars($item['ext']) ?>
                            </span>
                        </div>
                    </div>
                    <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES) ?>"
                       class="dl-btn"
                       download>
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>

            </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endforeach; ?>

</main>

<footer style="background:#1e293b;color:rgba(255,255,255,.4);padding:2rem 0;text-align:center;margin-top:2rem;">
    <div class="container">
        <p style="font-size:.8rem;margin:0;">
            &copy; 2026 BookIT. Alle Rechte vorbehalten.
            &nbsp;·&nbsp; <a href="/about.php" style="color:rgba(255,255,255,.4);">Über uns</a>
            &nbsp;·&nbsp; <a href="/impressum.php" style="color:rgba(255,255,255,.4);">Impressum</a>
            &nbsp;·&nbsp; <a href="/#contact" style="color:rgba(255,255,255,.4);">Kontakt</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>