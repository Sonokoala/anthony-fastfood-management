<?php
require_once 'auth.php';
checkAuth();
require_once "db.php";

$error = '';
$success = '';

// Get staff data if ID is provided
$staff = null;
if (isset($_GET['id'])) {
    $stmt = $connection->prepare("SELECT * FROM staff WHERE staffID = ?");
    $stmt->bind_param("s", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $staffID = $_POST['staffID'] ?? '';
    
    if (!empty($staffID)) {
        try {
            // トランザクション開始
            $connection->begin_transaction();
            
            // 最初にavailabilityテーブルから関連レコードを削除
            $stmt1 = $connection->prepare("DELETE FROM availability WHERE staffID = ?");
            $stmt1->bind_param("s", $staffID);
            $stmt1->execute();
            
            // 次にスタッフを削除
            $stmt2 = $connection->prepare("DELETE FROM staff WHERE staffID = ?");
            $stmt2->bind_param("s", $staffID);
            
            if ($stmt2->execute()) {
                // トランザクションをコミット
                $connection->commit();
                $success = 'Staff deleted successfully';
            } else {
                throw new Exception($stmt2->error);
            }
        } catch (Exception $e) {
            // エラーが発生した場合はロールバック
            $connection->rollback();
            $error = 'Deletion failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Staff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php require_once "header.php"; ?>
<div class="container mt-5" style="max-width: 600px;">
    <h2>Delete Staff</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php 
    // Get all staff members
    $staffList = [];
    $result = $connection->query("SELECT staffID, name, email, roleID FROM staff ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $staffList[] = $row;
        }
    }
    
    if ($staff): ?>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($staff['name']) ?></h5>
            <p class="card-text">
                ID: <?= htmlspecialchars($staff['staffID']) ?><br>
                Email: <?= htmlspecialchars($staff['email']) ?><br>
                Mobile: <?= htmlspecialchars($staff['mob']) ?><br>
                Address: <?= htmlspecialchars($staff['address']) ?>
            </p>
        </div>
    </div>

    <form method="post">
        <input type="hidden" name="staffID" value="<?= htmlspecialchars($staff['staffID']) ?>">
        <p class="text-danger">Are you sure you want to delete this staff member?</p>
        <button type="submit" name="confirm_delete" class="btn btn-danger">Confirm Delete</button>
        <a href="staff_delete.php" class="btn btn-secondary">Cancel</a>
    </form>
    <?php else: ?>
        <h4>Select Staff to Delete</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['name']) ?></td>
                        <td><?= htmlspecialchars($staff['email']) ?></td>
                        <td><?= htmlspecialchars($staff['roleID']) ?></td>
                        <td>
                            <a href="staff_delete.php?id=<?= $staff['staffID'] ?>" 
                               class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
