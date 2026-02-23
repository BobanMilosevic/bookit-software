<?php
// Mock-Buchungssystem für BookIT - Keine echte Datenbank oder E-Mail

// Simulierte Räume mit Belegungen
$rooms = [
    ['id' => 1, 'name' => 'Konferenzraum A', 'available' => true, 'booked_until' => null],
    ['id' => 2, 'name' => 'Konferenzraum B', 'available' => false, 'booked_until' => '14:00'], // Belegt bis 14:00
    ['id' => 3, 'name' => 'Meetingraum 1', 'available' => true, 'booked_until' => null],
    ['id' => 4, 'name' => 'Meetingraum 2', 'available' => true, 'booked_until' => null],
];

// Buchungslogik (Mock)
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = $_POST['email'];
    $room_id = $_POST['room'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = (int)$_POST['duration'];
    
    // Berechne Endzeit
    $start_hour = (int)explode(':', $time)[0];
    $end_hour = $start_hour + $duration;
    $end_time = str_pad($end_hour, 2, '0', STR_PAD_LEFT) . ':00';
    
    // Prüfe Verfügbarkeit (simuliert)
    $room_available = false;
    $room_name = '';
    foreach ($rooms as $room) {
        if ($room['id'] == $room_id) {
            $room_name = $room['name'];
            if ($room['available']) {
                $room_available = true;
            } elseif ($room['booked_until'] && $start_hour >= (int)explode(':', $room['booked_until'])[0]) {
                $room_available = true; // Nach Belegung frei
            }
            break;
        }
    }
    
    if ($room_available && $end_hour <= 20) {
        // Generiere Mock-Verifizierungscode
        $verification_code = rand(100000, 999999);
        
        // Mock-Bestätigung
        $message = "<div class='alert alert-success'><strong>Buchung simuliert erfolgreich!</strong><br>E-Mail: $user_email<br>Raum: $room_name<br>Datum: $date<br>Zeitraum: $time - $end_time<br>Dein Verifizierungscode: <strong>$verification_code</strong><br>Scanne den QR-Code vor Ort und gib den Code ein.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Raum ist in diesem Zeitraum belegt oder Buchung überschreitet 20:00.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookIT - Raum buchen</title>
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
        body { font-family: 'Inter', sans-serif; background: var(--light-bg); color: var(--dark-text); }
        .hero { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 80px 0; text-align: center; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat; opacity: 0.1; }
        .hero h1 { font-size: 3rem; font-weight: 700; margin-bottom: 1rem; }
        .logo { font-size: 2em; font-weight: bold; margin-bottom: 20px; }
        .btn { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 12px; font-weight: 600; padding: 0.75rem 2rem; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; }
        footer { background: var(--dark-text); color: white; padding: 3rem 0; text-align: center; margin-top: 5rem; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeInUp 0.8s ease-out; }
        .section-title { font-size: 2.5rem; font-weight: 700; margin-bottom: 3rem; color: var(--dark-text); }
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
                    <li class="nav-item"><a class="nav-link" href="mock_checkin.php">Check-in</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <div style="background: rgba(255,255,255,0.9); padding: 20px; border-radius: 20px; display: inline-block; margin-bottom: 20px;">
                <img src="logo.png" alt="BookIT Logo" style="max-width: 250px; display: block; margin: 0 auto;">
            </div>
            <h1>Raum buchen</h1>
            <p>Simulierte Buchung – Keine echte Datenbank oder E-Mail.</p>
        </div>
    </section>

    <div class="container my-5">
        <h2 class="text-center mb-4">Verfügbare Räume</h2>
        <div class="row">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-3 mb-3">
                    <div class="card <?php echo $room['available'] ? 'border-success' : 'border-danger'; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo $room['name']; ?></h5>
                            <p class="card-text <?php echo $room['available'] ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $room['available'] ? 'Verfügbar' : 'Belegt bis ' . $room['booked_until']; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php echo $message; ?>
        <div class="row justify-content-center mt-4">
            <div class="col-md-6">
                <form method="POST" action="" class="p-4 border rounded shadow">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-Mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="room" class="form-label">Raum auswählen</label>
                        <select class="form-control" id="room" name="room" required>
                            <option value="">-- Raum wählen --</option>
                            <?php foreach ($rooms as $room): ?>
                                <?php if ($room['available']): ?>
                                    <option value="<?php echo $room['id']; ?>"><?php echo $room['name']; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Datum</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Startzeit (8:00 - 20:00)</label>
                        <select class="form-control" id="time" name="time" required>
                            <option value="">-- Startzeit wählen --</option>
                            <?php for ($hour = 8; $hour <= 20; $hour++): ?>
                                <option value="<?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?>"><?php echo str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00'; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Dauer (Stunden)</label>
                        <select class="form-control" id="duration" name="duration" required>
                            <option value="">-- Dauer wählen --</option>
                            <option value="1">1 Stunde</option>
                            <option value="2">2 Stunden</option>
                            <option value="3">3 Stunden</option>
                            <option value="4">4 Stunden</option>
                            <option value="5">5 Stunden</option>
                            <option value="6">6 Stunden</option>
                            <option value="7">7 Stunden</option>
                            <option value="8">8 Stunden</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Buchen</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 BookIT. Alle Rechte vorbehalten.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>