<?php
session_start();

$error = '';
$success = '';

// Include database connection
include 'includes/db.php';

// Check if token exists
if (!isset($_GET['token'])) {
    echo "<div class='alert alert-danger'>Invalid reset link!</div>";
    exit();
}

$token = $_GET['token'];

// Verify token
$result = $conn->query("SELECT * FROM password_resets WHERE token = '$token'");
if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Invalid or expired reset link!</div>";
    exit();
}

$reset_data = $result->fetch_assoc();

// Check if expired
if (strtotime($reset_data['expires_at']) < time()) {
    // Delete expired token
    $conn->query("DELETE FROM password_resets WHERE token = '$token'");
    echo "<div class='alert alert-danger'>Reset link has expired! Please request a new one.</div>";
    exit();
}

/* ================= RESET PASSWORD ================= */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $reset_data['email'];
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Delete used token
            $conn->query("DELETE FROM password_resets WHERE token = '$token'");
            $success = "Password reset successful! <a href='login.php'>Login here</a>";
        } else {
            $error = "Failed to reset password!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
        
        <div class="login-card">
            <div class="login-header">
                <h2>🔐 Reset Password</h2>
            </div>
            <div class="login-body">
                <p class="text-muted text-center mb-4">Enter your new password below.</p>
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="New Password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login">Reset Password</button>
                </form>
            </div>
            <div class="login-footer">
                <p><a href="login.php">← Back to Login</a></p>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</body>
</html>