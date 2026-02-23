<?php
declare(strict_types=1);

// Fehler sichtbar machen (nur lokal!)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "Start<br>";

try {
    require __DIR__ . '/db.php';
    echo "db.php geladen<br>";

    $pdo = db();
    echo "DB Verbindung erfolgreich!<br>";

    // Mini-Testquery
    $stmt = $pdo->query("SELECT 1");
    echo "Query OK: " . $stmt->fetchColumn() . "<br>";

} catch (Throwable $e) {
    echo "<pre>";
    echo "Fehler:\n";
    echo $e->getMessage() . "\n\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}