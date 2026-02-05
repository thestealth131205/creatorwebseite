<?php
/**
 * db_config.php
 * Lädt Umgebungsvariablen aus /config/.env und stellt die PDO-Verbindung bereit.
 */

// Pfad zur .env Datei im Unterordner 'config'
$envPath = __DIR__ . '/config/.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Kommentare überspringen
        if (strpos(trim($line), '#') === 0) continue;
        
        // Key=Value Paare trennen
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            // Entferne potenzielle Anführungszeichen um den Wert
            $value = trim($parts[1], " \t\n\r\0\x0B\"'");
            
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

// Zugangsdaten aus den Umgebungsvariablen laden (mit Fallbacks)
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'thestealth_db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';

try {
    /**
     * WICHTIG: charset=utf8mb4 im DSN sorgt dafür, dass Umlaute 
     * bereits beim Senden an die DB korrekt kodiert werden.
     */
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // Zusätzliche Absicherung für UTF-8
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    // Fehler als JSON ausgeben, falls die API aufgerufen wird
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    die(json_encode([
        'success' => false, 
        'error' => 'Datenbank-Verbindung fehlgeschlagen',
        'message' => $e->getMessage()
    ]));
}
?>