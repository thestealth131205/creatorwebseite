<?php
require_once 'db_config.php';

/**
 * api.php - Zentrales Backend (Vollständige & Fehlerfreie Version)
 * Beinhaltet: Authentifizierung, Song-Management, Media-Uploads, Newsletter-Logik, 
 * Diskografie-Filter, detailliertes Besucher-Logging & System-Tools.
 */

// 1. Session & Umgebungsvariablen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zeitzone für korrekte Logs setzen
date_default_timezone_set('Europe/Berlin');
try {
    $pdo->exec("SET time_zone = '+01:00'"); 
} catch (Exception $e) {
    // Falls die Timezone-Tabelle in MySQL nicht geladen ist, ignorieren wir das
}

// Fehler-Konfiguration: Logging statt direkte Anzeige, um JSON-Output nicht zu zerstören
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. Header & CORS (Sicherheit und Interaktion mit React)
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Admin-Code");

// Preflight-Anfragen sofort beenden
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Admin-Sicherheitscode (aus ENV oder Fallback auf 1234)
$admin_code = getenv('ADMIN_CODE') ?: '1234';
$request_admin_code = $_SERVER['HTTP_X_ADMIN_CODE'] ?? '';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// --- HILFSFUNKTIONEN ---

/**
 * Prüft, ob der im Header gesendete Code mit dem System-Code übereinstimmt
 */
function verifyAdmin() {
    global $admin_code, $request_admin_code;
    if ($request_admin_code !== $admin_code) {
        http_response_code(403);
        echo json_encode(['error' => 'Nicht autorisierter Zugriff. Admin-Code ungültig.']);
        exit;
    }
}

/**
 * Konvertiert leere Strings oder 'null'-Strings in echte SQL NULL-Werte
 */
function nullIfEmpty($val) {
    return ($val === '' || $val === 'null' || $val === null) ? null : $val;
}

/**
 * Stellt sicher, dass alle notwendigen Tabellen existieren (Auto-Migration)
 */
