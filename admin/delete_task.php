<?php
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $taskId = intval($_POST['id']);

        // Check if task exists
        $checkStmt = $conn->prepare("SELECT task_id FROM tasks WHERE task_id = :task_id");
        $checkStmt->execute(['task_id' => $taskId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            exit;
        }

        $conn->beginTransaction();
        // Delete reminders first
        $stmt = $conn->prepare("DELETE FROM task_reminders WHERE task_id = :task_id");
        $stmt->execute(['task_id' => $taskId]);

        // Then delete the task
        $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = :task_id");
        $stmt->execute(['task_id' => $taskId]);
        $conn->commit();

        http_response_code(200);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>