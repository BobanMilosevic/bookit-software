<?php
require __DIR__ . '/../app/auth/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressum – BookIT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/index.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,700;9..144,900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
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
            --radius:    14px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* Sticky footer */
        html, body { height: 100%; }
        body {
            display: flex;
            flex-direction: column;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }
        main { flex: 1; }

        /* ── Hero ────────────────────────────────────────── */
        .imp-hero {
            background: linear-gradient(135deg, var(--ink) 0%, #2d3f55 100%);
            color: #fff;
            padding: 50px 0 164px;
            position: relative;
            overflow: hidden;
        }

        .imp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .imp-hero .container { position: relative; z-index: 1; }

        .imp-hero__eyebrow {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255,255,255,.5);
            margin-bottom: .75rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .imp-hero h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -.025em;
            margin-bottom: .75rem;
        }

        .imp-hero p {
            font-size: .92rem;
            color: rgba(255,255,255,.55);
            max-width: 420px;
        }

        /* ── Content layout ──────────────────────────────── */
        .imp-body { padding: 56px 0 80px; }

        .imp-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        @media (max-width: 768px) { .imp-grid { grid-template-columns: 1fr; } }

        /* ── Sidebar ─────────────────────────────────────── */
        .imp-nav {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            position: sticky;
            top: 1.5rem;
        }

        .imp-nav__head {
            padding: .9rem 1.2rem;
            background: #f8f7f4;
            border-bottom: 1px solid var(--border);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--ink-30);
        }

        .imp-nav a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.2rem;
            font-size: .83rem;
            font-weight: 500;
            color: var(--ink-60);
            text-decoration: none;
            border-bottom: 1px solid var(--border);
            transition: background .15s, color .15s;
        }

        .imp-nav a:last-child { border-bottom: none; }
        .imp-nav a:hover { background: #f5f4f0; color: var(--ink); }
        .imp-nav a i { font-size: .9rem; color: var(--ink-30); }

        /* ── Main content ────────────────────────────────── */
        .imp-section {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            margin-bottom: 1.5rem;
            scroll-margin-top: 2rem;
        }

        .imp-section__head {
            padding: 1.1rem 1.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .imp-section__icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #f0faf9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--green);
            flex-shrink: 0;
        }

        .imp-section__title {
            font-family: 'Fraunces', serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            margin: 0;
        }

        .imp-section__body {
            padding: 1.5rem 1.75rem;
        }

        /* ── Data rows ───────────────────────────────────── */
        .imp-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: .5rem 1.5rem;
            padding: .55rem 0;
            border-bottom: 1px solid #f2f1ed;
            font-size: .88rem;
            align-items: baseline;
        }

        .imp-row:last-child { border-bottom: none; }

        .imp-row__label {
            font-weight: 600;
            color: var(--ink-30);
            font-size: .78rem;
            letter-spacing: .03em;
        }

        .imp-row__value { color: var(--ink-60); }
        .imp-row__value a { color: var(--green); text-decoration: none; }
        .imp-row__value a:hover { text-decoration: underline; }

        /* ── Prose ───────────────────────────────────────── */
        .imp-prose {
            font-size: .9rem;
            color: var(--ink-60);
            line-height: 1.75;
        }

        .imp-prose p { margin-bottom: .9rem; }
        .imp-prose p:last-child { margin-bottom: 0; }

        .imp-prose strong { color: var(--ink); font-weight: 600; }

        /* ── Notice box ──────────────────────────────────── */
        .imp-notice {
            background: #f0faf9;
            border: 1px solid #b6dcd8;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            font-size: .85rem;
            color: var(--green-mid);
            margin-top: 1.25rem;
        }

        .imp-notice i { margin-top: .1rem; flex-shrink: 0; }

        /* ── Footer ──────────────────────────────────────── */
        footer {
            background: var(--ink);
            color: rgba(255,255,255,.4);
            text-align: center;
            padding: 2.5rem 0;
            font-size: .82rem;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        footer a { color: rgba(255,255,255,.5); text-decoration: none; }
        footer a:hover { color: rgba(255,255,255,.85); }

        @media (max-width: 580px) {
            .imp-row { grid-template-columns: 1fr; gap: .15rem; }
        }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

    <!-- ── Hero ───────────────────────────────────────────── -->
    <div class="imp-hero">
        <div class="container">
            <div class="imp-hero__eyebrow"><i class="bi bi-file-text"></i> Rechtliches</div>
            <h1>Impressum</h1>
            <p>Angaben gemäß § 5 ECG und § 25 MedienG</p>
        </div>
    </div>

    <main>
        <div class="imp-body">
            <div class="container">
                <div class="imp-grid">

                    <!-- ── Sidebar Navigation ──────────────── -->
                    <aside>
                        <nav class="imp-nav">
                            <div class="imp-nav__head">Inhalt</div>
                            <a href="#anbieter"><i class="bi bi-building"></i> Anbieter</a>
                            <a href="#kontakt"><i class="bi bi-envelope"></i> Kontakt</a>
                            <a href="#ust"><i class="bi bi-receipt"></i> UID & Behörde</a>
                            <a href="#haftung"><i class="bi bi-shield-check"></i> Haftung</a>
                            <a href="#urheberrecht"><i class="bi bi-c-circle"></i> Urheberrecht</a>
                            <a href="#datenschutz"><i class="bi bi-lock"></i> Datenschutz</a>
                        </nav>
                    </aside>

                    <!-- ── Sections ────────────────────────── -->
                    <div>

                        <!-- Anbieter -->
                        <div class="imp-section" id="anbieter">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-building-fill"></i></div>
                                <h2 class="imp-section__title">Angaben zum Anbieter</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-row">
                                    <span class="imp-row__label">Unternehmen</span>
                                    <span class="imp-row__value"><strong>BookIT GmbH</strong></span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Rechtsform</span>
                                    <span class="imp-row__value">Gesellschaft mit beschränkter Haftung (GmbH)</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Adresse</span>
                                    <span class="imp-row__value">Plesserstraße 1, 3380 Pöchlarn</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Geschäftsführer</span>
                                    <span class="imp-row__value">Sebastian Hauss</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kontakt -->
                        <div class="imp-section" id="kontakt">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-envelope-fill"></i></div>
                                <h2 class="imp-section__title">Kontakt</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-row">
                                    <span class="imp-row__label">E-Mail</span>
                                    <span class="imp-row__value">
                                        <a href="mailto:hallo@bookit.at">office@bookit.at</a>
                                    </span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Telefon</span>
                                    <span class="imp-row__value">
                                        <a href="tel:+4312345678">+43 1 234 56 78</a>
                                    </span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Website</span>
                                    <span class="imp-row__value">
                                        <a href="https://www.bookit.at">www.bookit.at</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- UID & Behörde -->
                        <div class="imp-section" id="ust">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-receipt-cutoff"></i></div>
                                <h2 class="imp-section__title">UID-Nummer & Behörde</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-row">
                                    <span class="imp-row__label">UID-Nummer</span>
                                    <span class="imp-row__value">ATU12345678</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Firmenbuchnummer</span>
                                    <span class="imp-row__value">FN 123456 a</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Firmenbuchgericht</span>
                                    <span class="imp-row__value">Handelsgericht Wien</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Aufsichtsbehörde</span>
                                    <span class="imp-row__value">Magistrat der Stadt Wien</span>
                                </div>
                                <div class="imp-row">
                                    <span class="imp-row__label">Berufsrecht</span>
                                    <span class="imp-row__value">Gewerbeordnung (GewO), abrufbar unter <a href="https://www.ris.bka.gv.at" target="_blank" rel="noopener">www.ris.bka.gv.at</a></span>
                                </div>
                            </div>
                        </div>

                        <!-- Haftung -->
                        <div class="imp-section" id="haftung">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-shield-fill-check"></i></div>
                                <h2 class="imp-section__title">Haftungsausschluss</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-prose">
                                    <p><strong>Haftung für Inhalte:</strong> Die Inhalte dieser Website wurden mit größtmöglicher Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen.</p>
                                    <p><strong>Haftung für Links:</strong> Unsere Website enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Zum Zeitpunkt der Verlinkung waren keine Rechtsverstöße erkennbar.</p>
                                    <div class="imp-notice">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>Bei bekannt werden von Rechtsverletzungen werden wir derartige Links umgehend entfernen. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Urheberrecht -->
                        <div class="imp-section" id="urheberrecht">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-c-circle-fill"></i></div>
                                <h2 class="imp-section__title">Urheberrecht</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-prose">
                                    <p>Die durch den Seitenbetreiber erstellten Inhalte und Werke auf dieser Website unterliegen dem österreichischen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechts bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.</p>
                                    <p>Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, werden die Urheberrechte Dritter beachtet.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Datenschutz -->
                        <div class="imp-section" id="datenschutz">
                            <div class="imp-section__head">
                                <div class="imp-section__icon"><i class="bi bi-lock-fill"></i></div>
                                <h2 class="imp-section__title">Datenschutzhinweis</h2>
                            </div>
                            <div class="imp-section__body">
                                <div class="imp-prose">
                                    <p>Die Nutzung dieser Website ist in der Regel ohne Angabe personenbezogener Daten möglich. Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, Anschrift oder E-Mail-Adressen) erhoben werden, erfolgt dies, soweit möglich, stets auf freiwilliger Basis.</p>
                                    <p>Wir weisen darauf hin, dass die Datenübertragung im Internet (z. B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht möglich.</p>
                                    <p>Nähere Informationen zur Verarbeitung personenbezogener Daten finden Sie in unserer <a href="datenschutz.php">Datenschutzerklärung</a>.</p>
                                </div>
                            </div>
                        </div>

                        <p style="font-size:.75rem; color:var(--ink-30); margin-top:1rem; text-align:right;">
                            Stand: Februar 2026
                        </p>

                    </div><!-- /main col -->
                </div><!-- /grid -->
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            &copy; 2026 BookIT. Alle Rechte vorbehalten.
            &nbsp;·&nbsp;<a href="about.php">Über uns</a>
            &nbsp;·&nbsp;<a href="impressum.php">Impressum</a>
            &nbsp;·&nbsp;<a href="index.php#contact">Kontakt</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>