<?php
session_start();
require_once 'config/database.php';

// Debug logging function
function debugLog($message, $data) {
    error_log(print_r($message . ": " . json_encode($data), true));
}

// Ensure user has gone through forgot password process
if (!isset($_SESSION['password_reset_email']) || !isset($_SESSION['password_reset_token'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_token = trim($_POST['reset_token']); // Remove any whitespace
    $email = $_SESSION['password_reset_email'];
    
    try {
        // First, let's check if we can find the user
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $userStmt->execute([$email]);
        $user = $userStmt->fetch();
        
        if ($user) {
            // Now verify the token
            $stmt = $pdo->prepare("
                SELECT * FROM password_reset_tokens 
                WHERE user_id = ? 
                AND token = ? 
                AND expires_at > NOW()
            ");
            
            $stmt->execute([$user['id'], $entered_token]);
            $reset_request = $stmt->fetch();

            // Debug logging
            debugLog("Entered Token", $entered_token);
            debugLog("Session Token", $_SESSION['password_reset_token']);
            debugLog("DB Token Result", $reset_request);
            
            if ($reset_request) {
                // Token is valid, proceed to reset password
                $_SESSION['token_verified'] = true;
                header("Location: reset_password.php");
                exit();
            } else {
                // Check if token is expired
                $expiredCheck = $pdo->prepare("
                    SELECT * FROM password_reset_tokens 
                    WHERE user_id = ? 
                    AND token = ? 
                    AND expires_at <= NOW()
                ");
                $expiredCheck->execute([$user['id'], $entered_token]);
                $expired = $expiredCheck->fetch();
                
                if ($expired) {
                    $error = "Token has expired. Please request a new token.";
                } else {
                    $error = "Invalid token. Please check and try again.";
                }
            }
        } else {
            $error = "Invalid email address.";
        }
    } catch(Exception $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        $error = "An error occurred while verifying the token. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BAMS - Enter Reset Token</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">Enter Reset Token</h2>
           
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
           
            <p class="text-center">
                Please enter the token that was generated.
                Make sure to copy the entire token exactly as shown.
            </p>
           
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="reset_token" class="form-label">Reset Token</label>
                    <input type="text" 
                           class="form-control" 
                           id="reset_token" 
                           name="reset_token" 
                           required
                           autocomplete="off"
                           placeholder="Paste your token here">
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify Token</button>
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none">Request New Token</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>