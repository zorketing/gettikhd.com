<?php
$is_page = false;
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        
        <div class="hero-content">
            <h1 class="hero-title">TikTok Video Downloader <br><span class="highlight">Without Watermark</span></h1>
            <p class="hero-subtitle">Save TikTok videos and MP3 music in HD quality. Fast, free, and unlimited downloads for mobile and PC.</p>

            <div class="downloader-card">
                <div class="input-group-lg">
                    <div class="input-wrapper">
                        <input type="text" id="urlInput" placeholder="Paste link here..." aria-label="TikTok URL">
                        <button id="pasteBtn" class="paste-btn" aria-label="Paste Link">
                            📋
                        </button>
                    </div>
                    <button id="downloadBtn" class="download-btn-lg">
                        <span class="btn-text">Download</span>
                        <div class="loader"></div>
                    </button>
                </div>

                <!-- Progress & Error (Hidden initially) -->

                <p id="errorMsg" class="error-msg"></p>
            </div>
        </div>

        <div class="hero-visual">
            <!-- Animated CSS Illustration (TikTok style circles) -->
            <!-- Animated CSS Illustration -->
            <div class="floating-shapes">
                <!-- Main Circle: TikTok Logo -->
                <div class="shape shape-1">
                    <svg class="tiktok-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="currentColor" d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                    </svg>
                </div>
                
                <!-- Secondary Circle: Download Arrow -->
                <div class="shape shape-2">
                    <svg class="download-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Result Section (Separated from hero for cleaner flow) -->
<section id="result" class="result-section">
    <!-- Results injected here -->
</section>

<!-- Features Grid -->
<section class="features-section">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🚫</div>
            <h3>No Watermark</h3>
            <p>Get clean videos without the TikTok logo or username.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Unlimited</h3>
            <p>Download as many videos as you want, totally free.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎧</div>
            <h3>MP4 & MP3</h3>
            <p>Save videos in HD or extract just the audio/music.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>