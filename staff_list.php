<?php
require_once "auth.php";
checkAuth([1, 2]);
require_once "db.php";

// Get all staff members
$query = "SELECT s.*, r.name as role_name 
          FROM staff s 
          LEFT JOIN role r ON s.roleID = r.roleID 
          ORDER BY s.name";
$result = $connection->query($query);
$staff_list = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff List - Anthony's Fast Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Staff List</h2>
            <a href="staff_create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Staff
            </a>
        </div>

        <?php if (empty($staff_list)): ?>
            <div class="alert alert-info">No staff members found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_list as $staff): ?>
                            <tr>
                                <td><?= htmlspecialchars($staff['name']) ?></td>
                                <td><?= htmlspecialchars($staff['role_name'] ?? 'Not assigned') ?></td>
                                <td><?= htmlspecialchars($staff['email']) ?></td>
                                <td><?= htmlspecialchars($staff['mob']) ?></td>
                                <td>
                                    <a href="staff_update.php?id=<?= htmlspecialchars($staff['staffID']) ?>" 
                                       class="btn btn-sm btn-outline-primary me-1" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="staff_delete.php?id=<?= htmlspecialchars($staff['staffID']) ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this staff member?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="/anthonyfastfood/index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 