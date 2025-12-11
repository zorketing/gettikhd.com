<?php
// admin/login.php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf_token)) {
        $error = "Invalid CSRF Token";
    } elseif ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TikTok Downloader</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-login-container">
        <div class="container" style="max-width: 400px; padding: 3rem 2rem;">
            <h1>Admin Login</h1>
            
            <?php if ($error): ?>
                <div class="error-msg" style="display: block; margin-bottom: 20px;">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                
                <div class="input-group-lg" style="gap: 1.5rem;">
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="Username" required autofocus>
                    </div>
                    
                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="Password" required 
                               style="width: 100%; height: 60px; padding: 0 1.5rem; border-radius: 20px; border: none; background: var(--bg-color); box-shadow: inset 6px 6px 10px var(--shadow-dark), inset -6px -6px 10px var(--shadow-light); color: var(--text-primary); font-size: 1.1rem; outline: none;">
                    </div>

                    <button type="submit" class="download-btn-lg" style="margin-top: 10px;">Login</button>
                </div>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="../index.php" class="btn-link">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
