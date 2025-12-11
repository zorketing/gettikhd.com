<?php
$is_page = true;
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
                Why did the download fail?
                <span>▼</span>
            </button>
            <div class="accordion-body">
                Common reasons include: private videos, deleted videos, or unstable internet connection. If the issue persists, try again in a few minutes.
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
