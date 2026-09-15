<?php
require_once "db.php";
require_once 'header.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Get staff information
$result = $connection->query("SELECT staffID, name, email, roleID FROM staff ORDER BY staffID");
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">Welcome to Anthony's Fast Food Staff Management System</h1>
        
        <div class="row">
            <?php if (isset($_SESSION['roleID']) && ($_SESSION['roleID'] == 1 || $_SESSION['roleID'] == 2)): ?>
                <!-- Manager/Supervisor Options -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Staff Management</h5>
                            <p class="card-text">Add, edit, or remove staff members.</p>
                            <a href="staff_create.php" class="btn btn-primary">Manage Staff</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Availability</h5>
                        <p class="card-text">View and update your availability.</p>
                        <a href="availability.php" class="btn btn-primary">Manage Availability</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
