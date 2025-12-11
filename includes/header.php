<?php
// Determine base path for assets
$base_path = isset($is_page) && $is_page ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Video Downloader - No Watermark</title>
    <meta name="description" content="Download TikTok videos without watermark for free. Fast, HD quality, and unlimited downloads.">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/style.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header / Navigation -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?php echo $base_path; ?>index.php">TikTokDL</a>
        </div>
        <div class="nav-links">
            <a href="<?php echo $base_path; ?>index.php">Home</a>
            <a href="<?php echo $base_path; ?>pages/how-to.php">How to</a>
            <a href="<?php echo $base_path; ?>pages/faq.php">FAQ</a>
        </div>
        <div class="theme-toggle-container" style="display: flex; align-items: center; gap: 15px;">
            <button id="themeToggle" class="theme-btn" aria-label="Toggle Dark Mode">
                <span class="icon">🌙</span>
            </button>
            <button class="hamburger" id="hamburger" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <main class="main-content">
