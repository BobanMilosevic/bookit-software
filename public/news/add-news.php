<?php
declare(strict_types=1);

// Login voraussetzen
require __DIR__ . '/auth/require_login.php';
require __DIR__ . '/db.php';

$success = '';
$error   = '';

// Formular abgeschickt?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim((string)($_POST['title']   ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $type    = in_array($_POST['type'] ?? '', ['internal', 'customer'], true)
               ? $_POST['type']
               : 'customer';

    if ($title === '' || $content === '') {
        $error = 'Bitte Titel und Inhalt ausfüllen.';
    } else {
        $pdo  = db();
        $stmt = $pdo->prepare(
            'INSERT INTO news (title, content, type, author_id, created_at)
             VALUES (:title, :content, :type, :author_id, NOW())'
        );
        $stmt->execute([
            ':title'     => $title,
            ':content'   => $content,
            ':type'      => $type,
            ':author_id' => $_SESSION['user_id'],
        ]);
        $success = 'News-Post wurde erfolgreich erstellt!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News erstellen – BookIT</title>
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
        .navbar { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem 0;
        }
        .cms-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: -2rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(17,128,117,.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            font-weight: 600;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        label { font-weight: 500; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="logo.png" alt="BookIT Logo" style="height:40px; margin-right:10px;">
            <span style="font-weight:700; color:var(--primary-color);">BookIT</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="customer-news.php">News</a></li>
                <?php if (($_SESSION['user_role'] ?? 'customer') === 'employee'): ?>
                <li class="nav-item"><a class="nav-link" href="internal-news.php">Interne News</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link active" href="add-news.php">
                    <i class="bi bi-plus-circle me-1"></i>News erstellen
                </a></li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Header -->
<div class="page-header">
    <div class="container">
        <h1 class="fw-bold mb-1"><i class="bi bi-newspaper me-2"></i>News erstellen</h1>
        <p class="mb-0 opacity-75">Erstelle einen neuen News-Post für Kunden oder intern.</p>
    </div>
</div>

<!-- CMS-Formular -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="cms-card">

                <?php if ($success !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="add-news.php" novalidate>

                    <!-- Titel -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Titel <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control"
                            id="title"
                            name="title"
                            placeholder="News-Titel eingeben…"
                            maxlength="255"
                            required
                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        >
                    </div>

                    <!-- Inhalt -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Inhalt <span class="text-danger">*</span></label>
                        <textarea
                            class="form-control"
                            id="content"
                            name="content"
                            rows="8"
                            placeholder="Inhalt des News-Posts eingeben…"
                            required
                        ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    </div>

                    <!-- Typ -->
                    <div class="mb-4">
                        <label for="type" class="form-label">Kategorie</label>
                        <select class="form-select" id="type" name="type">
                            <option value="customer"
                                <?= (($_POST['type'] ?? 'customer') === 'customer') ? 'selected' : '' ?>>
                                🌐 Kunden-News (öffentlich)
                            </option>
                            <?php if (($_SESSION['user_role'] ?? 'customer') === 'employee'): ?>
                            <option value="internal"
                                <?= (($_POST['type'] ?? '') === 'internal') ? 'selected' : '' ?>>
                                🔒 Interne News (nur Mitarbeiter)
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send-fill me-2"></i>Post veröffentlichen
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i>Abbrechen
                        </a>
                    </div>

                </form>
            </div>

            <!-- Letzte News-Posts -->
            <div class="mt-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Zuletzt erstellt</h5>
                <?php
                $pdo   = db();
                $stmt  = $pdo->prepare(
                    'SELECT n.id, n.title, n.type, n.created_at, u.username
                     FROM news n
                     LEFT JOIN users u ON u.idusers = n.author_id
                     ORDER BY n.created_at DESC
                     LIMIT 5'
                );
                $stmt->execute();
                $recent = $stmt->fetchAll();
                ?>
                <?php if (empty($recent)): ?>
                    <p class="text-muted">Noch keine News vorhanden.</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($recent as $item): ?>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                            <small class="d-block text-muted">
                                von <?= htmlspecialchars($item['username'] ?? 'Unbekannt') ?>
                                · <?= date('d.m.Y H:i', strtotime($item['created_at'])) ?>
                            </small>
                        </div>
                        <span class="badge <?= $item['type'] === 'internal' ? 'bg-warning text-dark' : 'bg-success' ?> ms-2">
                            <?= $item['type'] === 'internal' ? 'Intern' : 'Kunden' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>