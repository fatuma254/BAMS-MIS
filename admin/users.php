
<?php 
ob_start();
      require_once '../profile_check.php';
      include('../includes/topbar.php');
      include('../includes/sidebar.php');


// Delete user
if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header("Location: users.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Fetch users with their profiles
$stmt = $pdo->query("
    SELECT u.*, up.first_name, up.last_name, up.phone_number, up.address 
    FROM users u 
    LEFT JOIN user_profiles up ON u.id = up.user_id 
    
    ORDER BY u.created_at DESC
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <button class="btn btn-primary" onclick="window.location.href='add_user.php'">
            <i class="bi bi-plus-circle"></i> Add New User
        </button>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Employee deleted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($employees as $employee): ?>
                            <tr>
                                <td><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                                <td><?= htmlspecialchars($employee['email']) ?></td>
                                <td><?= htmlspecialchars($employee['phone_number'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $employee['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $employee['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($employee['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" onclick="viewEmployee(<?= $employee['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="window.location.href='edit_user.php?id=<?= $employee['id'] ?>'">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteEmployee(<?= $employee['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Employee View Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDetails">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
function viewEmployee(id) {
    const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
    modal.show();
    
    fetch(`get_user.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('employeeDetails').innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Personal Information</h6>
                        <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>Phone:</strong> ${data.phone_number || 'N/A'}</p>
                        <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                        <hr>
                        <h6 class="card-subtitle mb-2 text-muted">Account Information</h6>
                        <p><strong>Status:</strong> ${data.is_active ? 'Active' : 'Inactive'}</p>
                        <p><strong>Last Login:</strong> ${data.last_login || 'Never'}</p>
                        <p><strong>Profile Complete:</strong> ${data.profile_complete ? 'Yes' : 'No'}</p>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('employeeDetails').innerHTML = 'Error loading employee details.';
        });
}

function deleteEmployee(id) {
    if(confirm('Are you sure you want to delete this employee?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="delete_user" value="1"><input type="hidden" name="user_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php  include('../includes/footer.php'); ?>