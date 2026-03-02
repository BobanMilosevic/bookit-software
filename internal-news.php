<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interne News - BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #118075;
            --secondary-color: #4D8496;
            --accent-color: #80111B;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark-text); overflow-x: hidden; }
        .hero { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 80px 0; text-align: center; position: relative; overflow: hidden; transform: translateZ(0); }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.3); z-index: 1; }
        .hero > * { position: relative; z-index: 2; }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat; opacity: 0.1; animation: float 20s ease-in-out infinite; }
        .hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 1rem; }
        .logo { font-size: 3em; font-weight: bold; margin-bottom: 20px; }
        .news-card { margin: 20px 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: none; padding: 2rem; opacity: 0; transform: translateY(50px); transition: all 0.6s ease; }
        .news-card.visible { opacity: 1; transform: translateY(0); }
        .news-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .news-card .card-title { color: var(--primary-color); font-weight: 600; }
        .news-card .card-text { color: var(--dark-text); }
        .news-date { color: var(--secondary-color); font-size: 0.9rem; font-weight: 500; }
        .internal-badge { background: var(--accent-color); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
        .btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 12px; font-weight: 600; padding: 0.75rem 2rem; position: relative; overflow: hidden; }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s; }
        .btn:hover::before { left: 100%; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; }
        footer { background: var(--dark-text); color: white; padding: 3rem 0; text-align: center; margin-top: 5rem; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeInUp 0.8s ease-out; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="logo.png" alt="BookIT Logo" style="height: 40px; margin-right: 10px;">
                <span style="font-weight: 700; color: var(--primary-color);">BookIT</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="internal-news.php">Interne News</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer-news.php">Öffentliche News</a></li>
                    <li class="nav-item"><a class="nav-link" href="business-downloads.php">Business Downloads</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div style="padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo" style="max-width: 250px; display: block; margin: 0 auto; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.5));">
            </div>
            <h1>Interne News</h1>
            <p class="lead">Exklusive Informationen für BookIT-Mitarbeiter.</p>
            <div class="internal-badge">Nur für Mitarbeiter</div>
        </div>
    </section>

    <div class="container my-5 fade-in">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="news-card">
                    <div class="news-date mb-2">23. Februar 2026</div>
                    <h5 class="card-title">Neue Entwicklungsrichtlinien</h5>
                    <p class="card-text">Ab sofort gelten neue Coding-Standards für alle PHP-Entwicklungen. Bitte lesen Sie das aktualisierte Handbuch im internen Wiki.</p>
                    <a href="#" class="btn btn-primary">Im Wiki lesen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">22. Februar 2026</div>
                    <h5 class="card-title">Team-Meeting nächste Woche</h5>
                    <p class="card-text">Das wöchentliche Team-Meeting findet am Donnerstag um 14:00 Uhr statt. Agenda: Q1-Ziele und neue Feature-Planung.</p>
                    <a href="#" class="btn btn-primary">Zur Agenda</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">21. Februar 2026</div>
                    <h5 class="card-title">Server-Migration abgeschlossen</h5>
                    <p class="card-text">Die Migration auf die neuen Server ist erfolgreich abgeschlossen. Alle Systeme laufen stabil. Monitoring-Dashboard aktualisiert.</p>
                    <a href="#" class="btn btn-primary">Monitoring öffnen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">20. Februar 2026</div>
                    <h5 class="card-title">Neue Sicherheitsrichtlinien</h5>
                    <p class="card-text">Wichtige Updates zu unseren Sicherheitsprotokollen. Alle Mitarbeiter müssen das neue Training absolvieren.</p>
                    <a href="#" class="btn btn-primary">Training starten</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">19. Februar 2026</div>
                    <h5 class="card-title">Q1 Performance Review</h5>
                    <p class="card-text">Die Q1-Zahlen sind eingetroffen. Überdurchschnittliches Wachstum in allen Bereichen. Detaillierte Analyse im Dashboard verfügbar.</p>
                    <a href="#" class="btn btn-primary">Report ansehen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">18. Februar 2026</div>
                    <h5 class="card-title">Neue Mitarbeiter-Willkommensprozedur</h5>
                    <p class="card-text">Wir haben unseren Onboarding-Prozess optimiert. Neue Checkliste und Welcome-Paket stehen zur Verfügung.</p>
                    <a href="#" class="btn btn-primary">Checkliste öffnen</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten. | Internes System</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prüfe Benutzerrolle
        const currentUser = JSON.parse(localStorage.getItem('bookit_current_user') || 'null');
        
        if (!currentUser) {
            alert('Sie müssen sich zuerst anmelden.');
            window.location.href = 'login.php';
        } else if (currentUser.role !== 'employee') {
            alert('Zugriff verweigert. Diese Seite ist nur für Mitarbeiter zugänglich.');
            window.location.href = 'customer-news.php';
        }

        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.news-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>