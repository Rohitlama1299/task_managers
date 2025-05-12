<?php
require_once 'includes/auth_helpers.php';
require 'config/db.php';

if (!isAdmin()) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['email'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['role']
    ]);

    // Set the session message before redirect
    $_SESSION['message'] = "User successfully created!";

    // Redirect to the users page
    header("Location: users.php");
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header text-center bg-primary text-white">
            <h3>Create New User</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name <i class="bi bi-person-fill"></i></label>
                    <input name="name" type="text" class="form-control" id="name" placeholder="Enter Name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email <i class="bi bi-envelope-fill"></i></label>
                    <input name="email" type="email" class="form-control" id="email" placeholder="Enter Email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <i class="bi bi-lock-fill"></i></label>
                    <input name="password" type="password" class="form-control" id="password" placeholder="Enter Password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role <i class="bi bi-person-check-fill"></i></label>
                    <select name="role" class="form-select" id="role" required>
                        <option value="user">User</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-person-plus-fill"></i> Create User
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
