<?php
require_once 'includes/auth_helpers.php';
require 'config/db.php';
session_start();

// Only allow admins
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

// Fetch users
$stmt = $pdo->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<!-- Optional: Bootstrap icons if not already included -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Display a notification if the message is set -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" id="popupNotification">
        <?= $_SESSION['message'] ?>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="container mt-4">
    <h3>User Management</h3>

    <a href="create_user.php" class="btn btn-success mb-3">
        <i class="bi bi-person-plus"></i> Add New User
    </a>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th style="width: 180px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm me-1">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this user?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>

<!-- JavaScript to handle the auto-hide of the popup -->
<script>
    // Check if the popup notification exists
    var notification = document.getElementById('popupNotification');
    if (notification) {
        // Set a timeout to hide the notification after 2 seconds (2000 milliseconds)
        setTimeout(function() {
            notification.classList.remove('show');
        }, 2000);
    }
</script>
