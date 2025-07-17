<?php
session_start();
require_once 'config/database.php';

// Check if token is valid
if (!isset($_GET['token'])) {
    header("location: member_forgot_password.php");
    exit();
}

$reset_token = $_GET['token'];

// Verify token
$stmt = $pdo->prepare("SELECT member_id FROM member_sessions WHERE session_id = ? AND created_at > NOW() - INTERVAL 1 HOUR");
$stmt->execute([$reset_token]);
$session = $stmt->fetch();

if (!$session) {
    $error = "Invalid or expired reset link. Please request a new one.";
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
        $stmt->execute([$password, $session['member_id']]);
        
        // Clear reset token
        $stmt = $pdo->prepare("DELETE FROM member_sessions WHERE session_id = ?");
        $stmt->execute([$reset_token]);
        
        $success = "Password has been reset successfully. You can now login.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">Reset Password</h2>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                            <div class="mt-3">
                                <a href="member_login.php" class="btn btn-primary">Login Now</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if(!isset($success)): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Reset Password</button>
                                    <a href="member_login.php" class="btn btn-secondary">Back to Login</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
