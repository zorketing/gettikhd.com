<?php
// admin/contacts.php
require_once 'config.php';
checkLogin();

// Delete Action
if (isset($_POST['delete_id'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF Token");
    }
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header('Location: contacts.php');
    exit;
}

// Pagination Setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch Total for Pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM contacts");
$totalRows = $totalStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// Fetch Data
$stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contacts = $stmt->fetchAll();

// View Specific Message (Modal Trigger)
$viewData = null;
if (isset($_GET['view'])) {
    $viewStmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $viewStmt->execute([$_GET['view']]);
    $viewData = $viewStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contacts - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand"><a href="dashboard.php">Admin Panel</a></div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="contacts.php" style="color: var(--accent-color);">All Contacts</a>
            <a href="../index.php" target="_blank">View Site</a>
        </div>
        <a href="dashboard.php?logout=1" class="theme-btn" title="Logout">⏻</a>
    </nav>

    <div class="admin-wrapper">
        <div class="container" style="max-width: 1000px;">
            <h1>Contact submissions</h1>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $row): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td><?= date('M d, H:i', strtotime($row['created_at'])) ?></td>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h($row['email']) ?></td>
                                <td><?= h(mb_strimwidth($row['message'], 0, 40, "...")) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?view=<?= $row['id'] ?>&page=<?= $page ?>" class="btn-sm">View</a>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="btn-sm btn-danger">Del</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($contacts)): ?>
                            <tr><td colspan="6" style="text-align:center; padding: 2rem;">No messages found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="page-link">Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="page-link">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Message Modal -->
    <?php if ($viewData): ?>
    <div class="modal-overlay">
        <div class="modal-content container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                <h2>Message Details</h2>
                <a href="contacts.php?page=<?= $page ?>" class="close-btn">&times;</a>
            </div>
            
            <div class="modal-body">
                <p><strong>Date:</strong> <?= date('F j, Y, g:i a', strtotime($viewData['created_at'])) ?></p>
                <p><strong>From:</strong> <?= h($viewData['name']) ?> &lt;<?= h($viewData['email']) ?>&gt;</p>
                <hr style="margin: 1.5rem 0; opacity: 0.1;">
                <p style="white-space: pre-wrap;"><?= h($viewData['message']) ?></p>
            </div>
            
            <div style="margin-top: 2rem; text-align: right;">
                <a href="mailto:<?= h($viewData['email']) ?>" class="btn-primary" style="width: auto; display: inline-flex; padding: 10px 25px;">Reply via Email</a>
                <a href="contacts.php?page=<?= $page ?>" class="btn-secondary" style="width: auto; display: inline-flex; padding: 10px 25px; margin-left: 10px;">Close</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>
