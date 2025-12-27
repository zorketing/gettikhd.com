<?php
/**
 * GetTikHD Web - Central Configuration
 */

// Site Info
define('SITE_NAME', 'TikTokDL');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");

// Paths
define('ROOT_DIR', dirname(__DIR__));
define('CACHE_DIR', ROOT_DIR . '/api/cache/');
define('LOG_DIR', ROOT_DIR . '/api/logs/');
define('DB_PATH', ROOT_DIR . '/admin/data.db');

// Cache Settings
define('CACHE_TTL', 86400); // 24 Hours

// Download Providers
const DOWNLOAD_PROVIDERS = [
    'tikwm' => 'https://tikwm.com/api/',
    'lovetik' => 'https://lovetik.com/api/video',
    'ssstik' => 'https://api.ssstik.io/quote'
];

// Streaming Allowed Domains (SSRF Protection)
const ALLOWED_STREAM_DOMAINS = [
    'tiktokcdn.com',
    'tiktokcdn-us.com',
    'tiktok.com',
    'musical.ly',
    'muscdn.com',
    'byteoversea.com',
    'ibytedtos.com',
    'tiktokv.com',
    'tikwm.com'
];

// Admin Credentials (Should ideally be in ENV, but kept here for now as per project context)
define('ADMIN_USER', 'Jacob');
define('ADMIN_PASS_HASH', '$2y$10$3L2KpHkqSPhm4Ibttnd6zeo1NFzWKh2jLCsI.DpYJnd34MMqHHKaK'); 

/**
 * Global Helper Functions
 */

if (!function_exists('h')) {
    function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Database Helper
 */
function getDatabaseConnection() {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

/**
 * PSR-4 Style Autoloader Placeholder (Simple version)
 */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = ROOT_DIR . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
