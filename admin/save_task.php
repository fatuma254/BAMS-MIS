<?php
// save_task.php
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'start_date' => $_POST['start_date'],
            'start_time' => $_POST['start_time'],
            'end_date' => $_POST['end_date'] ?: null,
            'end_time' => $_POST['end_time'] ?: null,
            'priority' => $_POST['priority'],
            'status' => 'Pending',
            'assigned_to' => $_POST['assigned_to'] ?? null,
            'reminder_before' => $_POST['reminder_before']
        ];

        $taskId = addTask($conn, $data);
        echo json_encode(['success' => true, 'task_id' => $taskId]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error saving task: ' . $e->getMessage()]);
    }
}

// Function to add a new task
function addTask($conn, $data) {
    $sql = "INSERT INTO tasks (title, description, start_date, start_time, end_date, end_time, priority, status, assigned_to, reminder_before) 
            VALUES (:title, :description, :start_date, :start_time, :end_date, :end_time, :priority, :status, :assigned_to, :reminder_before)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
    
    $taskId = $conn->lastInsertId();
    
    // Create reminder
    $reminderTime = date('Y-m-d H:i:s', strtotime("$data[start_date] $data[start_time] - $data[reminder_before] minutes"));
    $reminderSql = "INSERT INTO task_reminders (task_id, reminder_time) VALUES (:task_id, :reminder_time)";
    $reminderStmt = $conn->prepare($reminderSql);
    $reminderStmt->execute(['task_id' => $taskId, 'reminder_time' => $reminderTime]);
    
    return $taskId;
}
?>