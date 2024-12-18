<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

// Handle deletion
if (isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM talents WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all talents
$stmt = $pdo->query("SELECT * FROM talents ORDER BY name");
$talents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Talents</title>
</head>
<body>
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Talents</h2>
            <a href="add_talent.php" class="btn btn-primary">Add New Talent</a>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Experience</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($talents as $talent): ?>
                <tr>
                    <td><?= htmlspecialchars($talent['name']) ?></td>
                    <td><?= htmlspecialchars($talent['specialty']) ?></td>
                    <td><?= htmlspecialchars($talent['experience_years']) ?> years</td>
                    <td>Ksh <?= htmlspecialchars($talent['hourly_rate']) ?>/hr</td>
                    <td>
                        <span class="badge bg-<?= $talent['status'] === 'available' ? 'success' : 
                            ($talent['status'] === 'busy' ? 'warning' : 'danger') ?>">
                            <?= ucfirst(htmlspecialchars($talent['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <a href="add_talent.php?id=<?= $talent['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this talent?');">
                            <input type="hidden" name="delete_id" value="<?= $talent['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php  include('../includes/footer.php'); ?>


</body>
</html>