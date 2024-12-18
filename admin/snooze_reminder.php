<?php
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $taskId = $_POST['task_id'];
        $snoozeMinutes = $_POST['snooze_minutes'];
        
        // Update reminder time
        $sql = "UPDATE task_reminders 
                SET reminder_time = DATE_ADD(NOW(), INTERVAL :snooze_minutes MINUTE),
                    is_sent = 0
                WHERE task_id = :task_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'task_id' => $taskId,
            'snooze_minutes' => $snoozeMinutes
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>