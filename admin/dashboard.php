<?php
// admin/dashboard.php
require_once 'config.php';
checkLogin();

// Logout Logic
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch Stats
$totalStmt = $pdo->query("SELECT COUNT(*) FROM contacts");
$totalMessages = $totalStmt->fetchColumn();

// Fetch Recent Messages
$recentStmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
$recentMessages = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TikTok Downloader</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard.php">Admin Panel</a>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" style="color: var(--accent-color);">Dashboard</a>
            <a href="contacts.php">All Contacts</a>
            <a href="../index.php" target="_blank">View Site</a>
        </div>
        <a href="?logout=1" class="theme-btn" title="Logout" style="text-decoration: none;">⏻</a>
    </nav>

    <div class="admin-wrapper">
        <!-- Stats Card -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Messages</h3>
                <div class="number highlight"><?= $totalMessages ?></div>
                <p>All time submissions</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="container" style="max-width: 1000px; margin-top: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="margin: 0;">Recent Messages</h2>
                <a href="contacts.php" class="btn-secondary" style="width: auto; padding: 10px 20px;">View All</a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Message</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentMessages) > 0): ?>
                            <?php foreach ($recentMessages as $msg): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($msg['created_at'])) ?></td>
                                    <td><strong><?= h($msg['name']) ?></strong></td>
                                    <td><?= h(mb_strimwidth($msg['message'], 0, 50, "...")) ?></td>
                                    <td>
                                        <a href="contacts.php?view=<?= $msg['id'] ?>" class="btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem;">No messages yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
