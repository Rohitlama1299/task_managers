<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Required includes
require_once 'config/db.php';
require_once 'includes/auth_helpers.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];
$tasks = []; // Initialize tasks array to prevent undefined variable errors

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (isAdmin() || isManager()) {
        if (isset($_POST['title'], $_POST['description'], $_POST['assigned_to'])) {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $assignedTo = $_POST['assigned_to'];
            $imagePath = null;

            if (empty($title) || empty($desc)) {
                $_SESSION['error_message'] = "Title and description cannot be empty!";
                header("Location: tasks.php");
                exit();
            }

            // Handle image upload
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
                    header("Location: tasks.php");
                    exit();
                }
            }

            if ($userRole === 'manager') {
                $check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                $check->execute([$assignedTo]);
                $targetRole = $check->fetchColumn();
                if ($targetRole === 'admin') {
                    $_SESSION['error_message'] = "Managers cannot assign tasks to admins.";
                    header("Location: tasks.php");
                    exit();
                }
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, created_by, status, image_path) VALUES (?, ?, ?, ?, 'todo', ?)");
                $stmt->execute([$title, $desc, $assignedTo, $userId, $imagePath]);
                $_SESSION['success_message'] = "Task added successfully!";
                header("Location: tasks.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Database error: " . $e->getMessage();
                header("Location: tasks.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Required fields are missing!";
            header("Location: tasks.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized to create tasks.";
        header("Location: tasks.php");
        exit();
    }
}

// Load tasks
try {
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT tasks.*, users.name AS assigned_name FROM tasks LEFT JOIN users ON tasks.assigned_to = users.id ORDER BY tasks.id DESC");
        $tasks = $stmt->fetchAll();
    } elseif (isManager()) {
        $stmt = $pdo->prepare("
            SELECT tasks.*, users.name AS assigned_name
            FROM tasks
            LEFT JOIN users ON tasks.assigned_to = users.id
            WHERE users.role = 'user'
            ORDER BY tasks.id DESC
        ");
        $stmt->execute();
        $tasks = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("
            SELECT tasks.*, users.name AS assigned_name
            FROM tasks
            LEFT JOIN users ON tasks.assigned_to = users.id
            WHERE assigned_to = ?
            ORDER BY tasks.id DESC
        ");
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error loading tasks: " . $e->getMessage();
    $tasks = [];
}

// Load user list for assigning tasks
$userList = [];
if (isAdmin()) {
    $userList = $pdo->query("SELECT id, name, role FROM users ORDER BY name")->fetchAll();
} elseif (isManager()) {
    $userList = $pdo->query("SELECT id, name FROM users WHERE role = 'user' ORDER BY name")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-5">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div id="task-alert" class="alert alert-success alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div id="error-alert" class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h3 class="text-center mb-4 text-primary">
        <i class="fas fa-tasks"></i> Task Management
    </h3>

    <?php if (isAdmin() || isManager()): ?>
    <div class="card mb-4 shadow-lg">
        <div class="card-header">
            <h5><i class="fas fa-plus-circle"></i> Create a New Task</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <input name="title" class="form-control" placeholder="Task title" required maxlength="255">
                </div>
                <div class="mb-3">
                    <textarea name="description" class="form-control" placeholder="Task description" required rows="3" maxlength="1000"></textarea>
                </div>
                <div class="mb-3">
                    <label for="task_image" class="form-label">Task Image (Optional)</label>
                    <input type="file" name="task_image" class="form-control" accept="image/*">
                    <small class="text-muted">Allowed types: JPG, JPEG, PNG, GIF</small>
                </div>
                <?php if (!empty($userList)): ?>
                <div class="mb-3">
                    <select name="assigned_to" class="form-select" required>
                        <option value="">Select User to Assign</option>
                        <?php foreach ($userList as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check-circle"></i> Add Task</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <?php if (isAdmin() || isManager()): ?>
                            <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['description']) ?></td>
                        <td>
                            <?php if (!empty($task['image_path'])): ?>
                                <img src="<?= htmlspecialchars($task['image_path']) ?>" 
                                    alt="Task Image" 
                                    style="max-width: 100px; max-height: 100px;" 
                                    class="img-thumbnail">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isUser() && $task['assigned_to'] == $userId): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <input type="hidden" name="action" value="update_status">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="todo" <?= $task['status'] === 'todo' ? 'selected' : '' ?>>To Do</option>
                                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="done" <?= $task['status'] === 'done' ? 'selected' : '' ?>>Done</option>
                                </select>
                            </form>
                            <?php else: ?>
                                <?= ucfirst(str_replace('_', ' ', htmlspecialchars($task['status']))) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($userRole === 'admin'): ?>
                                <!-- Admin sees no name or just an empty field here -->
                                <?= $task['assigned_name'] !== 'admin' ? htmlspecialchars($task['assigned_name']) : '' ?>
                            <?php else: ?>
                                <!-- Manager or User sees assigned name -->
                                <?= htmlspecialchars($task['assigned_name']) ?>
                            <?php endif; ?>
                        </td>
                        <?php if (isAdmin() || isManager()): ?>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
