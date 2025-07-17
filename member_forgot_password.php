<?php
session_start();
require_once 'config/database.php';

// Generate reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Handle forgot password request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    $member = $stmt->fetch();
    
    if ($member) {
        // Generate reset token and expiry time
        $reset_token = generateResetToken();
        $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        $stmt = $pdo->prepare("INSERT INTO member_sessions (member_id, session_id, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$member['id'], $reset_token, $expiry_time]);
        
        // Send reset email
        $reset_url = "http://localhost/bhavu/member_reset_password.php?token=" . $reset_token;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password:\n\n" . $reset_url;
        $headers = "From: no-reply@library.com";
        
        if (mail($email, $subject, $message, $headers)) {
            $success = "We've sent a password reset link to your email address.";
        } else {
            $error = "Failed to send password reset email. Please try again.";
        }
    } else {
        $error = "No account found with this email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title mb-4">Forgot Password</h2>
                        
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Send Reset Link</button>
                                <a href="member_login.php" class="btn btn-secondary">Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
