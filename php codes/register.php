<?php
session_start();
include 'includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Clear old registration data if starting fresh
if (!isset($_POST['username']) && !isset($_POST['otp'])) {
    unset($_SESSION['reg_username'], $_SESSION['reg_email'], $_SESSION['reg_password'], $_SESSION['otp']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // STEP 1: First Click → Send OTP
    if (isset($_POST['username']) && !isset($_POST['otp'])) {

        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email or Username already registered!";
        } else {

            $otp = rand(100000, 999999);

            // Store in SESSION (persists after refresh)
            $_SESSION['reg_username'] = $username;
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_password'] = $password;
            $_SESSION['otp'] = $otp;

            // Send OTP Email
            sendOTP($email, $otp);

            $success = "OTP sent to your email. Please enter OTP below.";
        }
    }

    // STEP 2: Verify OTP
    if (isset($_POST['otp'])) {

        if ($_POST['otp'] == $_SESSION['otp']) {

            $username = $_SESSION['reg_username'];
            $email = $_SESSION['reg_email'];
            $password = password_hash($_SESSION['reg_password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
                // Clear all registration data
                unset($_SESSION['reg_username'], $_SESSION['reg_email'], $_SESSION['reg_password'], $_SESSION['otp']);
            } else {
                $error = "Registration failed!";
            }

        } else {
            $error = "Invalid OTP!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vinayak Footwear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="register-page">
    <div class="register-container">
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="register-card">
            <div class="register-header">
                <h2>👟 Create Account</h2>
            </div>
            <div class="register-body">
                <form method="POST" class="register-form" autocomplete="on">
                    
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" class="form-control" placeholder="Username" required 
                                   value="<?php echo $_SESSION['reg_username'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="Email" required 
                                   value="<?php echo $_SESSION['reg_email'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>

                    <!-- OTP FIELD (APPEARS AFTER FIRST SUBMIT) -->
                    <?php if (isset($_SESSION['otp'])): ?>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-key"></i>
                            <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
                        </div>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-register">Register</button>

                </form>
            </div>
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>