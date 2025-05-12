<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Check if user is an Admin
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Check if user is a Manager
function isManager() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'manager';
}

// Check if user is a Regular User
function isUser() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
