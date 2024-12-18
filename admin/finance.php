<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');

// Create transactions table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_date DATE NOT NULL,
        type ENUM('expense', 'revenue') NOT NULL,
        category VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {
    echo "Table creation failed: " . $e->getMessage();
}


// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Get transactions
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE transaction_date BETWEEN ? AND ?
    ORDER BY transaction_date DESC
");
$stmt->execute([$start_date, $end_date]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_revenue = 0;
$total_expenses = 0;
foreach($transactions as $transaction) {
    if($transaction['type'] == 'revenue') {
        $total_revenue += $transaction['amount'];
    } else {
        $total_expenses += $transaction['amount'];
    }
}
$net_income = $total_revenue - $total_expenses;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAMS Financial Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
</head>
<body>

<div class="content-wrapper">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title mb-0">Financial Dashboard</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Revenue</h5>
                                    <h3>Ksh <?= number_format($total_revenue, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Expenses</h5>
                                    <h3>Ksh <?= number_format($total_expenses, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card <?= $net_income >= 0 ? 'bg-info' : 'bg-warning' ?> text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Net Income</h5>
                                    <h3>Ksh <?= number_format($net_income, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Financial Transactions</h3>
                        <div class="btn-group">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRevenueModal">
                                <i class="bi bi-plus-circle"></i> Add Revenue
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="bi bi-dash-circle"></i> Add Expense
                            </button>
                            <a href="generate_report.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-download"></i> Download Report
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form class="row mb-4" method="GET">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control flatpickr" name="start_date" 
                                   value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control flatpickr" name="end_date" 
                                   value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filter</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transactions as $transaction): ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $transaction['type'] == 'revenue' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($transaction['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($transaction['category']) ?></td>
                                    <td><?= htmlspecialchars($transaction['description']) ?></td>
                                    <td>Ksh <?= number_format($transaction['amount'], 2) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                            onclick="editTransaction(
                                                <?= $transaction['id'] ?>, 
                                                '<?= $transaction['type'] ?>', 
                                                '<?= $transaction['transaction_date'] ?>', 
                                                '<?= $transaction['category'] ?>', 
                                                <?= $transaction['amount'] ?>, 
                                                '<?= htmlspecialchars($transaction['description']) ?>'
                                            )">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteTransaction(<?= $transaction['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Revenue Modal -->
<div class="modal fade" id="addRevenueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add Revenue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="save_transaction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" value="revenue">
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control flatpickr" name="transaction_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Service Fee">Service Fee</option>
                            <option value="Commission">Commission</option>
                            <option value="Product Sales">Product Sales</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Revenue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="save_transaction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="type" value="expense">
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control flatpickr" name="transaction_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Supplies">Supplies</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Rent">Rent</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Payroll">Payroll</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="update_transaction.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="editTransactionId">
                    <input type="hidden" name="type" id="editTransactionType">
                    
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control flatpickr" name="transaction_date" id="editTransactionDate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category" id="editTransactionCategory" required>
                            <optgroup label="Revenue Categories">
                                <option value="Service Fee">Service Fee</option>
                                <option value="Commission">Commission</option>
                                <option value="Product Sales">Product Sales</option>
                                <option value="Other">Other</option>
                            </optgroup>
                            <optgroup label="Expense Categories">
                                <option value="Supplies">Supplies</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Rent">Rent</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Payroll">Payroll</option>
                                <option value="Other">Other</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" id="editTransactionAmount" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editTransactionDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php  include('../includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr(".flatpickr", {
    dateFormat: "Y-m-d"
});

function deleteTransaction(id) {
    if(confirm('Are you sure you want to delete this transaction?')) {
        window.location.href = `delete_transaction.php?id=${id}`;
    }
}

function editTransaction(id, type, date, category, amount, description) {
    // Populate edit modal with transaction details
    document.getElementById('editTransactionId').value = id;
    document.getElementById('editTransactionType').value = type;
    document.getElementById('editTransactionDate').value = date;
    document.getElementById('editTransactionCategory').value = category;
    document.getElementById('editTransactionAmount').value = amount;
    document.getElementById('editTransactionDescription').value = description;

    // Show the edit modal
    var editModal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
    editModal.show();
}
</script>
</body>
</html>


