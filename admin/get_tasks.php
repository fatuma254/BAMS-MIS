<?php
// get_tasks.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../includes/db_connection.php';

// Set proper headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Check database connection
    if (!$conn) {
        throw new PDOException("Database connection failed");
    }

    $stmt = $conn->prepare("SELECT * FROM tasks ORDER BY start_date DESC, start_time DESC");
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $formattedTasks = array_map(function($task) {
        // Make sure all date/time values exist before using them
        $startDate = $task['start_date'] ?? date('Y-m-d');
        $startTime = $task['start_time'] ?? '00:00:00';
        $endDate = $task['end_date'] ?? null;
        $endTime = $task['end_time'] ?? null;
        
        // Format the dates
        $startDateTime = date('Y-m-d\TH:i:s', strtotime("$startDate $startTime"));
        $endDateTime = ($endDate && $endTime) ? 
            date('Y-m-d\TH:i:s', strtotime("$endDate $endTime")) : null;

        return [
            'id' => $task['task_id'],
            'title' => htmlspecialchars($task['title'] ?? ''),
            'start' => $startDateTime,
            'end' => $endDateTime,
            'description' => htmlspecialchars($task['description'] ?? ''),
            'priority' => $task['priority'] ?? 'Medium',
            'status' => $task['status'] ?? 'Pending',
            'task_id' => $task['task_id'],
            'reminder_before' => $task['reminder_before'] ?? 30,
            'assigned_to' => $task['assigned_to'] ?? null
        ];
    }, $tasks);
    
    echo json_encode($formattedTasks, JSON_THROW_ON_ERROR);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_THROW_ON_ERROR);
    exit;
}
?>