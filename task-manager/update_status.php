<?php
require 'includes/auth_helpers.php';  // Include the updated auth_helpers.php
require 'config/db.php';  // Include the database connection file

// Check if the user is logged in
if (!isLoggedIn()) {
    echo "User is not logged in!";
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

// Check if the task ID and status are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    $taskId = $_POST['task_id'];
    $status = $_POST['status'];

    // Validate the status value
    $validStatuses = ['todo', 'in_progress', 'done'];
    if (!in_array($status, $validStatuses)) {
        echo "Invalid status value!";
        exit();
    }

    // Only allow users to update tasks assigned to them
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
    $stmt->execute([$taskId, $userId]);
    $task = $stmt->fetch();

    if (!$task) {
        echo "Task not found or not assigned to this user.";
        exit();
    }

    // Proceed to update the task's status
    try {
        $update = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $result = $update->execute([$status, $taskId]);

        if ($result) {
            // Redirect back to tasks.php after updating the status
            header("Location: tasks.php");
            exit();
        } else {
            echo "Failed to update task status. Please try again.";
        }
    } catch (Exception $e) {
        // Log error and show a friendly message
        error_log($e->getMessage());
        echo "An error occurred while updating the task status. Please try again later.";
    }
} else {
    echo "Invalid request.";
    exit();
}
?>
