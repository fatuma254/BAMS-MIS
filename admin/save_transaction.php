
<!-- save_transaction.php -->
<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO transactions (transaction_date, type, category, amount, description)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['transaction_date'],
            $_POST['type'],
            $_POST['category'],
            $_POST['amount'],
            $_POST['description']
        ]);
        
        header("Location: finance.php?msg=saved");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>