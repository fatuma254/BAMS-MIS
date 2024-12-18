<?php
require_once '../includes/db_connection.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = :task_id");
        $stmt->execute(['task_id' => $_GET['id']]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            // Ensure dates and times are properly formatted
            $task['start_date'] = $task['start_date'] ? date('Y-m-d', strtotime($task['start_date'])) : '';
            $task['end_date'] = $task['end_date'] ? date('Y-m-d', strtotime($task['end_date'])) : '';
            $task['start_time'] = $task['start_time'] ? date('H:i', strtotime($task['start_time'])) : '';
            $task['end_time'] = $task['end_time'] ? date('H:i', strtotime($task['end_time'])) : '';
            $task['status'] = $task['status'] ?: 'Pending';
            $task['description'] = $task['description'] ?: '';

            http_response_code(200);
            echo json_encode(['success' => true, 'task' => $task]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Task not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No task ID provided']);
}
?>