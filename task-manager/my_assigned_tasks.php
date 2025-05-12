<?php
require 'includes/auth_helpers.php';
require 'config/db.php';

session_start(); // Ensure session is started

// Check if the user is a manager
if (!isManager()) {
    header("Location: dashboard.php");
    exit();
}

// Fetch the manager's ID from the session
$userId = $_SESSION['user']['id'] ?? null; // Using null coalescing operator to avoid undefined index

if (!$userId) {
    // Handle the case where user ID is not found in session
    die("Error: User ID not found in session.");
}

// Fetch tasks assigned to this manager
try {
    $stmt = $pdo->prepare("SELECT tasks.*, users.name AS assigned_name FROM tasks 
                          LEFT JOIN users ON tasks.assigned_to = users.id 
                          WHERE tasks.assigned_to = ?");
    $stmt->execute([$userId]);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    // Catch and display any database errors
    echo "Database Error: " . $e->getMessage();
    exit();
}

// Allowed status values
$allowedStatuses = ['todo', 'in_progress', 'done'];


// Handle task status update when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
    // Sanitize and validate the inputs
    $taskId = $_POST['task_id'];
    $status = $_POST['status'];
    $desc = $_POST['description'] ?? ''; // Optional description field
    
    // Validate status
    if (!in_array($status, $allowedStatuses)) {
        die("Error: Invalid status value.");
    }

    if (empty($taskId) || empty($status)) {
        die("Error: Missing required fields.");
    }

    // Update the task status and description in the database
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, description = ? WHERE id = ? AND assigned_to = ?");
        $stmt->execute([$status, $desc, $taskId, $userId]);
        
        // Redirect back to the tasks page after successful update
        header("Location: my_assigned_tasks.php");
        exit();
    } catch (PDOException $e) {
        // Handle any database errors
        echo "Error updating task: " . $e->getMessage();
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h3 class="mb-4">My Assigned Tasks</h3>

    <?php if (count($tasks) > 0): ?>
        <div class="row">
            <?php foreach ($tasks as $task): ?>
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title"><?= htmlspecialchars($task['title']) ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">

                                <!-- Description Field -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" class="form-control"><?= htmlspecialchars($task['description']) ?></textarea>
                                </div>

                                <!-- Status Dropdown -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="todo" <?= $task['status'] === 'todo' ? 'selected' : '' ?>>To Do</option>
                                        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="done" <?= $task['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                                    </select>

                                </div>

                                <!-- Update Button -->
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-save"></i> Update Task
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No tasks assigned.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
