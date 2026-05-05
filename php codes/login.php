<?php
include 'includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

/* ================= LOGIN ================= */

if (isset($_POST['login'])) {

    $username = $conn->real_escape_string($_POST['username']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($_POST['password'], $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                echo "<script>alert('Hello Admin! Welcome to Vinayak Footwear Dashboard 👑'); window.location.href='admin_dashboard.php';</script>";
            } else {
                echo "<script>alert('Hello User! Welcome to Vinayak Footwear 👟'); window.location.href='index.php';</script>";
            }

            exit();

        } else {
            $error = "Invalid username or password!";
        }

    } else {
        $error = "Invalid username or password!";
    }

    $stmt->close();
}

/* ================= FORGOT PASSWORD ================= */

if (isset($_POST['forgot_password'])) {

    $email = $conn->real_escape_string($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // Delete old tokens for this email
        $conn->query("DELETE FROM password_resets WHERE email = '$email'");

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save to database
        $conn->query("INSERT INTO password_resets (email, token, expires_at) VALUES ('$email', '$token', '$expires')");

        // Create reset link
        $reset_link = "http://localhost/vinayak_footwear/reset_password.php?token=$token";
        
        // Send email
        if (sendPasswordResetLink($email, $reset_link)) {
            $success = "Password reset link sent to your email!";
        } else {
            $error = "Failed to send email. Please try again.";
        }

    } else {
        $error = "Email not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="login-container">
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="login-card" id="login-card">
            <div class="login-header">
                <h2>👟 Login to your account</h2>
            </div>
            <div class="login-body">
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn-login">Login</button>
                </form>
                <div class="login-footer">
                    <div class="login-toggle">
                        <a id="forgot-link">Forgot Password?</a>
                    </div>
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>

        <div class="forgot-section" id="forgot-section">
            <div class="forgot-card">
                <div class="forgot-header">
                    <h3>Forgot Password</h3>
                </div>
                <div class="forgot-body">
                    <p class="text-muted text-center mb-3">Enter your email to receive a password reset link.</p>
                    <form method="POST" class="forgot-form">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                        </div>
                        <button type="submit" name="forgot_password" class="btn-forgot">Send Reset Link</button>
                    </form>
                    <div class="login-toggle">
                        <a id="back-link">← Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('forgot-link').addEventListener('click', function() {
        document.getElementById('login-card').style.display = 'none';
        document.getElementById('forgot-section').style.display = 'block';
    });
    document.getElementById('back-link').addEventListener('click', function() {
        document.getElementById('forgot-section').style.display = 'none';
        document.getElementById('login-card').style.display = 'block';
    });
    </script>
</body>
</html>