<?php
session_start();
require_once 'config/database.php';

// Ensure user has verified token
if (!isset($_SESSION['token_verified']) || !$_SESSION['token_verified']) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['password_reset_email'];

    try {
        // Validate password
        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update user password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);

            // Delete used token
            $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = (SELECT id FROM users WHERE email = ?)");
            $stmt->execute([$email]);

            // Clear session data
            unset($_SESSION['password_reset_token']);
            unset($_SESSION['password_reset_email']);
            unset($_SESSION['token_verified']);

            $success = "Password successfully reset. You can now login.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BAMS - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">Reset Password</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <div class="text-center">
                    <a href="index.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}" 
                           title="Must contain at least one number, one uppercase, one lowercase letter, one special character, and be at least 8 characters long">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>