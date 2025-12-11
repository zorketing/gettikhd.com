<?php
$is_page = true;
require_once '../includes/header.php';
?>

<div class="container">
    <h1>Contact Us</h1>
    <p class="subtitle">Have a question or suggestion?</p>

    <form id="contactForm">
        <div class="input-group">
            <input type="text" id="c_name" name="name" placeholder="Your Name" required>
        </div>
        <div class="input-group">
            <input type="email" id="c_email" name="email" placeholder="Your Email" required>
        </div>
        <div class="input-group">
            <textarea id="c_message" name="message" placeholder="Your Message" required></textarea>
        </div>
        
        <button type="submit" class="submit-btn" id="c_submit">Send Message</button>
    </form>
    <p id="c_status" style="margin-top: 15px; display: none;"></p>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('c_submit');
    const status = document.getElementById('c_status');
    const formData = {
        name: document.getElementById('c_name').value,
        email: document.getElementById('c_email').value,
        message: document.getElementById('c_message').value
    };

    btn.disabled = true;
    btn.textContent = 'Sending...';
    status.style.display = 'none';

    try {
        const res = await fetch('../api/submit_contact.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const data = await res.json();

        if(data.status === 'success') {
            status.style.color = '#00b894';
            status.textContent = "Thank you! We'll get back to you soon.";
            document.getElementById('contactForm').reset();
        } else {
            status.style.color = '#ff7675';
            status.textContent = 'Error: ' + data.message;
        }
    } catch(err) {
        status.style.color = '#ff7675';
        status.textContent = 'Failed to connect to server.';
    } finally {
        status.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Send Message';
    }
});
</script>
</div>

<?php require_once '../includes/footer.php'; ?>
