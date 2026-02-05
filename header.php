<?php 
/**
 * header.php - Kopfzeile & SEO-Management
 * Lädt globale Einstellungen und definiert die Meta-Tags für Google & Social Media.
 */

// HTTPS erzwingen und Encoding setzen
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}
ini_set('default_charset', 'utf-8');
header('Content-Type: text/html; charset=utf-8'); 
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once 'db_config.php';
$admin_env_code = getenv('ADMIN_CODE') ?: '1234';

// --- EINSTELLUNGEN LADEN ---
$seo_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $seo_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // Fallback falls DB Fehler
}

// Basis-URL ermitteln (WICHTIG für WhatsApp Bilder)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $domainName . '/';

// Variablen vorbereiten
$s_artist = htmlspecialchars($seo_settings['artist_name'] ?? 'TheStealth Music');
// Wir nutzen die SEO-Beschreibung falls vorhanden, sonst Fallback auf About-Text
$s_desc_raw = $seo_settings['seo_description'] ?? ($seo_settings['about_text'] ?? 'Offizielle Musikseite. Neue Tracks, Merch und exklusive Inhalte.');
$s_desc = htmlspecialchars(mb_strimwidth(strip_tags($s_desc_raw), 0, 160, "..."));

// --- BILD FÜR WHATSAPP / FACEBOOK ---
$static_image_name = 'home_preview.jpg';
$s_image = '';

// Priorität 1: Sharing-Banner aus den SEO-Settings (Variablen-Fix: $seo_settings statt $settings)
if (!empty($seo_settings['og_image_url'])) {
    $s_image_rel = $seo_settings['og_image_url'];
    $s_image = (strpos($s_image_rel, 'http') === 0) ? $s_image_rel : $baseUrl . ltrim($s_image_rel, '/');
} 
// Priorität 2: Statisches Bild auf dem Server
elseif (file_exists(__DIR__ . '/' . $static_image_name)) {
    $s_image = $baseUrl . $static_image_name;
} 
// Priorität 3: Profilbild
elseif (!empty($seo_settings['about_photo_url'])) {
    $s_image_rel = $seo_settings['about_photo_url'];
    $s_image = (strpos($s_image_rel, 'http') === 0) ? $s_image_rel : $baseUrl . ltrim($s_image_rel, '/');
}

$theme_color = $seo_settings['main_color'] ?? '#020617';
$accent_color = $seo_settings['accent_color'] ?? '#93d507';
$hf_color = $seo_settings['header_footer_color'] ?? '#0a0f1d';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <!-- Dynamischer Titel & Favicon -->
    <title><?php echo $s_artist; ?></title>
    <link rel="icon" id="dynamic-favicon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==">

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $s_desc; ?>">
    <meta name="author" content="<?php echo $s_artist; ?>">
    <meta name="keywords" content="Music, Artist, Streaming, <?php echo $s_artist; ?>, Techno, Ambient">

    <!-- OPEN GRAPH (WICHTIG FÜR WHATSAPP) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $baseUrl; ?>">
    <meta property="og:title" content="<?php echo $s_artist; ?>">
    <meta property="og:description" content="<?php echo $s_desc; ?>">
    <?php if($s_image): ?>
    <meta property="og:image" content="<?php echo $s_image; ?>">
    <meta property="og:image:secure_url" content="<?php echo $s_image; ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <meta property="og:site_name" content="<?php echo $s_artist; ?>">

    <!-- TWITTER CARDS -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $s_artist; ?>">
    <meta name="twitter:description" content="<?php echo $s_desc; ?>">
    <?php if($s_image): ?>
    <meta name="twitter:image" content="<?php echo $s_image; ?>">
    <?php endif; ?>

    <!-- Libraries (React, Tailwind, Icons) -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Icons Library (Lucide) -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Oswald:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --accent: <?php echo $accent_color; ?>;
            --main-bg: <?php echo $theme_color; ?>;
            --header-footer: <?php echo $hf_color; ?>;
        }
        
        body { 
            background-color: var(--main-bg); 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: var(--accent); }
        
        .spinner {
            width: 24px; height: 24px;
            border: 3px solid rgba(255,255,255,0.1);
            border-radius: 50%; border-top-color: var(--accent);
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <script>
        // Server Variables passed to JS
        const SERVER_ADMIN_CODE = "<?php echo $admin_env_code; ?>";
        const { useState, useEffect, useCallback, useMemo, useRef } = React;
        
        // Tailwind Config
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: 'var(--accent)',
                        main: 'var(--main-bg)',
                        hf: 'var(--header-footer)'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Oswald', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[var(--main-bg)] text-slate-200 min-h-screen selection:bg-accent selection:text-white overflow-x-hidden">
    <div id="root"></div>

    <script type="text/babel">
        // --- GLOBAL ICONS & UI HELPERS ---
        const SocialIcons = {
            Instagram: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>,
            Facebook: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>,
            TikTok: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>,
            Youtube: () => <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon></svg>
        };

        const StarIcon = ({ filled, onClick, size = 18 }) => {
            return (
                <div onClick={onClick} className={`cursor-pointer transition-transform ${onClick ? 'hover:scale-110 active:scale-95' : ''}`}>
                    <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 24 24" fill={filled ? "currentColor" : "none"} stroke="currentColor" strokeWidth="2" className={filled ? "text-yellow-400" : "text-slate-700"}><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
            );
        };
        
        const StarPartial = ({ fillLevel, size = 18 }) => {
            return (
                <div className="relative inline-block">
                    <svg viewBox="0 0 24 24" width={size} height={size} fill="none" stroke="currentColor" strokeWidth="2" className="text-slate-700"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <div className="absolute inset-0 overflow-hidden pointer-events-none" style={{ width: `${Math.max(0, Math.min(100, fillLevel * 100))}%` }}>
                        <svg viewBox="0 0 24 24" width={size} height={size} fill="currentColor" stroke="currentColor" strokeWidth="2" className="text-yellow-400"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                </div>
            );
        };

        const StarRatingGroup = ({ rating, interactive = false, onRate, size = 18 }) => {
            const [hoverRating, setHoverRating] = useState(0);
            return (
                <div className="flex items-center gap-0.5 sm:gap-1" onMouseLeave={() => setHoverRating(0)}>
                    {[1, 2, 3, 4, 5].map(star => (
                        <StarIcon 
                            key={star} 
                            size={size}
                            filled={star <= (hoverRating || Math.round(rating))} 
                            onClick={interactive ? () => onRate(star) : undefined}
                        />
                    ))}
                </div>
            );
        };