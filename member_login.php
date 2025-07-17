<?php
session_start();
require_once 'config/database.php';

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$login_attempts = 5; // Maximum login attempts
$lockout_time = 300; // Lockout time in seconds (5 minutes)

// Check if IP is locked out
$stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM member_sessions WHERE session_id LIKE 'login_%' AND created_at > NOW() - INTERVAL ? SECOND AND session_id LIKE CONCAT('%', ?)");
$stmt->execute([$lockout_time, $ip]);
$attempts = $stmt->fetch()['attempts'];

if ($attempts >= $login_attempts) {
    $error = "Too many failed login attempts. Please wait 5 minutes before trying again.";
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Record login attempt
    try {
        $stmt = $pdo->prepare("INSERT INTO member_sessions (member_id, session_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([0, 'login_' . $ip]);
    } catch (PDOException $e) {
        // Log error but continue with login attempt
        error_log("Failed to record login attempt: " . $e->getMessage());
    }
    
    $sql = "SELECT id, password FROM members WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $member = $stmt->fetch();
    
    if ($member && password_verify($password, $member['password'])) {
        // Clean up login attempts
        try {
            $stmt = $pdo->prepare("DELETE FROM member_sessions WHERE session_id LIKE CONCAT('%', ?)");
            $stmt->execute([$ip]);
        } catch (PDOException $e) {
            error_log("Failed to clean up login attempts: " . $e->getMessage());
        }
        
        $_SESSION['member_id'] = $member['id'];
        
        // Store session in database
        $session_id = session_id();
        $sql = "INSERT INTO member_sessions (member_id, session_id) VALUES (?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$member['id'], $session_id]);
        } catch (PDOException $e) {
            error_log("Failed to store session: " . $e->getMessage());
            // Continue with login even if session storage fails
        }
        
        header("location: member_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-book-reader fa-3x text-primary mb-3"></i>
                            <h2 class="card-title mb-3">Member Login</h2>
                            <p class="text-muted">Welcome back! Please login to continue.</p>
                        </div>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                                <a href="member_register.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </a>
                            </div>
                            <div class="text-center mt-3">
                                <a href="member_forgot_password.php" class="text-decoration-none text-muted">
                                    <i class="fas fa-question-circle me-1"></i>Forgot Password?
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
