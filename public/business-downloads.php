<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Downloads - BookIT</title>
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

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
            overflow-x: hidden;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.3);
            z-index: 1;
        }

        .hero>* {
            position: relative;
            z-index: 2;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.1;
            animation: float 20s ease-in-out infinite;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        .business-badge {
            background: var(--accent-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .download-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: none;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .download-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .download-card .card-title {
            color: var(--primary-color);
            font-weight: 600;
        }

        .download-card .card-text {
            color: var(--dark-text);
        }

        .download-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .file-info {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .file-info .bi {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        footer {
            background: var(--dark-text);
            color: white;
            padding: 3rem 0;
            text-align: center;
            margin-top: 5rem;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../views/partials/navbar.php'; ?>

    <section class="hero">
        <div class="container">
            <div style="padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo"
                    style="max-width: 250px; display: block; margin: 0 auto; filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.5));">
            </div>
            <h1>Business Downloads</h1>
            <p class="lead">Exklusive Ressourcen für unsere Business-Kunden und Partner.</p>
            <div class="business-badge">Nur für Business-Kunden & autorisierte Mitarbeiter</div>
        </div>
    </section>

    <div class="container my-5 fade-in">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="download-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title"><i class="bi bi-file-earmark-pdf"></i> Technische Dokumentation</h5>
                            <p class="card-text">Umfassende technische Dokumentation für die Integration und
                                Administration von BookIT-Systemen.</p>
                            <div class="file-info">
                                <i class="bi bi-file-earmark"></i> BookIT_Technical_Documentation_v2.1.pdf (4.2 MB)<br>
                                <i class="bi bi-calendar"></i> Aktualisiert: 15. Februar 2026
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="download-btn" onclick="downloadFile('technical-docs')">
                                <i class="bi bi-download"></i> Herunterladen
                            </a>
                        </div>
                    </div>
                </div>

                <div class="download-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title"><i class="bi bi-file-earmark-spreadsheet"></i> API-Referenz</h5>
                            <p class="card-text">Vollständige API-Dokumentation mit Beispielen für die Integration in
                                bestehende Systeme.</p>
                            <div class="file-info">
                                <i class="bi bi-file-earmark"></i> BookIT_API_Reference_v3.0.xlsx (2.8 MB)<br>
                                <i class="bi bi-calendar"></i> Aktualisiert: 20. Februar 2026
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="download-btn" onclick="downloadFile('api-reference')">
                                <i class="bi bi-download"></i> Herunterladen
                            </a>
                        </div>
                    </div>
                </div>

                <div class="download-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title"><i class="bi bi-file-earmark-word"></i> Implementierungsleitfaden
                            </h5>
                            <p class="card-text">Schritt-für-Schritt Anleitung für die erfolgreiche Implementierung von
                                BookIT in Unternehmen.</p>
                            <div class="file-info">
                                <i class="bi bi-file-earmark"></i> BookIT_Implementation_Guide_v1.5.docx (1.9 MB)<br>
                                <i class="bi bi-calendar"></i> Aktualisiert: 10. Februar 2026
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="download-btn" onclick="downloadFile('implementation-guide')">
                                <i class="bi bi-download"></i> Herunterladen
                            </a>
                        </div>
                    </div>
                </div>

                <div class="download-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title"><i class="bi bi-file-earmark-presentation"></i>
                                Präsentationsmaterialien</h5>
                            <p class="card-text">Professionelle Präsentationsvorlagen und Marketing-Materialien für
                                Partner und Kunden.</p>
                            <div class="file-info">
                                <i class="bi bi-file-earmark"></i> BookIT_Presentation_Kit_v1.2.pptx (8.7 MB)<br>
                                <i class="bi bi-calendar"></i> Aktualisiert: 5. Februar 2026
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="download-btn" onclick="downloadFile('presentation-kit')">
                                <i class="bi bi-download"></i> Herunterladen
                            </a>
                        </div>
                    </div>
                </div>

                <div class="download-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title"><i class="bi bi-file-earmark-text"></i> Sicherheitsrichtlinien</h5>
                            <p class="card-text">Umfassende Sicherheitsrichtlinien und Best Practices für den Betrieb
                                von BookIT-Systemen.</p>
                            <div class="file-info">
                                <i class="bi bi-file-earmark"></i> BookIT_Security_Guidelines_v1.3.pdf (3.1 MB)<br>
                                <i class="bi bi-calendar"></i> Aktualisiert: 1. März 2026
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="#" class="download-btn" onclick="downloadFile('security-guidelines')">
                                <i class="bi bi-download"></i> Herunterladen
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten. | Business Downloads - Nur für autorisierte Mitarbeiter</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prüfe Benutzerrolle und Zugriffsberechtigung
       // Client-seitige Prüfungen vorübergehend deaktiviert - Server-seitige Auth reicht
        // if (!currentUser) {
        //     alert('Sie müssen sich zuerst anmelden.');
        //     window.location.href = 'login.php';
        // }
        // // Rolle-Prüfung auskommentiert - alle haben Zugriff
        // // else if (currentUser.role !== 'business' && currentUser.role !== 'employee') {
        // //     alert('Zugriff verweigert. Diese Seite ist nur für Business-Kunden und Mitarbeiter zugänglich.');
        // //     window.location.href = 'customer-news.php';
        // // }
        // Navigation aktualisieren
        if (currentUser) {
            document.getElementById('loginLink').style.display = 'none';
            document.getElementById('logoutLink').style.display = 'block';

            if (currentUser.role === 'employee' || currentUser.role === 'business') {
                document.getElementById('internalNewsLink').style.display = 'block';
                document.getElementById('businessDownloadsLink').style.display = 'block';
            }
        }

        // Download-Funktion (simuliert)
        function downloadFile(fileType) {
            const fileNames = {
                'technical-docs': 'BookIT_Technical_Documentation_v2.1.pdf',
                'api-reference': 'BookIT_API_Reference_v3.0.xlsx',
                'implementation-guide': 'BookIT_Implementation_Guide_v1.5.docx',
                'presentation-kit': 'BookIT_Presentation_Kit_v1.2.pptx',
                'security-guidelines': 'BookIT_Security_Guidelines_v1.3.pdf'
            };

            const fileName = fileNames[fileType];
            if (fileName) {
                alert(`Download von "${fileName}" wird gestartet...\n\n(Dies ist eine Demo - in der echten Anwendung würde hier der tatsächliche Download beginnen)`);
            }
        }

        // Smooth scrolling für Navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>

</html>