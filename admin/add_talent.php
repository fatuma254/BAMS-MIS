<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

$mode = isset($_GET['id']) ? 'edit' : 'add';
$talent = null;

if ($mode === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM talents WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $talent = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($mode === 'add') {
            $stmt = $pdo->prepare("
                INSERT INTO talents (name, specialty, bio, experience_years, hourly_rate, image_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
        } else {
            $stmt = $pdo->prepare("
                UPDATE talents 
                SET name = ?, specialty = ?, bio = ?, experience_years = ?, 
                    hourly_rate = ?, image_url = ?, status = ?
                WHERE id = ?
            ");
        }

        $params = [
            $_POST['name'],
            $_POST['specialty'],
            $_POST['bio'],
            $_POST['experience_years'],
            $_POST['hourly_rate'],
            $_POST['image_url'],
            $_POST['status']
        ];

        if ($mode === 'edit') {
            $params[] = $_GET['id'];
        }

        $stmt->execute($params);
        header('Location: manage_talents.php');
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'edit' ? 'Edit' : 'Add' ?> Talent Profile</title>
</head>
<body>
    <div class="content-wrapper">
        <h2><?= $mode === 'edit' ? 'Edit' : 'Add' ?> Talent Profile</h2>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?= $talent ? htmlspecialchars($talent['name']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="specialty" class="form-label">Specialty</label>
                <input type="text" class="form-control" id="specialty" name="specialty"
                       value="<?= $talent ? htmlspecialchars($talent['specialty']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="3"><?= $talent ? htmlspecialchars($talent['bio']) : '' ?></textarea>
            </div>

            <div class="mb-3">
                <label for="experience_years" class="form-label">Years of Experience</label>
                <input type="number" class="form-control" id="experience_years" name="experience_years"
                       value="<?= $talent ? htmlspecialchars($talent['experience_years']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="hourly_rate" class="form-label">Hourly Rate</label>
                <input type="number" step="0.01" class="form-control" id="hourly_rate" name="hourly_rate"
                       value="<?= $talent ? htmlspecialchars($talent['hourly_rate']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="url" class="form-control" id="image_url" name="image_url"
                       value="<?= $talent ? htmlspecialchars($talent['image_url']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="available" <?= ($talent && $talent['status'] === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="busy" <?= ($talent && $talent['status'] === 'busy') ? 'selected' : '' ?>>Busy</option>
                    <option value="unavailable" <?= ($talent && $talent['status'] === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="manage_talents.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php  include('../includes/footer.php'); ?>

</body>
</html>