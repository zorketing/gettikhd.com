<?php
/**
 * TikTok Metadata API - Service Refactor
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Methods: POST');

use App\Services\TikTokDownloaderService;

// Get Input
$input = json_decode(file_get_contents('php://input'), true);
$url = $input['url'] ?? '';

if (empty($url)) {
    echo json_encode(['code' => -1, 'msg' => 'URL is required']);
    exit;
}

// Initialize Service
$downloader = new TikTokDownloaderService(
    DOWNLOAD_PROVIDERS,
    CACHE_DIR,
    LOG_DIR . 'api_errors.log'
);

// Fetch Result
$result = $downloader->fetchMetadata($url);

// Output
echo json_encode($result);
?>
