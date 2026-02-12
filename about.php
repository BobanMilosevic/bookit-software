<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookIT - About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #06b6d4;
            --light-bg: #f8fafc;
            --dark-text: #1e293b;
        }
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark-text); overflow-x: hidden; }
        .hero { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 80px 0; text-align: center; position: relative; overflow: hidden; transform: translateZ(0); }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat; opacity: 0.1; animation: float 20s ease-in-out infinite; }
        .hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 1rem; animation: slideInFromTop 1s ease-out; }
        .logo { font-size: 2em; font-weight: bold; margin-bottom: 20px; }
        .btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 12px; font-weight: 600; padding: 0.75rem 2rem; position: relative; overflow: hidden; }
        .btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s; }
        .btn:hover::before { left: 100%; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; }
        footer { background: var(--dark-text); color: white; padding: 3rem 0; text-align: center; margin-top: 5rem; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeInUp 0.8s ease-out; }
        .section-title { font-size: 2.5rem; font-weight: 700; margin-bottom: 3rem; color: var(--dark-text); }
        @keyframes slideInFromTop { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Start</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="impressum.php">Impressum</a></li>
                    <li class="nav-item"><a class="nav-link" href="mock_booking.php">Demo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo" style="max-width: 250px; display: block; margin: 0 auto;">
            </div>
            <h1>About Us</h1>
        </div>
    </section>

    <div class="container my-5 fade-in">
        <h2>Über BookIT</h2>
        <p>BookIT wurde 2023 in Berlin, Deutschland, gegründet. Unser Ziel ist es, Raumverwaltung für Unternehmen und Organisationen zu vereinfachen. Mit einem Team von erfahrenen Entwicklern und UX-Designern bieten wir innovative Lösungen für moderne Arbeitsumgebungen.</p>
        <h4>Unser Standort</h4>
        <p>BookIT GmbH<br>Hauptstraße 123<br>10115 Berlin<br>Deutschland</p>
        <p>Telefon: +49 30 12345678<br>E-Mail: info@bookit.com</p>
        <h4>Warum BookIT?</h4>
        <p>Wir glauben an benutzerfreundliche Technologie, die den Alltag erleichtert. Neben unserer Buchungssoftware bieten wir auf Anfrage komplette Website-Entwicklung und Server-Hosting an, um Ihre digitale Präsenz zu optimieren.</p>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten. | <a href="about.php">About Us</a> | <a href="impressum.php">Impressum</a> | <a href="index.php#contact">Kontakt</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>