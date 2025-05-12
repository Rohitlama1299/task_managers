<?php
require 'includes/auth_helpers.php';
require 'config/db.php';

if (!isAdmin() && !isManager()) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $taskId = $_GET['id'];

    // Delete task from the database
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);

    // Success message
    $_SESSION['message'] = 'Task deleted successfully.';
}

header("Location: tasks.php");
exit();
?>
