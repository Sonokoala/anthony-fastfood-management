<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

require_once 'header.php';
?>

<!-- Bootstrap 4 specific scripts for this page -->
<script>
$(document).ready(function(){
  // Bootstrap 4 dropdown initialization
  $('.dropdown-toggle').dropdown();
  
  // Direct click handler for dropdown toggles
  $('.dropdown-toggle').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).dropdown('toggle');
    console.log('Dropdown clicked: ' + $(this).attr('id'));
  });
  
  console.log('Home page dropdown initialization complete');
});
</script>

<?php

// Get user information from session
$staffID = $_SESSION['staffID'] ?? '';
$email = $_SESSION['email'] ?? '';
$roleID = $_SESSION['roleID'] ?? '';

// Get staff name from database
$stmt = $connection->prepare("SELECT name FROM staff WHERE staffID = ?");
$stmt->bind_param("i", $staffID);
$stmt->execute();
$result = $stmt->get_result();
$staffName = $result->fetch_assoc()['name'] ?? 'User';
$stmt->close();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($staffName); ?></h1>
            <p class="lead">Staff Management Dashboard</p>
        </div>
    </div>

    <div class="row mt-4">
        <?php if (isset($_SESSION['roleID']) && ($_SESSION['roleID'] == 1 || $_SESSION['roleID'] == 2)): ?>
            <!-- Manager/Supervisor Options -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Staff Management</h5>
                        <p class="card-text">Manage staff members and their information.</p>
                        <a href="staff_create.php" class="btn btn-primary">Manage Staff</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Availability</h5>
                    <p class="card-text">View and update your work availability.</p>
                    <a href="availability.php" class="btn btn-primary">Manage Availability</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
