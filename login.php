<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BookIT</title>
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo img {
            height: 60px;
            margin-bottom: 1rem;
        }
        .logo h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin: 0;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .links a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.875rem;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="logo.png" alt="BookIT Logo">
            <h2>BookIT</h2>
            <p class="text-muted">Raumverwaltung leicht gemacht</p>
        </div>

        <div id="alert-container"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">E-Mail</label>
                <input type="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Passwort</label>
                <input type="password" class="form-control" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Anmelden</button>
        </form>

        <div class="links">
            <a href="#" onclick="showRegister()">Noch kein Konto? Registrieren</a><br>
            <a href="index.php">Zurück zur Startseite</a>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrieren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        <div class="mb-3">
                            <label for="regName" class="form-label">Vollständiger Name</label>
                            <input type="text" class="form-control" id="regName" required>
                        </div>
                        <div class="mb-3">
                            <label for="regEmail" class="form-label">E-Mail</label>
                            <input type="email" class="form-control" id="regEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="regPassword" class="form-label">Passwort</label>
                            <input type="password" class="form-control" id="regPassword" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="regConfirmPassword" class="form-label">Passwort bestätigen</label>
                            <input type="password" class="form-control" id="regConfirmPassword" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrieren</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Demo User Datenbank (für Entwicklung)
        const users = JSON.parse(localStorage.getItem('bookit_users') || '[]');
        let currentUser = JSON.parse(localStorage.getItem('bookit_current_user') || 'null');

        // Prüfe ob User bereits eingeloggt ist
        if (currentUser) {
            window.location.href = 'dashboard.php';
        }

        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function showRegister() {
            const modal = new bootstrap.Modal(document.getElementById('registerModal'));
            modal.show();
        }

        // Login Form Handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Finde User
            const user = users.find(u => u.email === email && u.password === password);

            if (user) {
                currentUser = user;
                localStorage.setItem('bookit_current_user', JSON.stringify(user));
                showAlert('Erfolgreich angemeldet! Weiterleitung...', 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                showAlert('Ungültige E-Mail oder Passwort!');
            }
        });

        // Register Form Handler
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regConfirmPassword').value;

            // Validierung
            if (password !== confirmPassword) {
                showAlert('Passwörter stimmen nicht überein!');
                return;
            }

            if (password.length < 6) {
                showAlert('Passwort muss mindestens 6 Zeichen lang sein!');
                return;
            }

            // Prüfe ob E-Mail bereits existiert
            if (users.find(u => u.email === email)) {
                showAlert('Diese E-Mail-Adresse ist bereits registriert!');
                return;
            }

            // Neuen User erstellen
            const newUser = {
                id: Date.now(),
                name: name,
                email: email,
                password: password,
                createdAt: new Date().toISOString()
            };

            users.push(newUser);
            localStorage.setItem('bookit_users', JSON.stringify(users));

            // Modal schließen und Erfolg anzeigen
            const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
            modal.hide();

            showAlert('Registrierung erfolgreich! Sie können sich jetzt anmelden.', 'success');

            // Form zurücksetzen
            document.getElementById('registerForm').reset();
        });
    </script>
</body>
</html>