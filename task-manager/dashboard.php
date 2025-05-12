<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'includes/auth_helpers.php'; // Role check helpers
require 'config/db.php'; // DB connection
include 'includes/header.php'; // Page header

$user = $_SESSION['user']; // Current user info
?>

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header text-center">
            <h2>Welcome, <?= htmlspecialchars($user['name']); ?>! 
                <?php 
                // Display role next to the name
                if (isAdmin()) {
                    echo "<span class=''>(Admin)</span>";
                } elseif (isManager()) {
                    echo "<span class=''>(Manager)</span>";
                } else {
                    echo "<span class=''>(User)</span>";
                }
                ?>
            </h2>
        </div>
        <div class="card-body">
            <p class="text-center">Select an option below to manage tasks and view your assigned tasks.</p>
            
            <!-- Admin controls -->
            <?php if (isAdmin()): ?>
            <div class="d-grid gap-2">
                <a href="users.php" class="btn btn-outline-primary btn-lg">
                    <i class="bi bi-person-badge"></i> Manage Users
                </a>
                <a href="tasks.php" class="btn btn-outline-success btn-lg">
                    <i class="bi bi-clipboard-check"></i> Manage All Tasks
                </a>
            </div>
            <hr>
            <?php endif; ?>

            <!-- Manager controls -->
            <?php if (isManager()): ?>
            <div class="d-grid gap-2">
                <a href="tasks.php" class="btn btn-outline-success btn-lg">
                    <i class="bi bi-people-fill"></i> Manage Team Tasks
                </a>
                <a href="my_assigned_tasks.php" class="btn btn-outline-info btn-lg">
                    <i class="bi bi-folder-lock"></i> My Assigned Tasks
                </a>
            </div>
            <hr>
            <?php endif; ?>

            <!-- Regular user controls -->
            <?php if (!isAdmin() && !isManager()): ?>
            <div class="d-grid gap-2">
                <a href="tasks.php" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-list-task"></i> My Tasks
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
