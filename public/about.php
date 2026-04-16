<?php
require __DIR__ . '/../app/auth/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Über uns – BookIT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/index.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,700;9..144,900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">

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
            --radius:    16px;
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
        .about-hero {
            background: linear-gradient(135deg, var(--green), var(--blue));
            color: #fff;
            padding: 40px 0 220px;
            position: relative;
            overflow: hidden;
        }

        .about-hero__noise {
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(ellipse 70% 80% at 100% 0%, rgba(255,255,255,.06) 0%, transparent 60%),
                repeating-linear-gradient(
                    -55deg,
                    transparent, transparent 32px,
                    rgba(255,255,255,.025) 32px, rgba(255,255,255,.025) 33px
                );
            pointer-events: none;
        }

        .about-hero .container { position: relative; z-index: 1; }

        .about-hero__eyebrow {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: rgba(255,255,255,.6);
            margin-bottom: .85rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .about-hero h1 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.6rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -.025em;
            margin-bottom: 1.1rem;
        }

        .about-hero p {
            font-size: 1.05rem;
            color: rgba(255,255,255,.75);
            max-width: 540px;
            line-height: 1.7;
        }

        /* ── Section shared ──────────────────────────────── */
        .section { padding: 72px 0; }
        .section--alt { background: var(--white); }

        .section__eyebrow {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--green);
            margin-bottom: .6rem;
        }

        .section__title {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.6rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -.02em;
            color: var(--ink);
            margin-bottom: 1rem;
        }

        .section__lead {
            font-size: 1rem;
            color: var(--ink-60);
            max-width: 560px;
            line-height: 1.7;
        }

        /* ── Mission split ───────────────────────────────── */
        .mission-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .mission-grid { grid-template-columns: 1fr; gap: 2.5rem; }
        }

        .mission-visual {
            background: linear-gradient(135deg, var(--green), var(--blue));
            border-radius: 24px;
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .mission-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='36' stroke='rgba(255,255,255,.08)' stroke-width='1' fill='none'/%3E%3C/svg%3E") center/80px repeat;
        }

        .mission-visual i {
            font-size: 5rem;
            color: rgba(255,255,255,.7);
            position: relative;
            z-index: 1;
        }

        /* ── Value cards ─────────────────────────────────── */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 900px) { .values-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .values-grid { grid-template-columns: 1fr; } }

        .value-card {
            background: var(--bg);
            border-radius: var(--radius);
            padding: 2rem;
            border: 1px solid var(--border);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .55s ease, transform .55s ease;
        }

        .value-card.visible { opacity: 1; transform: none; }

        .value-card__icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--green);
            margin-bottom: 1.1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }

        .value-card h3 {
            font-family: 'Fraunces', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .5rem;
        }

        .value-card p {
            font-size: .875rem;
            color: var(--ink-60);
            line-height: 1.65;
        }

        /* ── Team ────────────────────────────────────────── */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 3rem;
        }

        @media (max-width: 900px) { .team-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .team-grid { grid-template-columns: 1fr; } }

        .team-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2rem 1.75rem;
            border: 1px solid var(--border);
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .55s ease, transform .55s ease, box-shadow .2s ease;
        }

        .team-card.visible { opacity: 1; transform: none; }
        .team-card:hover { box-shadow: 0 8px 24px rgba(15,28,46,.1); transform: translateY(-3px); }

        .team-card__avatar {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fraunces', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            margin: 0 auto 1.1rem;
        }

        .team-card h3 {
            font-family: 'Fraunces', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: .25rem;
        }

        .team-card__role {
            font-size: .78rem;
            font-weight: 600;
            color: var(--green);
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: .65rem;
        }

        .team-card p {
            font-size: .85rem;
            color: var(--ink-60);
            line-height: 1.6;
        }

        /* ── CTA strip ───────────────────────────────────── */
        .cta-strip {
            background: var(--ink);
            color: #fff;
            padding: 64px 0;
            text-align: center;
        }

        .cta-strip h2 {
            font-family: 'Fraunces', serif;
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            font-weight: 800;
            margin-bottom: .75rem;
            letter-spacing: -.02em;
        }

        .cta-strip p {
            color: rgba(255,255,255,.6);
            font-size: .97rem;
            margin-bottom: 2rem;
        }

        .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .8rem 2rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .2s ease;
        }

        .btn-cta--primary { background: var(--green); color: #fff; }
        .btn-cta--primary:hover { background: var(--green-mid); color: #fff; transform: translateY(-2px); }
        .btn-cta--outline { border: 1.5px solid rgba(255,255,255,.25); color: rgba(255,255,255,.8); }
        .btn-cta--outline:hover { border-color: #fff; color: #fff; }

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

        /* ── Reveal ──────────────────────────────────────── */
        .reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .65s ease, transform .65s ease;
        }
        .reveal.visible { opacity: 1; transform: none; }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

    <!-- ── Hero ───────────────────────────────────────────── -->
    <section class="about-hero">
        <div class="about-hero__noise"></div>
        <div class="container">
            <div class="about-hero__eyebrow"><i class="bi bi-building"></i> Über uns</div>
            <h1>Wir sind BookIT.</h1>
            <p>Eine österreichische Softwarelösung, die Raumverwaltung endlich so einfach macht wie sie sein sollte — schnell, sicher und ohne Papierkram.</p>
        </div>
    </section>

    <main>

        <!-- ── Mission ────────────────────────────────────── -->
        <section class="section">
            <div class="container">
                <div class="mission-grid">
                    <div class="reveal">
                        <div class="section__eyebrow">Unsere Mission</div>
                        <h2 class="section__title">Räume buchen.<br>Nicht bekämpfen.</h2>
                        <p class="section__lead">
                            BookIT entstand aus der Frustration über veraltete Buchungssysteme: Excel-Listen, E-Mail-Pingpong, doppelt gebuchte Räume. Wir haben das Problem selbst erlebt — und eine Lösung gebaut, die einfach funktioniert.
                        </p>
                        <p class="section__lead" style="margin-top:1rem;">
                            Heute nutzen Unternehmen, Schulen und Behörden in ganz Österreich BookIT, um ihre Räume effizient zu verwalten — vom kleinen Meetingraum bis zum großen Veranstaltungssaal.
                        </p>
                    </div>
                    <div class="reveal" style="transition-delay:.15s;">
                        <div class="mission-visual">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Values ─────────────────────────────────────── -->
        <section class="section section--alt">
            <div class="container">
                <div class="reveal">
                    <div class="section__eyebrow">Unsere Werte</div>
                    <h2 class="section__title">Was uns antreibt.</h2>
                </div>
                <div class="values-grid">
                    <div class="value-card reveal" style="transition-delay:0ms;">
                        <div class="value-card__icon"><i class="bi bi-lightning-charge-fill"></i></div>
                        <h3>Einfachheit</h3>
                        <p>Komplexe Prozesse hinter einer klaren, intuitiven Oberfläche verbergen — das ist unser Designprinzip.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay:80ms;">
                        <div class="value-card__icon"><i class="bi bi-shield-check-fill"></i></div>
                        <h3>Sicherheit</h3>
                        <p>Datenschutz nach DSGVO, gehostete Server in Österreich, keine Daten an Dritte. Ihr Vertrauen ist unser Standard.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay:160ms;">
                        <div class="value-card__icon"><i class="bi bi-arrow-repeat"></i></div>
                        <h3>Verlässlichkeit</h3>
                        <p>99,9 % Uptime, automatische Backups und ein Support-Team, das schnell antwortet — nicht erst übermorgen.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay:240ms;">
                        <div class="value-card__icon"><i class="bi bi-people-fill"></i></div>
                        <h3>Nähe</h3>
                        <p>Wir sind ein kleines österreichisches Team und kennen unsere Kunden persönlich. Kein Callcenter, kein Ticket-System.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay:320ms;">
                        <div class="value-card__icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <h3>Wachstum</h3>
                        <p>BookIT wächst mit Ihren Anforderungen. Skalierbar von 5 bis unbegrenzt viele Räume — ohne Systemwechsel.</p>
                    </div>
                    <div class="value-card reveal" style="transition-delay:400ms;">
                        <div class="value-card__icon"><i class="bi bi-stars"></i></div>
                        <h3>Innovation</h3>
                        <p>QR-Code-Check-in, automatische Erinnerungen, mobile App — wir entwickeln kontinuierlich weiter.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Team ───────────────────────────────────────── -->
        <section class="section">
            <div class="container">
                <div class="reveal">
                    <div class="section__eyebrow">Das Team</div>
                    <h2 class="section__title">Menschen hinter BookIT.</h2>
                    <p class="section__lead">Klein, erfahren und mit viel Herzblut dabei.</p>
                </div>
                <div class="team-grid">
                    <div class="team-card reveal" style="transition-delay:0ms;">
                        <div class="team-card__avatar">S</div>
                        <h3>Sebastian Hauss</h3>
                        <div class="team-card__role">Gründer & CEO</div>
                        <p>Softwareentwickler mit über 10 Jahren Erfahrung in SaaS-Produkten. Hat BookIT 2026 gegründet.</p>
                    </div>
                    <div class="team-card reveal" style="transition-delay:80ms;">
                        <div class="team-card__avatar" style="background: linear-gradient(135deg, var(--blue), #6baaba);">O</div>
                        <h3>Oliver Mauß</h3>
                        <div class="team-card__role">Lead Developer</div>
                        <p>Verantwortlich für das gesamte Backend und die API-Architektur.</p>
                    </div>
                    <div class="team-card reveal" style="transition-delay:160ms;">
                        <div class="team-card__avatar" style="background: linear-gradient(135deg, #555, #888);">B</div>
                        <h3>Boban Milosevic</h3>
                        <div class="team-card__role">UX & Design</div>
                        <p>Gestaltet die Oberflächen von BookIT. </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ────────────────────────────────────────── -->
        <div class="cta-strip reveal">
            <div class="container">
                <h2>Bereit, loszulegen?</h2>
                <p>Testen Sie BookIT kostenlos — keine Kreditkarte erforderlich.</p>
                <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
                    <a href="index.php#pricing" class="btn-cta btn-cta--primary">
                        <i class="bi bi-rocket-takeoff-fill"></i> Pakete ansehen
                    </a>
                    <a href="index.php#contact" class="btn-cta btn-cta--outline">
                        <i class="bi bi-envelope"></i> Kontakt aufnehmen
                    </a>
                </div>
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
    <script>
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

        document.querySelectorAll('.reveal, .value-card, .team-card').forEach(el => io.observe(el));
    </script>

</body>
</html>