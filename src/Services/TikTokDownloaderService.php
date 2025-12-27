<?php
namespace App\Services;

/**
 * Service class for handling TikTok metadata fetching and normalization.
 */
class TikTokDownloaderService {
    private $providers;
    private $cacheDir;
    private $logFile;

    public function __construct(array $providers, string $cacheDir, string $logFile) {
        $this->providers = $providers;
        $this->cacheDir = $cacheDir;
        $this->logFile = $logFile;

        if (!is_dir($this->cacheDir)) mkdir($this->cacheDir, 0755, true);
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    }

    public function fetchMetadata(string $url) {
        if (!$this->isValidTikTokUrl($url)) {
            return ['code' => -1, 'msg' => 'Invalid TikTok URL. Please paste a valid link.'];
        }

        $cacheKey = md5($url);
        $cacheFile = $this->cacheDir . $cacheKey . '.json';

        // Check Cache
        if (file_exists($cacheFile)) {
            $fileAge = time() - filemtime($cacheFile);
            if ($fileAge < 86400) { // 24h
                return json_decode(file_get_contents($cacheFile), true);
            }
            @unlink($cacheFile);
        }

        $lastError = '';
        foreach ($this->providers as $name => $endpoint) {
            $response = $this->fetchFromProvider($name, $endpoint, $url);
            if ($response && isset($response['code']) && $response['code'] === 0) {
                file_put_contents($cacheFile, json_encode($response));
                return $response;
            }
            $lastError = $response['msg'] ?? 'Unknown Error';
            $this->logError($name, $endpoint, $lastError);
        }

        return ['code' => -1, 'msg' => 'All providers failed. Last error: ' . $lastError];
    }

    private function fetchFromProvider($name, $endpoint, $videoUrl) {
        $ch = curl_init();
        $headers = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'];
        $postFields = [];

        switch ($name) {
            case 'tikwm': $postFields = ['url' => $videoUrl, 'web' => 1, 'hd' => 1]; break;
            case 'lovetik': $postFields = ['query' => $videoUrl]; break;
            case 'ssstik': $postFields = ['url' => $videoUrl]; break;
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postFields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode >= 400) return ['code' => -1, 'msg' => "HTTP $httpCode / Curl: $curlErr"];
        $json = json_decode($raw, true);
        return $json ? $this->normalizeResponse($name, $json) : ['code' => -1, 'msg' => 'Invalid JSON'];
    }

    private function normalizeResponse($provider, $data) {
        $normalized = ['code' => 0, 'data' => []];
        try {
            if ($provider === 'tikwm') {
                if (!isset($data['data'])) return ['code' => -1, 'msg' => 'No Data'];
                $d = $data['data'];
                $normalized['data'] = [
                    'title' => $d['title'] ?? '',
                    'cover' => $d['cover'] ?? '',
                    'play' => $d['play'] ?? '',
                    'hdplay' => $d['hdplay'] ?? '',
                    'music' => $d['music'] ?? '',
                    'size' => $d['size'] ?? 0,
                    'hd_size' => $d['hd_size'] ?? 0,
                    'author' => [
                        'nickname' => $d['author']['nickname'] ?? '',
                        'avatar' => $d['author']['avatar'] ?? '',
                        'unique_id' => $d['author']['unique_id'] ?? ''
                    ]
                ];
                return $normalized;
            }
            // Add other providers normalization here...
        } catch (\Exception $e) {
            return ['code' => -1, 'msg' => 'Normalization Error'];
        }
        return ['code' => -1, 'msg' => 'Provider Unknown'];
    }

    public function isValidTikTokUrl(string $url): bool {
        $pattern = '/^https?:\/\/(www\.|vm\.|vt\.|v\.)?tiktok\.com\/.*$/i';
        return preg_match($pattern, $url) === 1;
    }

    private function logError($provider, $url, $msg) {
        $entry = "[" . date('Y-m-d H:i:s') . "] [$provider] [$url] Error: $msg" . PHP_EOL;
        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }
}
