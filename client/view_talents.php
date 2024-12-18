<?php 
ob_start();
require_once '../profile_check.php';
include('../includes/topbar.php');
include('../includes/sidebar.php');

// Initialize message variables
$message = [
    'type' => '',
    'text' => ''
];

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_talent'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (talent_id, client_name, client_email, booking_date, booking_time)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['talent_id'],
            $_POST['client_name'],
            $_POST['client_email'],
            $_POST['booking_date'],
            $_POST['booking_time']
        ]);
        $message = [
            'type' => 'success',
            'text' => 'Booking request submitted successfully!'
        ];
    } catch (PDOException $e) {
        $message = [
            'type' => 'danger',
            'text' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Fetch all available talents
$stmt = $pdo->query("SELECT * FROM talents WHERE status = 'available' ORDER BY name");
$talents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Beauty Professional</title>
</head>
<body>
    <div class="content-wrapper">
        <h2 class="mb-4">Available Beauty Professionals</h2>

        <?php if ($message['type'] && $message['text']): ?>
            <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($talents as $talent): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($talent['image_url']): ?>
                        <img src="<?= htmlspecialchars($talent['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($talent['name']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($talent['name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($talent['specialty']) ?></h6>
                        <p class="card-text"><?= htmlspecialchars($talent['bio']) ?></p>
                        <ul class="list-unstyled">
                            <li><strong>Experience:</strong> <?= htmlspecialchars($talent['experience_years']) ?> years</li>
                            <li><strong>Rate:</strong> Ksh <?= htmlspecialchars($talent['hourly_rate']) ?>/hr</li>
                        </ul>
                        <button class="btn btn-primary" data-bs-toggle="modal" 
                                data-bs-target="#bookingModal<?= $talent['id'] ?>">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Booking Modal -->
            <div class="modal fade" id="bookingModal<?= $talent['id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Book <?= htmlspecialchars($talent['name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST">
                                <input type="hidden" name="talent_id" value="<?= $talent['id'] ?>">
                                <input type="hidden" name="book_talent" value="1">
                                
                                <div class="mb-3">
                                    <label for="client_name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" id="client_name" name="client_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="client_email" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" id="client_email" name="client_email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="booking_date" class="form-label">Preferred Date</label>
                                    <input type="date" class="form-control" id="booking_date" name="booking_date" 
                                           min="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="booking_time" class="form-label">Preferred Time</label>
                                    <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Booking Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>

    <script>
        // Set minimum date for booking to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.min = today;
            });
        });
    </script>
</body>
</html>