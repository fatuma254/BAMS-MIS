<?php 
ob_start();
require_once '../profile_check.php';
include('../includes/topbar.php');
include('../includes/sidebar.php');

// Initialize message array
$message = [
    'type' => '',
    'text' => ''
];

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking'])) {
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['booking_id']]);
        $message = [
            'type' => 'success',
            'text' => 'Booking status updated successfully!'
        ];
    } catch (PDOException $e) {
        $message = [
            'type' => 'danger',
            'text' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Fetch all bookings with talent information
$stmt = $pdo->query("
    SELECT b.*, t.name as talent_name, t.specialty 
    FROM bookings b 
    JOIN talents t ON b.talent_id = t.id 
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
</head>
<body>
    <div class="content-wrapper">
        <h2 class="mb-4">Manage Bookings</h2>

        <?php if ($message['type'] && $message['text']): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Booking Date</th>
                    <th>Time</th>
                    <th>Client</th>
                    <th>Professional</th>
                    <th>Specialty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($booking['booking_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($booking['booking_time'])) ?></td>
                    <td>
                        <?= htmlspecialchars($booking['client_name']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($booking['client_email']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($booking['talent_name']) ?></td>
                    <td><?= htmlspecialchars($booking['specialty']) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $booking['status'] === 'confirmed' ? 'success' : 
                            ($booking['status'] === 'pending' ? 'warning' : 
                            ($booking['status'] === 'cancelled' ? 'danger' : 'info')) 
                        ?>">
                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                            <input type="hidden" name="update_booking" value="1">
                            <select name="status" class="form-select form-select-sm d-inline-block w-auto" 
                                    onchange="this.form.submit()">
                                <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>