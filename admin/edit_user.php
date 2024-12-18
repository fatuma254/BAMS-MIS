
<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');


if(isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("
        SELECT u.*, up.first_name, up.last_name, up.phone_number, up.address, up.bio
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Update users table
        $stmt = $pdo->prepare("
            UPDATE users 
            SET is_active = ?, profile_complete = ?
            WHERE id = ?
        ");
        $stmt->execute([
            isset($_POST['is_active']) ? 1 : 0,
            isset($_POST['profile_complete']) ? 1 : 0,
            $_POST['id']
        ]);
        
        // Update user_profiles table
        $stmt = $pdo->prepare("
            UPDATE user_profiles 
            SET first_name = ?, last_name = ?, phone_number = ?, address = ?, bio = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['phone_number'],
            $_POST['address'],
            $_POST['bio'],
            $_POST['id']
        ]);
        
        $pdo->commit();
        header("Location: users.php?msg=updated");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
</head>
<body>

<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Employee</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $employee['id'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" 
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone_number" 
                                   value="<?= htmlspecialchars($employee['phone_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($employee['address']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="3"><?= htmlspecialchars($employee['bio']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       <?= $employee['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label">Active Account</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="profile_complete" 
                                       <?= $employee['profile_complete'] ? 'checked' : '' ?>>
                                <label class="form-check-label">Profile Complete</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='users.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php  include('../includes/footer.php'); ?>