<?php
require __DIR__ . '/../app/auth/bootstrap.php';

require __DIR__ . '/../app/auth/require_login.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunden News - BookIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
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
        .news-card { margin: 20px 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: none; padding: 2rem; transform: translateY(50px); transition: all 0.6s ease; }
        .news-card.visible { opacity: 1; transform: translateY(0); }
        .news-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .news-card .card-title { color: var(--primary-color); font-weight: 600; }
        .news-card .card-text { color: var(--dark-text); }
        .news-date { color: var(--secondary-color); font-size: 0.9rem; font-weight: 500; }
        .customer-badge { background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Startseite</a></li>
                    <li class="nav-item"><a class="nav-link active" href="customer-news.php">News</a></li>
                    <li class="nav-item" id="internalNewsLink" style="display: none;"><a class="nav-link" href="internal-news.php">Interne News</a></li>
                    <li class="nav-item" id="loginLink"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item" id="logoutLink" style="display: none;"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div style="padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo" style="max-width: 250px; display: block; margin: 0 auto; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.5));">
            </div>
            <h1>Kunden News</h1>
            <p class="lead">Bleiben Sie informiert über die neuesten Entwicklungen bei BookIT.</p>
            <div class="customer-badge">Für alle Kunden</div>
        </div>
    </section>

    <div class="container my-5 fade-in">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="news-card">
                    <div class="news-date mb-2">23. Februar 2026</div>
                    <h5 class="card-title">Neue Funktionen verfügbar</h5>
                    <p class="card-text">Wir haben unser Buchungssystem um mehrere neue Funktionen erweitert. Jetzt können Sie Räume für längere Zeiträume reservieren und automatische Erinnerungen einstellen.</p>
                    <a href="mock_booking.php" class="btn btn-primary">Jetzt ausprobieren</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">22. Februar 2026</div>
                    <h5 class="card-title">Wartungsarbeiten am Wochenende</h5>
                    <p class="card-text">Am kommenden Wochenende führen wir wichtige Wartungsarbeiten durch. Das System wird von 22:00 bis 06:00 Uhr nicht verfügbar sein.</p>
                    <a href="#" class="btn btn-primary">Mehr Informationen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">21. Februar 2026</div>
                    <h5 class="card-title">Neue Räume hinzugefügt</h5>
                    <p class="card-text">Wir freuen uns, Ihnen drei neue Konferenzräume präsentieren zu können. Alle Räume sind mit modernster Technik ausgestattet.</p>
                    <a href="#" class="btn btn-primary">Räume ansehen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">20. Februar 2026</div>
                    <h5 class="card-title">Mobile App jetzt verfügbar</h5>
                    <p class="card-text">Unsere neue mobile App ist jetzt im App Store und bei Google Play verfügbar. Verwalten Sie Ihre Buchungen unterwegs.</p>
                    <a href="#" class="btn btn-primary">App herunterladen</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">19. Februar 2026</div>
                    <h5 class="card-title">Kundenfeedback-Umfrage</h5>
                    <p class="card-text">Ihre Meinung ist uns wichtig! Nehmen Sie an unserer kurzen Umfrage teil und helfen Sie uns, unser Service zu verbessern.</p>
                    <a href="#" class="btn btn-primary">Umfrage starten</a>
                </div>

                <div class="news-card">
                    <div class="news-date mb-2">18. Februar 2026</div>
                    <h5 class="card-title">Neue Zahlungsmethoden</h5>
                    <p class="card-text">Ab sofort akzeptieren wir auch Kryptowährungen und digitale Wallets als Zahlungsmethoden für Ihre Buchungen.</p>
                    <a href="#" class="btn btn-primary">Zahlungsmethoden anzeigen</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prüfe Login-Status und aktualisiere Navigation
        const currentUser = JSON.parse(localStorage.getItem('bookit_current_user') || 'null');
        
        if (currentUser) {
            // Benutzer ist eingeloggt
            document.getElementById('loginLink').style.display = 'none';
            document.getElementById('logoutLink').style.display = 'block';
            
            // Zeige internen News-Link nur für Mitarbeiter
            if (currentUser.role === 'employee') {
                document.getElementById('internalNewsLink').style.display = 'block';
            }
        } else {
            // Benutzer ist nicht eingeloggt
            document.getElementById('loginLink').style.display = 'block';
            document.getElementById('logoutLink').style.display = 'none';
            document.getElementById('internalNewsLink').style.display = 'none';
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