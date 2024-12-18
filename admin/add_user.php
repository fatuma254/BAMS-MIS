
<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form data
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation checks
    if (!$email) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (strlen($first_name) < 2) {
        $errors[] = "First name must be at least 2 characters long.";
    }
    
    if (strlen($last_name) < 2) {
        $errors[] = "Last name must be at least 2 characters long.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email address already exists.";
    }
    
    // If no errors, proceed with insertion
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Insert into users table
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, user_type, is_active, profile_complete) 
                VALUES (?, ?, 'professional', ?, ?)
            ");
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $profile_complete = isset($_POST['profile_complete']) ? 1 : 0;
            
            $stmt->execute([
                $email,
                $hashed_password,
                $is_active,
                $profile_complete
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Insert into user_profiles table
            $stmt = $pdo->prepare("
                INSERT INTO user_profiles (user_id, first_name, last_name, phone_number, address, bio) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id,
                $first_name,
                $last_name,
                $_POST['phone_number'] ?? null,
                $_POST['address'] ?? null,
                $_POST['bio'] ?? null
            ]);
            
            $pdo->commit();
            $success = true;
            
            // Redirect after successful addition
            header("Location: users.php?msg=added");
            exit();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Employee</title>
    <style>
        .password-strength {
            margin-top: 5px;
            font-size: 0.875em;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #198754; }
    </style>
</head>
<body>

<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Add New User</h3>
                    <a href="users.php" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="addEmployeeForm" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                   required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" id="password" 
                                       required minlength="8">
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       required minlength="8">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number" 
                                   value="<?= isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="3"><?= isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">Active Account</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="profile_complete">
                                <label class="form-check-label">Profile Complete</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='users.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthDiv = document.getElementById('passwordStrength');
    let strength = 0;
    let message = '';

    // Length check
    if (password.length >= 8) strength += 1;
    // Contains number
    if (/\d/.test(password)) strength += 1;
    // Contains letter
    if (/[a-zA-Z]/.test(password)) strength += 1;
    // Contains special character
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;

    if (password.length === 0) {
        strengthDiv.className = 'password-strength';
        strengthDiv.textContent = '';
    } else if (strength < 2) {
        strengthDiv.className = 'password-strength strength-weak';
        strengthDiv.textContent = 'Weak password';
    } else if (strength < 4) {
        strengthDiv.className = 'password-strength strength-medium';
        strengthDiv.textContent = 'Medium password';
    } else {
        strengthDiv.className = 'password-strength strength-strong';
        strengthDiv.textContent = 'Strong password';
    }
});

// Form validation
document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
    const password = document.querySelector('input[name="password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>
<?php  include('../includes/footer.php'); ?>