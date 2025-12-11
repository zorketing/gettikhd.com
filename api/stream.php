<?php
// stream.php - Optimized Download Method (Refined)

// Disable errors to prevent binary corruption
ini_set('display_errors', 0);
error_reporting(0);
set_time_limit(0);

// Security
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Get Parameters
$videoUrl = isset($_GET['url']) ? urldecode($_GET['url']) : ''; // Decode as requested
$filename = $_GET['name'] ?? 'video.mp4';

if (empty($videoUrl)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL is required']);
    exit;
}

// Sanitize Filename
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
if (strlen($filename) > 100) {
    $filename = substr($filename, 0, 100);
}
if (!preg_match('/\.(mp4|mp3|webm)$/', $filename)) {
    $filename .= '.mp4';
}

function download_video($videoUrl, $filename) {
    $ch = curl_init();
    
    // Cookie Jar
    $cookieFile = sys_get_temp_dir() . '/tiktok_cookies.txt';

    curl_setopt_array($ch, [
        CURLOPT_URL => $videoUrl,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_REFERER => 'https://www.tiktok.com/',
        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
            'Origin: https://www.tiktok.com',
            'Sec-Fetch-Dest: video',
            'Sec-Fetch-Mode: no-cors',
            'Sec-Fetch-Site: cross-site',
            'Range: bytes=0-',
        ],
        // ENCODING: Handle gzip/deflate automatically
        CURLOPT_ENCODING => '', 
        
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 0,
        
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        
        CURLOPT_RETURNTRANSFER => false,
        // CURLOPT_FILE => STDOUT, // Removed to rely entirely on WRITEFUNCTION
    ]);
    
    // State to track if we've committed to a file download
    $downloadStarted = false;
    $headersBuffer = []; // Store upstream headers temporarily

    // HEADER FUNCTION
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$downloadStarted, &$headersBuffer, $filename) {
        $len = strlen($header);
        $trimHeader = trim($header);
        
        if (empty($trimHeader)) return $len;
        
        // Capture relevant headers
        if (stripos($trimHeader, 'Content-Type:') === 0) {
            $headersBuffer['Content-Type'] = $trimHeader;
        }
        if (stripos($trimHeader, 'Content-Length:') === 0) {
            $headersBuffer['Content-Length'] = $trimHeader;
        }

        return $len;
    });

    // WRITE FUNCTION
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) use (&$downloadStarted, &$headersBuffer, $filename) {
        // This function is called when body data arrives.
        // It implies headers have been processed for this chunk.
        
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // If this is the FIRST chunk of body data
        if (!$downloadStarted) {
            if ($httpCode >= 200 && $httpCode < 300) {
                // SUCCESS: Send Download Headers
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Pragma: public');
                header('Cache-Control: must-revalidate');
                
                // Pass through upstream headers
                if (isset($headersBuffer['Content-Type'])) header($headersBuffer['Content-Type']);
                if (isset($headersBuffer['Content-Length'])) header($headersBuffer['Content-Length']);
                
                $downloadStarted = true;
            } else {
                // FAILURE: Do not send file headers. 
                // We will let curl finish (or return) and handle error json at the end, 
                // OR we can output the error body if it's small? 
                // For now, let's capture it? No, writefunction must write or return len.
                // We'll just ignore output for now if error, to prevent corrupt file download?
                // Actually, if we error, we want to return JSON. 
                // But we can't buffer infinite video.
                return strlen($data); 
            }
        }

        if ($downloadStarted) {
            echo $data;
            if (ob_get_length()) ob_flush();
            flush();
        }
        
        return strlen($data);
    });
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    
    curl_close($ch);
    
    // If we never started downloading (e.g. 404 error), send JSON error
    if (!$downloadStarted) {
        http_response_code($httpCode >= 400 ? $httpCode : 500);
        // Ensure strictly JSON
        header('Content-Type: application/json');
        echo json_encode([
            'error' => "Remote Server Error: $httpCode",
            'curl_error' => $curlErr,
            'url' => $videoUrl
        ]);
    }
    
    exit;
}

try {
    download_video($videoUrl, $filename);
} catch (Exception $e) {
    if (!headers_sent()) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
