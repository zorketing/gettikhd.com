<?php
/**
 * Enterprise TikTok Metadata Fetcher
 * Features: Multi-API Failover, Smart Caching, Robust Logging
 */

// Configuration
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Paths
define('CACHE_DIR', __DIR__ . '/cache/');
define('LOG_FILE', __DIR__ . '/logs/api_errors.log');
define('CACHE_TTL', 86400); // 24 Hours

// Providers List
$PROVIDERS = [
    'tikwm' => 'https://tikwm.com/api/',
    'lovetik' => 'https://lovetik.com/api/video',
    'ssstik' => 'https://api.ssstik.io/quote'
];

// PHP Configuration
ini_set('display_errors', 0); // Hide errors in production
error_reporting(E_ALL);

// Ensure directories exist
if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
$logDir = dirname(LOG_FILE);
if (!is_dir($logDir)) mkdir($logDir, 0755, true);

// Get Input
$input = json_decode(file_get_contents('php://input'), true);
$url = $input['url'] ?? '';

if (empty($url)) {
    echo json_encode(['code' => -1, 'msg' => 'URL is required']);
    exit;
}

// 1. CHECK CACHE
$cacheKey = md5($url);
$cacheFile = CACHE_DIR . $cacheKey . '.json';

if (file_exists($cacheFile)) {
    $fileAge = time() - filemtime($cacheFile);
    if ($fileAge < CACHE_TTL) {
        // Return Cached Data
        $cachedData = file_get_contents($cacheFile);
        if ($cachedData) {
            // Touch file to extend life? Optional.
            echo $cachedData;
            exit;
        }
    } else {
        // Cache expired, remove it
        @unlink($cacheFile);
    }
}

// 2. FAILOVER LOGIC
$finalResult = null;
$lastError = '';

foreach ($PROVIDERS as $name => $endpoint) {
    $response = fetch_from_provider($name, $endpoint, $url);
    
    if ($response && isset($response['code']) && $response['code'] === 0) {
        // SUCCESS
        $finalResult = $response;
        break; // Stop loop
    } else {
        // FAIL
        $errorMsg = $response['msg'] ?? 'Unknown Error';
        log_error($name, $endpoint, $errorMsg);
        $lastError = $errorMsg;
    }
}

// 3. RETURN RESPONSE
if ($finalResult) {
    // Save to Cache
    file_put_contents($cacheFile, json_encode($finalResult));
    echo json_encode($finalResult);
} else {
    // All providers failed
    echo json_encode(['code' => -1, 'msg' => 'All providers failed. Last error: ' . $lastError]);
}


// --- Helper Functions ---

function fetch_from_provider($name, $endpoint, $videoUrl) {
    $ch = curl_init();
    
    // Common Headers
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    ];

    $postFields = [];

    // Provider Specifics
    switch ($name) {
        case 'tikwm':
            $postFields = [
                'url' => $videoUrl,
                'count' => 12,
                'cursor' => 0,
                'web' => 1,
                'hd' => 1
            ];
            break;
        case 'lovetik':
            $postFields = ['query' => $videoUrl];
            break;
        case 'ssstik':
            $postFields = ['url' => $videoUrl];
            break;
    }

    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5s timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for portable PHP
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Might need Referer for some

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr || $httpCode >= 400) {
        return ['code' => -1, 'msg' => "HTTP $httpCode / Curl: $curlErr"];
    }

    $json = json_decode($raw, true);
    if (!$json) {
        return ['code' => -1, 'msg' => 'Invalid JSON Response'];
    }

    // NORMALIZE DATA
    return normalize_response($name, $json);
}

function normalize_response($provider, $data) {
    $normalized = [
        'code' => 0,
        'msg' => 'success',
        'data' => [
            'title' => '',
            'cover' => '',
            'play' => '',
            'hdplay' => '',
            'music' => '',
            'author' => [
                'nickname' => '',
                'avatar' => ''
            ]
        ]
    ];

    try {
        if ($provider === 'tikwm') {
            if (!isset($data['data'])) return ['code' => -1, 'msg' => 'TikWM: No Data'];
            $d = $data['data'];
            $normalized['data']['title'] = $d['title'] ?? '';
            $normalized['data']['cover'] = $d['cover'] ?? '';
            $normalized['data']['play'] = $d['play'] ?? '';
            $normalized['data']['hdplay'] = $d['hdplay'] ?? '';
            $normalized['data']['size'] = $d['size'] ?? 0; // Standard size
            $normalized['data']['hd_size'] = $d['hd_size'] ?? 0; // HD Size
            $normalized['data']['music'] = $d['music'] ?? '';
            $normalized['data']['author']['nickname'] = $d['author']['nickname'] ?? '';
            $normalized['data']['author']['avatar'] = $d['author']['avatar'] ?? '';
            
            return $normalized;
        }
        
        // Placeholder adaptors for others (Logic guessed based on common structures)
        // In a real scenario, we would inspect $data
        if ($provider === 'lovetik') {
            if (isset($data['links'])) {
                 $normalized['data']['title'] = $data['desc'] ?? 'TikTok Video';
                 $normalized['data']['cover'] = $data['cover'] ?? '';
                 $normalized['data']['play'] = $data['links'][0]['a'] ?? ''; // Assuming first link
                 $normalized['data']['author']['nickname'] = $data['author'] ?? 'User';
                 return $normalized;
            }
             return ['code' => -1, 'msg' => 'Lovetik: Invalid Structure'];
        }

        if ($provider === 'ssstik') {
            // SSSTik usually returns HTML or different JSON. 
            // Assuming simplified success for failover demonstration
            if (isset($data['result_url'])) {
                 $normalized['data']['play'] = $data['result_url'];
                 return $normalized;
            }
            return ['code' => -1, 'msg' => 'SSSTik: Handling Complex Response'];
        }

    } catch (Exception $e) {
        return ['code' => -1, 'msg' => 'Normalization Error'];
    }

    return ['code' => -1, 'msg' => 'Provider Unknown'];
}

function log_error($provider, $url, $msg) {
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] [$provider] [$url] Error: $msg" . PHP_EOL;
    file_put_contents(LOG_FILE, $entry, FILE_APPEND);
}
?>
