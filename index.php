<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['user_email'] = $user['email'];
            
            // Fetch user profile data
            $profileStmt = $pdo->prepare("SELECT first_name, last_name FROM user_profiles WHERE user_id = ?");
            $profileStmt->execute([$user['id']]);
            $profile = $profileStmt->fetch();
            
            if ($profile) {
                $_SESSION['full_name'] = $profile['first_name'] . ' ' . $profile['last_name'];
            }
            
            // Redirect based on user type
            switch($user['user_type']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    break;
                case 'professional':
                    header("Location: professional/dashboard.php");
                    break;
                case 'client':
                    header("Location: client/dashboard.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } catch(PDOException $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BAMS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">BAMS Beauty MIS Login</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <div class="text-center mt-3">
                    <a href="signup.php" class="text-decoration-none">Create an account</a> | 
                    <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>