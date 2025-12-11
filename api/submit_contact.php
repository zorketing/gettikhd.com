<?php
// api/submit_contact.php

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
// CORS restricted - remove wildcard for security

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get Data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Check JSON input if POST is empty (in case of fetch json)
if (empty($name)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $message = $input['message'] ?? '';
}

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Security: Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Security: Sanitize for email header injection
$name = preg_replace('/[\r\n]/', '', $name);
$email = preg_replace('/[\r\n]/', '', $email);

// Format Entry
$timestamp = date('Y-m-d H:i:s');
$entry = "--------------------------------------------------\n";
$entry .= "Date:    $timestamp\n";
$entry .= "Name:    $name\n";
$entry .= "Email:   $email\n";
$entry .= "Message: $message\n";
$entry .= "--------------------------------------------------\n\n";

// Save to SQLite Database
try {
    // Database Path (Adjust path to reach admin folder)
    $dbPath = __DIR__ . '/../admin/data.db';
    
    // Connect
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare & Execute
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message
    ]);

} catch (PDOException $e) {
    // Log error silently, don't break the user flow
    error_log("Database Error: " . $e->getMessage());
}

// Backup to Text File (Optional, kept for safety)
$logFile = __DIR__ . '/contacts.txt';
file_put_contents($logFile, $entry, FILE_APPEND);

// --- SEND EMAIL ---
$to = 'jacobfred19729@gmail.com';
$subject = "New Message from " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . " - TikTok Downloader";

// Security: Escape user input for HTML email
$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

// HTML Email Template (Matches Website Design)
$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #e0e5ec; margin: 0; padding: 20px; color: #2d3436; }
        .container { max-width: 600px; margin: 0 auto; background: #e0e5ec; padding: 40px; border-radius: 30px; 
                     box-shadow: 9px 9px 16px #a3b1c6, -9px -9px 16px #ffffff; }
        .header { text-align: center; margin-bottom: 30px; }
        .brand { font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #00f2ea 0%, #ff0050 100%); 
                 -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card { background: #e0e5ec; padding: 25px; border-radius: 20px; box-shadow: inset 6px 6px 10px #a3b1c6, inset -6px -6px 10px #ffffff; }
        .label { font-size: 12px; color: #636e72; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .value { font-size: 16px; margin-bottom: 20px; line-height: 1.6; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #636e72; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='brand'>New Contact Message</div>
        </div>
        
        <div class='card'>
            <div class='label'>Name</div>
            <div class='value'>$safeName</div>
            
            <div class='label'>Email</div>
            <div class='value'><a href='mailto:$safeEmail' style='color: #ff0050; text-decoration: none;'>$safeEmail</a></div>
            
            <div class='label'>Message</div>
            <div class='value'>$safeMessage</div>
        </div>
        
        <div class='footer'>
            Sent from your TikTok Downloader Web
        </div>
    </div>
</body>
</html>
";

// Headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Website Contact <no-reply@yourdomain.com>" . "\r\n";
$headers .= "Reply-To: $email" . "\r\n";

// Attempt Send
if(mail($to, $subject, $emailBody, $headers)) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent to email!']);
} else {
    // Fallback if mail() fails (common on localhost)
    echo json_encode(['status' => 'success', 'message' => 'Message saved (Email failed on localhost)']);
}
?>
