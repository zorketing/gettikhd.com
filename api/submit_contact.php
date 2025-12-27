<?php
/**
 * Contact Submission API - Service Refactor
 */

require_once __DIR__ . '/../includes/config.php';

use App\Services\ContactService;

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get Input Data
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$message = $input['message'] ?? '';

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Sanitize inputs
$name = preg_replace('/[\r\n]/', '', strip_tags($name));
$email = preg_replace('/[\r\n]/', '', strip_tags($email));

// Initialize Service
$pdo = getDatabaseConnection();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Service temporarily unavailable']);
    exit;
}

$contactService = new ContactService($pdo);

// Save to Database
$saved = $contactService->submitContact($name, $email, $message);

// Email Logic (Placeholder for brevity, keep existing flow if important)
// --- SEND EMAIL ---
$to = 'jacobfred19729@gmail.com'; 
$subject = "New Message from " . h($name) . " - TikTokDL";

$safeName = h($name);
$safeEmail = h($email);
$safeMessage = nl2br(h($message));

$emailBody = "
<div style='font-family: sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
    <h2>New Contact Submission</h2>
    <p><strong>Name:</strong> $safeName</p>
    <p><strong>Email:</strong> $safeEmail</p>
    <hr>
    <p><strong>Message:</strong><br>$safeMessage</p>
</div>
";

$headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
$headers .= "From: Website Contact <no-reply@gettikhd.com>\r\n";

@mail($to, $subject, $emailBody, $headers);

if ($saved) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save message.']);
}
?>
?>
