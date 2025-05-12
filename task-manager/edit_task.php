<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';
require_once 'includes/auth_helpers.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

// Fetch task by ID
if (isset($_GET['id'])) {
    $taskId = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    if (!$task) {
        $_SESSION['error_message'] = "Task not found.";
        header("Location: tasks.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Task ID is required.";
    header("Location: tasks.php");
    exit();
}

// Handle task update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (isAdmin() || isManager() || $task['assigned_to'] == $userId) {
        if (isset($_POST['title'], $_POST['description'])) {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $imagePath = $task['image_path'];  // Keep the old image unless a new one is uploaded

            if (empty($title) || empty($desc)) {
                $_SESSION['error_message'] = "Title and description cannot be empty!";
                header("Location: edit_task.php?id=" . $task['id']);
                exit();
            }

            // Handle image upload (same as before)
            if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/tasks/';
                try {
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            throw new Exception("Failed to create upload directory.");
                        }
                    }

                    if (!is_writable($uploadDir)) {
                        if (!chmod($uploadDir, 0777)) {
                            throw new Exception("Cannot make upload directory writable.");
                        }
                    }

                    $fileInfo = pathinfo($_FILES['task_image']['name']);
                    $fileExt = strtolower($fileInfo['extension']);
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (!in_array($fileExt, $allowedTypes)) {
                        throw new Exception("Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.");
                    }

                    $check = getimagesize($_FILES['task_image']['tmp_name']);
                    if ($check === false) {
                        throw new Exception("File is not a valid image.");
                    }

                    $fileName = uniqid('task_' . $userId . '_') . '.' . $fileExt;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['task_image']['tmp_name'], $targetPath)) {
                        chmod($targetPath, 0666);
                        $imagePath = $targetPath;
                    } else {
                        throw new Exception("Failed to move uploaded file.");
                    }
                    
                } catch (Exception $e) {
                    error_log("Image Upload Error: " . $e->getMessage());
                    $_SESSION['error_message'] = "Image upload failed: " . $e->getMessage();
                    header("Location: edit_task.php?id=" . $task['id']);
                    exit();
                }
            }

            try {
                $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, image_path = ? WHERE id = ?");
                $stmt->execute([$title, $desc, $imagePath, $taskId]);
                $_SESSION['success_message'] = "Task updated successfully!";
                header("Location: tasks.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error updating task: " . $e->getMessage();
                header("Location: edit_task.php?id=" . $task['id']);
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Required fields are missing!";
            header("Location: edit_task.php?id=" . $task['id']);
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized to edit this task.";
        header("Location: tasks.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <h3>Edit Task</h3>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($task['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="task_image" class="form-label">Task Image (Optional)</label>
            <input type="file" class="form-control" id="task_image" name="task_image" accept="image/*">
            <small class="text-muted">Allowed types: JPG, JPEG, PNG, GIF</small>
            <?php if ($task['image_path']): ?>
                <p>Current image:</p>
                <img src="<?= htmlspecialchars($task['image_path']) ?>" 
                alt="Task Image" 
                style="max-width: 100px; max-height: 100px;" 
                class="img-thumbnail">

            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Update Task</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
