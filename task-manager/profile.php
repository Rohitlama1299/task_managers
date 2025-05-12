<?php
session_start();
require 'config/db.php';
require 'includes/auth_helpers.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name)) {
        $_SESSION['error'] = "Name cannot be empty.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        $update_query = "UPDATE users SET name = :name";
        $params = ['name' => $name];

        if (!empty($new_password)) {
            $update_query .= ", password = :password";
            $params['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $update_query .= " WHERE id = :id";
        $params['id'] = $user['id'];

        $stmt = $pdo->prepare($update_query);
        if ($stmt->execute($params)) {
            // Update session data with new values
            $_SESSION['user']['name'] = $name;

            // If the password was updated, don't load the password into session
            if (!empty($new_password)) {
                unset($_SESSION['user']['password']);
            }

            // Success message stored in session for the next request
            $_SESSION['success'] = "Profile updated successfully.";

            // Reload the page to show updated data
            header("Location: profile.php"); // This will reload the page
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h3>Your Profile</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success fade show" id="success-message">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); // Remove the success message after showing ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger fade show" id="error-message">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); // Remove the error message after showing ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input name="name" type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email (cannot change)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">New Password (optional)</label>
            <input name="new_password" type="password" class="form-control" placeholder="Enter new password">
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input name="confirm_password" type="password" class="form-control" placeholder="Confirm new password">
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Function to hide notifications after 5 seconds
    setTimeout(function() {
        var successMessage = document.getElementById('success-message');
        var errorMessage = document.getElementById('error-message');

        // Fade out the success message
        if (successMessage) {
            successMessage.classList.remove('show');
            successMessage.classList.add('fade');
        }

        // Fade out the error message
        if (errorMessage) {
            errorMessage.classList.remove('show');
            errorMessage.classList.add('fade');
        }
    }, 2000); // Hide after 5 seconds
</script>
