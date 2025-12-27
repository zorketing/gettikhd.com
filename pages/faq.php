<?php
$is_page = true;
$page_title = 'FAQ - Questions about TikTok Video Downloader';
$page_desc = 'Find answers to common questions about saving TikTok videos without watermark, MP3 conversion, and legal usage.';
require_once '../includes/header.php';
?>

<div class="container">
    <h1>Frequently Asked Questions</h1>
    <p class="subtitle">Common questions about TikTokDL</p>

    <div class="accordion">
        <div class="accordion-item">
            <button class="accordion-header">
                Is this tool free?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Yes, TikTokDL is 100% free to use. You can download as many videos as you want without any hidden costs.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-header">
                Does it remove watermarks?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Absolutely! Our tool specifically targets the source stream to provide you with the clean, high-definition video without the TikTok logo.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-header">
                Is it legal to download videos?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                It is generally legal to download videos for personal use. However, you should respect the copyright of the content creators and not redistribute their work without permission.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-header">
                Can I download TikTok MP3?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Yes! Once you paste the link, our tool provides an option to "Download Audio MP3" which extracts the music in high quality.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-header">
                Does this work on mobile (Android/iOS)?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Absolutely. TikTokDL is mobile-first. You only need a mobile browser to save videos directly to your gallery or files.
            </div>
        </div>

        <div class="accordion-item">
            <button class="accordion-header">
                Why did the download fail?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Downloads usually fail if the video is private, has been deleted, or if your internet connection is unstable. If the issue persists, try refreshing the page and pasting the link again.
            </div>
        </div>
    </div>
</div>

<script>
// Simple Accordion Logic
document.querySelectorAll('.accordion-header').forEach(button => {
    button.addEventListener('click', () => {
        const item = button.parentElement;
        item.classList.toggle('active');
        const icon = button.querySelector('span');
        icon.style.transform = item.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
