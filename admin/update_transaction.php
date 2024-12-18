<?php
require_once '../config/database.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $transaction_date = filter_input(INPUT_POST, 'transaction_date', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Validate inputs
    if ($id === false || empty($type) || empty($transaction_date) || empty($category) || $amount === false) {
        $_SESSION['error'] = 'Invalid input. Please check all fields.';
        header('Location: index.php');
        exit();
    }

    try {
        // Prepare and execute update statement
        $stmt = $pdo->prepare("
            UPDATE transactions 
            SET transaction_date = ?, 
                category = ?, 
                amount = ?, 
                description = ?
            WHERE id = ? AND type = ?
        ");
        
        $result = $stmt->execute([
            $transaction_date, 
            $category, 
            $amount, 
            $description, 
            $id, 
            $type
        ]);

        if ($result) {
            $_SESSION['success'] = 'Transaction updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update transaction.';
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }

    // Redirect back to the main page
    header('Location: finance.php');
    exit();
} else {
    // If accessed directly without POST, redirect to main page
    header('Location: finance.php');
    exit();
}
?>