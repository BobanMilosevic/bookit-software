<?php
require __DIR__ . '/../app/auth/require_login.php';

$displayName = ($_SESSION['user_name'] ?? '') !== '' ? $_SESSION['user_name'] : ($_SESSION['user_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BookIT</title>
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

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light">
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
                    <li class="nav-item"><a class="nav-link" href="#bookings">Meine Buchungen</a></li>
                    <li class="nav-item"><a class="nav-link" href="#rooms">Räume</a></li>
                    <li class="nav-item"><a class="nav-link" href="customer-news.php">News</a></li>
                    <?php if (($_SESSION['user_role'] ?? 'customer') === 'employee'): ?>
                        <li class="nav-item"><a class="nav-link" href="internal-news.php">Interne News</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="#profile">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">Abmelden</a></li>
                </ul>
            </div>
            <div class="container mt-3">
                <div class="alert alert-success">
                    ✅ Eingeloggt als <strong>
                        <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                    (User-ID:
                    <?= (int) $_SESSION['user_id'] ?>)
                </div>
            </div>
        </div>
    </nav>

    <section class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 id="welcomeMessage">Willkommen zurück!</h1>
                    <p>Verwalten Sie Ihre Raumreservierungen und Einstellungen.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="mock_booking.php" class="btn btn-light btn-lg">Neue Buchung</a>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <!-- Statistiken -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="bi bi-calendar-check" style="font-size: 2rem; color: var(--primary-color);"></i>
                    <div class="stats-number" id="totalBookings">0</div>
                    <div>Gesamt Buchungen</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="bi bi-clock" style="font-size: 2rem; color: var(--accent-color);"></i>
                    <div class="stats-number" id="upcomingBookings">0</div>
                    <div>Anstehende</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="bi bi-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                    <div class="stats-number" id="completedBookings">0</div>
                    <div>Abgeschlossen</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="bi bi-x-circle" style="font-size: 2rem; color: #dc3545;"></i>
                    <div class="stats-number" id="cancelledBookings">0</div>
                    <div>Storniert</div>
                </div>
            </div>
        </div>

        <!-- Aktuelle Buchungen -->
        <div class="row">
            <div class="col-md-8">
                <h3 class="mb-3">Meine Buchungen</h3>
                <div id="bookingsList">
                    <div class="booking-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5>Konferenzraum A</h5>
                                <p class="mb-1"><i class="bi bi-calendar"></i> 25. Februar 2026, 14:00 - 16:00 Uhr</p>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> Gebäude 1, Raum 101</p>
                                <span class="badge bg-success">Bestätigt</span>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-sm btn-outline-primary mb-2">Ändern</button><br>
                                <button class="btn btn-sm btn-outline-danger">Stornieren</button>
                            </div>
                        </div>
                    </div>

                    <div class="booking-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5>Besprechungsraum B</h5>
                                <p class="mb-1"><i class="bi bi-calendar"></i> 28. Februar 2026, 10:00 - 11:30 Uhr</p>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> Gebäude 2, Raum 205</p>
                                <span class="badge bg-warning">Ausstehend</span>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-sm btn-outline-primary mb-2">Ändern</button><br>
                                <button class="btn btn-sm btn-outline-danger">Stornieren</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <h3 class="mb-3">Schnellzugriff</h3>

                <div class="stats-card">
                    <h5>Neue Buchung</h5>
                    <p>Suchen Sie verfügbare Räume und buchen Sie direkt.</p>
                    <a href="mock_booking.php" class="btn btn-primary btn-sm">Jetzt buchen</a>
                </div>

                <div class="stats-card">
                    <h5>Check-in</h5>
                    <p>Scannen Sie den QR-Code für den Raumzugang.</p>
                    <a href="mock_checkin.php" class="btn btn-outline-primary btn-sm">Check-in</a>
                </div>

                <div class="stats-card">
                    <h5>Profil bearbeiten</h5>
                    <p>Verwalten Sie Ihre persönlichen Daten.</p>
                    <button class="btn btn-outline-secondary btn-sm">Einstellungen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Demo-Statistiken (später aus DB laden)
        document.getElementById('totalBookings').textContent = '2';
        document.getElementById('upcomingBookings').textContent = '1';
        document.getElementById('completedBookings').textContent = '1';
        document.getElementById('cancelledBookings').textContent = '0';

        function logout() {
            window.location.href = '/auth/logout.php';
        }

        // Smooth scrolling für Navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>

</html>