
<!-- delete_transaction.php -->
<?php
require_once '../config/database.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header("Location: finance.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>