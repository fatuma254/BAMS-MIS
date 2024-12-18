
<?php 
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

    

// Fetch current user email if not in session
if (!isset($_SESSION['user_email'])) {
    $userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $userData = $userStmt->fetch();
    $_SESSION['user_email'] = $userData['email'] ?? '';
}

$success = $error = '';

// Fetch current profile data with email
$stmt = $pdo->prepare("
    SELECT p.*, u.email 
    FROM user_profiles p 
    JOIN users u ON u.id = p.user_id 
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// If no profile exists yet, get at least the email
if (!$profile) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    $profile = [
        'email' => $userData['email']
    ];
}

$success = $error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [
        'first_name' => htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'last_name' => htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'phone_number' => htmlspecialchars(trim($_POST['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'address' => htmlspecialchars(trim($_POST['address'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'bio' => htmlspecialchars(trim($_POST['bio'] ?? ''), ENT_QUOTES, 'UTF-8')
    ];
    
    // Handle password update if provided
    $password_updated = false;
    if (!empty($_POST['new_password']) && !empty($_POST['current_password'])) {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (password_verify($_POST['current_password'], $user['password'])) {
            $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password_hash, $_SESSION['user_id']]);
            $password_updated = true;
        } else {
            $error = "Current password is incorrect";
        }
    }
    
    if (empty($error)) {
        try {
            // Update profile
            $stmt = $pdo->prepare("
                INSERT INTO user_profiles 
                (user_id, first_name, last_name, phone_number, address, bio)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                phone_number = VALUES(phone_number),
                address = VALUES(address),
                bio = VALUES(bio)
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $fields['first_name'],
                $fields['last_name'],
                $fields['phone_number'],
                $fields['address'],
                $fields['bio']
            ]);
            
            // Update session data
            $_SESSION['full_name'] = $fields['first_name'] . ' ' . $fields['last_name'];
            
            $success = "Profile updated successfully" . ($password_updated ? " (including password)" : "");
        } catch(PDOException $e) {
            $error = "An error occurred while updating your profile";
        }
    }
}

// Fetch current profile data
$stmt = $pdo->prepare("
    SELECT p.*, u.email 
    FROM user_profiles p 
    JOIN users u ON u.id = p.user_id 
    WHERE p.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - BAMS</title>
    <link href="css/custom.css" rel="stylesheet">
</head>
<body >

    <div class="content-wrapper">
        <div class="row">
            <!-- Profile Summary Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Profile Summary</h5>
                        <div class="text-center mb-3">
                            <img src="<?php echo $profile['profile_image'] ?? '../assets/images/undraw_profile.svg'; ?>" 
                                 alt="Profile" 
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <p class="card-text">
                            <strong>Name:</strong> <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?><br>
                            <strong>Phone:</strong> <?php echo htmlspecialchars($profile['phone_number'] ?? ''); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Edit Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Profile</h5>
                        
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       value="<?php echo htmlspecialchars($profile['phone_number'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="2"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" 
                                          id="bio" 
                                          name="bio" 
                                          rows="3"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            </div>

                            <hr>
                            
                            <h6>Change Password (optional)</h6>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>

                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php  include('../includes/footer.php'); ?>
    <script>
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        if ((newPassword && !currentPassword) || (!newPassword && currentPassword)) {
            e.preventDefault();
            alert('Both current and new passwords are required to change password');
        }
    });
