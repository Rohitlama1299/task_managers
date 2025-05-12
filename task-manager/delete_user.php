<?php
session_start();
require 'includes/auth_helpers.php';
require 'config/db.php';

// Ensure the user is an admin
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

// Check if 'id' is set and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch the user role before proceeding
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        // Prevent deleting the admin or yourself
        if ($user['role'] !== 'admin' && $userId !== $_SESSION['user']['id']) {
            // Proceed with deleting the user
            $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $result = $deleteStmt->execute([$userId]);

            // Check if the delete query was successful
            if ($result) {
                $_SESSION['message'] = "User deleted successfully!";
            } else {
                $_SESSION['message'] = "Error: Unable to delete the user.";
            }
        } else {
            $_SESSION['message'] = "You cannot delete an admin or yourself.";
        }
    } else {
        $_SESSION['message'] = "User not found.";
    }
} else {
    $_SESSION['message'] = "Invalid user ID.";
}

header("Location: users.php");
exit();
