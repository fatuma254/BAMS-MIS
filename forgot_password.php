<?php
require_once 'config/database.php';
$error = '';
$success = '';
$generated_token = '';
$token_preview = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = "Invalid email format";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate unique token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // First delete existing tokens for this user
                $deleteStmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
                $deleteStmt->execute([$user['id']]);
                
                // Then insert new token with extended expiration
                $insertStmt = $pdo->prepare("
                    INSERT INTO password_reset_tokens 
                    (user_id, token, expires_at) 
                    VALUES (?, ?, ?)
                ");
                
                if ($insertStmt->execute([$user['id'], $token, $expires])) {
                    // Verify the token was inserted correctly
                    $verifyStmt = $pdo->prepare("
                        SELECT * FROM password_reset_tokens 
                        WHERE user_id = ? AND token = ? AND expires_at > NOW()
                    ");
                    $verifyStmt->execute([$user['id'], $token]);
                    
                    if ($verifyStmt->fetch()) {
                        // Token stored successfully
                        session_start();
                        $_SESSION['password_reset_token'] = $token;
                        $_SESSION['password_reset_email'] = $email;
                        $_SESSION['token_expiry'] = $expires;
                        
                        $generated_token = $token;
                        $token_preview = substr($token, 0, 6) . '...' . substr($token, -6);
                        $success = "Token has been generated. Redirecting to token verification page...";
                        
                        // Set redirect flag
                        $shouldRedirect = true;
                    } else {
                        throw new Exception("Failed to store token properly.");
                    }
                } else {
                    throw new Exception("Failed to generate token.");
                }
            } else {
                $error = "No account found with this email address.";
            }
        } catch(Exception $e) {
            error_log("Password Reset Error: " . $e->getMessage());
            $error = "Error generating token. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BAMS - Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .copy-token-container {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 400px;
        }
        .copy-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #007bff;
            transition: color 0.3s ease;
        }
        .copy-icon:hover {
            color: #0056b3;
        }
        .token-preview {
            padding-right: 40px;
            background-color: #f8f9fa;
            font-family: monospace;
        }
        #redirectCountdown {
            font-weight: bold;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h2 class="text-center mb-4">Forgot Password</h2>
           
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
           
            <?php if($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="mt-3 text-center copy-token-container">
                        <input type="text" 
                               class="form-control token-preview text-center" 
                               value="<?php echo htmlspecialchars($token_preview); ?>" 
                               readonly 
                               id="resetTokenPreview">
                        <i class="bi bi-clipboard copy-icon" 
                           id="copyTokenBtn" 
                           title="Copy Full Token"></i>
                        <small class="d-block text-muted mt-2">
                            Redirecting in <span id="redirectCountdown">3</span> seconds...
                        </small>
                    </div>
                </div>
            <?php endif; ?>
           
            <?php if(!$success): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Generate Reset Token</button>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-decoration-none">Back to Login</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    <?php if($generated_token): ?>
        // Copy token functionality
        document.getElementById('copyTokenBtn').addEventListener('click', function() {
            const tempInput = document.createElement('input');
            tempInput.value = <?php echo json_encode($generated_token); ?>;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            
            const btn = this;
            btn.classList.remove('bi-clipboard');
            btn.classList.add('bi-clipboard-check');
            
            setTimeout(() => {
                btn.classList.remove('bi-clipboard-check');
                btn.classList.add('bi-clipboard');
            }, 2000);
        });

        // Automatic redirect with countdown
        let countdown = 3;
        const countdownElement = document.getElementById('redirectCountdown');
        
        const countdownInterval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'enter_reset_token.php';
            }
        }, 1000);

        // Auto copy token to clipboard
        window.onload = function() {
            document.getElementById('copyTokenBtn').click();
        };
    <?php endif; ?>
    </script>
</body>
</html>