function syncDatabaseStructure($pdo) {
    try {
        // Tabelle: songs
        $pdo->exec("CREATE TABLE IF NOT EXISTS `songs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `artist` varchar(255) DEFAULT 'TheStealth',
            `description` text DEFAULT NULL,
            `is_out_now` tinyint(1) DEFAULT 0,
            `release_date` date DEFAULT NULL,
            `cover_url` varchar(255) DEFAULT NULL,
            `mp3_url` varchar(255) DEFAULT NULL,
            `album` varchar(255) DEFAULT NULL,
            `rating` decimal(3,2) DEFAULT 0.00,
            `rating_count` int(11) DEFAULT 0,
            `spotify_url` varchar(255) DEFAULT NULL,
            `apple_music_url` varchar(255) DEFAULT NULL,
            `deezer_url` varchar(255) DEFAULT NULL,
            `soundcloud_url` varchar(255) DEFAULT NULL,
            `youtube_url` varchar(255) DEFAULT NULL,
            `amazon_music_url` varchar(255) DEFAULT NULL,
            `spotify_artist_url` varchar(255) DEFAULT NULL,
            `deezer_presave_url` varchar(255) DEFAULT NULL,
            `facebook_url` varchar(255) DEFAULT NULL,
            `instagram_url` varchar(255) DEFAULT NULL,
            `tiktok_url` varchar(255) DEFAULT NULL,
            `isrc_code` varchar(50) DEFAULT NULL,
            `spotify_uri` varchar(100) DEFAULT NULL,
            `created_at` timestamp DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Tabelle: settings
        $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
            `setting_key` varchar(50) NOT NULL,
            `setting_value` text DEFAULT NULL,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Tabelle: media
        $pdo->exec("CREATE TABLE IF NOT EXISTS `media` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `file_name` varchar(255) NOT NULL,
            `file_path` varchar(255) NOT NULL,
            `file_type` varchar(50) DEFAULT NULL,
            `uploaded_at` timestamp DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Tabelle: newsletter
        $pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `preference` varchar(50) DEFAULT 'all',
            `created_at` timestamp DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Tabelle: visits (Unique Visitors)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `visits` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(255) NOT NULL,
            `last_visit` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `identifier` (`identifier`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Tabelle: visit_logs (Alle Klicks/Aufrufe)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `visit_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(255) NOT NULL,
            `visited_at` timestamp DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    } catch (Exception $e) {
        error_log("DB Sync Error: " . $e->getMessage());
    }
}

// --- API HAUPTLOGIK ---

try {
    syncDatabaseStructure($pdo);

    // 1. Alle Songs abrufen
    if ($action === 'getSongs') {
        $stmt = $pdo->query("SELECT * FROM songs ORDER BY release_date DESC");
        echo json_encode($stmt->fetchAll());
    }

    // 2. Diskografie-Jahre für Filter abrufen
    elseif ($action === 'getDiscography') {
        $stmt = $pdo->query("SELECT DISTINCT YEAR(release_date) as year FROM songs WHERE release_date IS NOT NULL ORDER BY year DESC");
        echo json_encode($stmt->fetchAll());
    }

    // 3. Songs eines spezifischen Albums abrufen
    elseif ($action === 'getAlbum') {
        $stmt = $pdo->prepare("SELECT * FROM songs WHERE album = ? ORDER BY release_date DESC");
        $stmt->execute([$_GET['name']]);
        echo json_encode($stmt->fetchAll());
    }

    // 4. Einzelnen Song über ID abrufen
    elseif ($action === 'getSong') {
        $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch());
    }

    // 5. Song speichern oder aktualisieren (Admin only)
    elseif ($action === 'saveSong' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        
        $params = [
            $data['title'], 
            $data['artist'] ?: 'TheStealth', 
            $data['description'], 
            (int)$data['is_out_now'], 
            nullIfEmpty($data['release_date']),
            nullIfEmpty($data['spotify_url']), 
            nullIfEmpty($data['apple_music_url']), 
            nullIfEmpty($data['deezer_url']),
            nullIfEmpty($data['soundcloud_url']), 
            nullIfEmpty($data['youtube_url']), 
            nullIfEmpty($data['amazon_music_url']),
            nullIfEmpty($data['spotify_artist_url']), 
            nullIfEmpty($data['deezer_presave_url']),
            nullIfEmpty($data['facebook_url']), 
            nullIfEmpty($data['instagram_url']), 
            nullIfEmpty($data['tiktok_url']),
            $data['cover_url'], 
            $data['mp3_url'], 
            nullIfEmpty($data['album']), 
            nullIfEmpty($data['isrc_code']), 
            nullIfEmpty($data['spotify_uri'])
        ];

        if (isset($data['id']) && $data['id']) {
            $sql = "UPDATE songs SET title=?, artist=?, description=?, is_out_now=?, release_date=?, 
                    spotify_url=?, apple_music_url=?, deezer_url=?, soundcloud_url=?, youtube_url=?, amazon_music_url=?, 
                    spotify_artist_url=?, deezer_presave_url=?, facebook_url=?, instagram_url=?, tiktok_url=?, 
                    cover_url=?, mp3_url=?, album=?, isrc_code=?, spotify_uri=? WHERE id=?";
            $params[] = $data['id'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "INSERT INTO songs (title, artist, description, is_out_now, release_date, 
                    spotify_url, apple_music_url, deezer_url, soundcloud_url, youtube_url, amazon_music_url, 
                    spotify_artist_url, deezer_presave_url, facebook_url, instagram_url, tiktok_url, 
                    cover_url, mp3_url, album, isrc_code, spotify_uri) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        echo json_encode(['success' => true]);
    }

    // 6. Song löschen (Admin only)
    elseif ($action === 'deleteSong' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("DELETE FROM songs WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
    }

    // 7. Media-Management: Abrufen
    elseif ($action === 'getMedia') {
        $stmt = $pdo->query("SELECT * FROM media ORDER BY uploaded_at DESC");
        echo json_encode($stmt->fetchAll());
    }

    // 8. Media-Management: Upload (Admin only)
    elseif ($action === 'uploadMedia' && $method === 'POST') {
        verifyAdmin();
        if (!isset($_FILES['file'])) throw new Exception("Keine Datei übertragen.");
        
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES['file']['name']);
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO media (file_name, file_path, file_type) VALUES (?, ?, ?)");
            $stmt->execute([$_FILES['file']['name'], $target, $_FILES['file']['type']]);
            echo json_encode(['success' => true, 'url' => $target]);
        } else throw new Exception("Datei-Upload fehlgeschlagen.");
    }

    // 9. Media-Management: Löschen (Admin only)
    elseif ($action === 'deleteMedia' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("SELECT file_path FROM media WHERE id = ?");
        $stmt->execute([$data['id']]);
        $file = $stmt->fetch();
        if ($file && file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
    }

    // 10. Statistiken abrufen (Admin only)
    elseif ($action === 'getStats') {
        $stats = [
            'total_visits' => $pdo->query("SELECT COUNT(*) FROM visits")->fetchColumn() ?: 0,
            'unique_visitors' => $pdo->query("SELECT COUNT(DISTINCT identifier) FROM visits")->fetchColumn() ?: 0,
            'total_songs' => $pdo->query("SELECT COUNT(*) FROM songs")->fetchColumn() ?: 0,
            'media_count' => $pdo->query("SELECT COUNT(*) FROM media")->fetchColumn() ?: 0,
            'subscribers' => $pdo->query("SELECT COUNT(*) FROM newsletter")->fetchColumn() ?: 0,
            'logs_count' => $pdo->query("SELECT COUNT(*) FROM visit_logs")->fetchColumn() ?: 0
        ];
        echo json_encode($stats);
    }

    // 11. Newsletter versenden (Admin only)
    elseif ($action === 'sendNewsletter' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $subject = $data['subject'];
        $body = $data['body'];
        
        $stmt = $pdo->query("SELECT email FROM newsletter");
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $count = 0;
        $headers = "From: TheStealth Music <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $html = "<div style='background:#020617;color:#f8fafc;padding:40px;font-family:sans-serif;'>
                 <h1 style='color:#93d507;text-transform:uppercase;'>News Update</h1>
                 <div style='font-size:16px;line-height:1.6;'>".nl2br(htmlspecialchars($body))."</div>
                 <p style='margin-top:40px;font-size:10px;color:#64748b;'>&copy; TheStealth Music - Du kannst dich jederzeit abmelden.</p>
                 </div>";

        foreach ($emails as $email) {
            if (mail($email, $subject, $html, $headers)) {
                $count++;
            }
        }
        echo json_encode(['success' => true, 'sent_count' => $count]);
    }

    // 12. Rating durch Nutzer aktualisieren
// Sterne-Rating (AKZEPTIERT 'score' ODER 'rating')
    elseif (($action === 'rate' || $action === 'updateRating') && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $songId = (int)($data['id'] ?? 0);
        // Unterstütze beide Keys: 'score' (Standard) oder 'rating' (User-Snippet)
        $val = isset($data['score']) ? floatval($data['score']) : (isset($data['rating']) ? floatval($data['rating']) : 0);

        if ($songId > 0 && $val >= 1 && $val <= 5) {
            $stmt = $pdo->prepare("
                UPDATE songs 
                SET rating = (COALESCE(rating, 0) * COALESCE(rating_count, 0) + :val) / (COALESCE(rating_count, 0) + 1),
                    rating_count = COALESCE(rating_count, 0) + 1 
                WHERE id = :id
            ");
            $stmt->execute(['val' => $val, 'id' => $songId]);
            
            $stmt = $pdo->prepare("SELECT rating, rating_count FROM songs WHERE id = ?");
            $stmt->execute([$songId]);
            echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        } else {
            throw new Exception("Rating-Daten ungültig. Empfangen: ID $songId, Wert $val");
        }
    }

    // 13. SQL Konsole (Admin only)
    elseif ($action === 'runSql' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        $query = trim($data['query']);
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        if (stripos($query, 'SELECT') === 0 || stripos($query, 'SHOW') === 0) {
            echo json_encode(['success' => true, 'result' => $stmt->fetchAll()]);
        } else {
            echo json_encode(['success' => true, 'result' => 'Erfolgreich. Zeilen betroffen: ' . $stmt->rowCount()]);
        }
    }

    // 14. System-Dateien patchen (Admin only)
    elseif ($action === 'updateSystemFile' && $method === 'POST') {
        verifyAdmin();
        if (isset($_FILES['file']) && !empty($_POST['target'])) {
            $allowed = ['api.php', 'index.php', 'header.php', 'footer.php', 'admin.php', 'admin_views.php', 'admin_components.php', 'views.php', 'components.php'];
            $target = $_POST['target'];
            
            if (in_array($target, $allowed)) {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    echo json_encode(['success' => true]);
                } else throw new Exception("Schreibfehler auf dem Server.");
            } else throw new Exception("Datei-Ziel nicht erlaubt.");
        }
    }

    // 15. Besucher-Tracking (Frontend Action)
    elseif ($action === 'trackVisit' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $identifier = $data['identifier'] ?? 'anonymous';
        
        // JEDEN Aufruf loggen
        $stmt = $pdo->prepare("INSERT INTO visit_logs (identifier) VALUES (?)");
        $stmt->execute([$identifier]);

        // Einmal pro Session für Unique Tracking
        if (!isset($_SESSION['visited'])) {
            $stmt = $pdo->prepare("INSERT INTO visits (identifier) VALUES (?) ON DUPLICATE KEY UPDATE last_visit = CURRENT_TIMESTAMP");
            $stmt->execute([$identifier]);
            $_SESSION['visited'] = true;
        }
        echo json_encode(['success' => true]);
    }

    // 16. Newsletter-Anmeldung (Frontend Action)
    elseif ($action === 'subscribe' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) throw new Exception("Ungültige E-Mail Adresse.");
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter (email, preference) VALUES (?, ?)");
        $stmt->execute([$email, $data['preference'] ?? 'all']);
        echo json_encode(['success' => true]);
    }

    // 17. Einstellungen (Settings) abrufen & speichern
    elseif ($action === 'getSettings') {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        echo json_encode($stmt->fetchAll(PDO::FETCH_KEY_PAIR));
    }

    elseif ($action === 'saveSettings' && $method === 'POST') {
        verifyAdmin();
        $data = json_decode(file_get_contents('php://input'), true);
        foreach ($data as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        echo json_encode(['success' => true]);
    }

    else {
        http_response_code(404);
        echo json_encode(['error' => 'Aktion unbekannt: ' . $action]);
    }

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

?>