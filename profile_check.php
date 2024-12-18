<?php
session_start();
require_once 'config/database.php';

// Function to check profile completion
function isProfileComplete($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, phone_number, address, bio
        FROM user_profiles
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        return false;
    }
    
    // Check if any required field is empty
    foreach ($profile as $field => $value) {
        if (empty(trim($value))) {
            return false;
        }
    }
    
    return true;
}

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

// Check if profile exists and is complete
if (!isProfileComplete($pdo, $_SESSION['user_id'])) {
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $fields = [
            'first_name' => trim(strip_tags($_POST['first_name'])),
            'last_name' => trim(strip_tags($_POST['last_name'])),
            'phone_number' => trim(strip_tags($_POST['phone_number'])),
            'address' => trim(strip_tags($_POST['address'])),
            'bio' => trim(strip_tags($_POST['bio']))
        ];
        
        // Validate all fields are filled
        $isEmpty = false;
        foreach ($fields as $field => $value) {
            if (empty($value)) {
                $isEmpty = true;
                $error = "All fields are required. Please complete your profile.";
                break;
            }
        }
        
        if (!$isEmpty) {
            try {
                // Check if profile exists
                $checkStmt = $pdo->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
                $checkStmt->execute([$_SESSION['user_id']]);
                
                if ($checkStmt->fetch()) {
                    // Update existing profile
                    $stmt = $pdo->prepare("
                        UPDATE user_profiles 
                        SET first_name = ?, last_name = ?, phone_number = ?, address = ?, bio = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([
                        $fields['first_name'],
                        $fields['last_name'],
                        $fields['phone_number'],
                        $fields['address'],
                        $fields['bio'],
                        $_SESSION['user_id']
                    ]);
                } else {
                    // Insert new profile
                    $stmt = $pdo->prepare("
                        INSERT INTO user_profiles 
                        (user_id, first_name, last_name, phone_number, address, bio)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_SESSION['user_id'],
                        $fields['first_name'],
                        $fields['last_name'],
                        $fields['phone_number'],
                        $fields['address'],
                        $fields['bio']
                    ]);
                }
                
                $success = "Profile updated successfully! Redirecting to dashboard...";
                header("refresh:2;url=dashboard.php");
                
            } catch(PDOException $e) {
                error_log("Profile Update Error: " . $e->getMessage());
                $error = "An error occurred while updating your profile. Please try again.";
            }
        }
    }
    
    // Fetch existing profile data if any
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="profile-container">
            <h2 class="text-center mb-4">Complete Your Profile</h2>
            <p class="text-muted text-center mb-4">Please complete your profile before continuing to the dashboard.</p>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="first_name" class="form-label required-field">First Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="first_name" 
                           name="first_name" 
                           value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="last_name" class="form-label required-field">Last Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="last_name" 
                           name="last_name" 
                           value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="phone_number" class="form-label required-field">Phone Number</label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone_number" 
                           name="phone_number" 
                           value="<?php echo htmlspecialchars($profile['phone_number'] ?? ''); ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label required-field">Address</label>
                    <textarea class="form-control" 
                              id="address" 
                              name="address" 
                              rows="2" 
                              required><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="bio" class="form-label required-field">Bio</label>
                    <textarea class="form-control" 
                              id="bio" 
                              name="bio" 
                              rows="3" 
                              required><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Save Profile</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit(); // Stop further execution
}

// If profile is complete, continue to dashboard
// Your existing dashboard code goes here...
?>