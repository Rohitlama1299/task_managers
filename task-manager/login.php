<?php
// Always start the session first
session_start();

require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the email and password are correct
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Store user info in the session
        $_SESSION['user'] = $user;

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --hover-color: #4338CA;
            --light-bg: #F9FAFB;
            --card-bg: #ffffff;
            --text-color: #111827;
            --input-bg: #F3F4F6;
            --input-border: #E5E7EB;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: var(--light-bg);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
        
        .login-card {
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: none;
        }
        
        .login-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
        }
        
        .login-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin: 0 auto 1rem;
        }
        
        .login-title {
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .login-subtitle {
            color: #6B7280;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .login-body {
            padding: 1rem 2rem 2rem;
        }
        
        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.15s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--hover-color);
            transform: translateY(-1px);
        }
        
        .input-group-text {
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-right: none;
            color: #6B7280;
        }
        
        .password-toggle {
            cursor: pointer;
        }
        
        .alert {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }
        
        .help-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #6B7280;
        }
        
        .help-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .help-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="login-title">Welcome back</h1>
                <p class="login-subtitle">Enter your credentials to access your account</p>
            </div>
            <div class="login-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input name="email" type="email" class="form-control" id="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label">Password</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input name="password" type="password" class="form-control" id="password" placeholder="••••••••" required>
                            <span class="input-group-text password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign in
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>