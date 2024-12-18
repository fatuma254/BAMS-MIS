<?php
// update_task.php
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    try {
        $sql = "UPDATE tasks SET 
                title = :title,
                description = :description,
                start_date = :start_date,
                start_time = :start_time,
                end_date = :end_date,
                end_time = :end_time,
                priority = :priority,
                status = :status,
                assigned_to = :assigned_to,
                reminder_before = :reminder_before
                WHERE task_id = :task_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'task_id' => $_POST['task_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'start_date' => $_POST['start_date'],
            'start_time' => $_POST['start_time'],
            'end_date' => $_POST['end_date'] ?: null,
            'end_time' => $_POST['end_time'] ?: null,
            'priority' => $_POST['priority'],
            'status' => $_POST['status'],
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'reminder_before' => $_POST['reminder_before']
        ]);

        // Update reminder
        $reminderTime = date('Y-m-d H:i:s', strtotime("$_POST[start_date] $_POST[start_time] - $_POST[reminder_before] minutes"));
        $reminderSql = "UPDATE task_reminders SET reminder_time = :reminder_time, is_sent = FALSE 
                       WHERE task_id = :task_id";
        $reminderStmt = $conn->prepare($reminderSql);
        $reminderStmt->execute([
            'task_id' => $_POST['task_id'],
            'reminder_time' => $reminderTime
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating task: ' . $e->getMessage()]);
    }
}


