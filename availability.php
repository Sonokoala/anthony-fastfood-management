<?php
require_once "db.php";
require_once "auth.php";

// Get staff information
$email = $_SESSION['email'];
$stmt = $connection->prepare("SELECT staffID, name, roleID FROM staff WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
$staffID = $staff['staffID'];
$staffName = $staff['name'];
$roleID = $staff['roleID'];

// Get already selected rosterIDs
$selected = [];
$res = $connection->prepare("SELECT rosterID FROM availability WHERE staffID = ?");
$res->bind_param("i", $staffID);
$res->execute();
$resSet = $res->get_result();
while ($row = $resSet->fetch_assoc()) {
    $selected[] = $row['rosterID'];
}

// Get available rosters (filtered by roleID)
$sql = "SELECT r.rosterID, r.dateTimeFrom, r.dateTimeTo
        FROM roster r
        JOIN rosterrole rr ON r.rosterID = rr.rosterID
        WHERE rr.roleID = ?
        ORDER BY r.dateTimeFrom";
$rosterStmt = $connection->prepare($sql);
$rosterStmt->bind_param("i", $roleID);
$rosterStmt->execute();
$rosterResult = $rosterStmt->get_result();

// Error message variable
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checked = isset($_POST['roster']) ? $_POST['roster'] : [];
    // Delete all existing availability
    $del = $connection->prepare("DELETE FROM availability WHERE staffID = ?");
    if (!$del) {
        $errorMessage = "DB error (delete): " . $connection->error;
    } else {
        $del->bind_param("i", $staffID);
        $del->execute();
    // Insert new availability
        if (!empty($checked)) {
            $ins = $connection->prepare("INSERT INTO availability (staffID, rosterID) VALUES (?, ?)");
            if (!$ins) {
                $errorMessage = "DB error (insert): " . $connection->error;
            } else {
                foreach ($checked as $rid) {
                    $ins->bind_param("ii", $staffID, $rid);
                    $ins->execute();
                }
            }
        }
        if ($errorMessage === '') {
            $_SESSION['success_message'] = "Availability submitted!";
            echo '<div class="alert alert-success">Availability successfully updated!</div>';
            echo '<meta http-equiv="refresh" content="3;url=index.php">';
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Availability Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <script>
  $(document).ready(function(){
    // Bootstrap 4 dropdown initialization
    $('.dropdown-toggle').dropdown();
    
    // Manual click handler for dropdown toggles
    $('.dropdown-toggle').on('click', function(e) {
      e.preventDefault();
      $(this).dropdown('toggle');
    });
    
    console.log('Dropdown initialization in availability.php');
  });
  </script>
  <style>
    .center-btns { display: flex; justify-content: center; gap: 60px; margin-top: 32px; }
    .center-btns button, .center-btns a { min-width: 160px; }
    .table th, .table td { vertical-align: middle; }
  </style>
</head>
<body>
<?php
// Load appropriate navbar based on user role
if (isset($_SESSION['roleID'])) {
    if ($_SESSION['roleID'] == 1 || $_SESSION['roleID'] == 2) {
        require_once 'navbar_admin.php';
    } else {
        require_once 'navbar_user.php';
    }
} else {
    require_once 'navbar.php';
}
?>
<div class="container my-5">
  <h2 class="mb-4">Staff Availability Management</h2>
  
  <?php if ($roleID == 2): // Only managers can see all staff availability ?>
  <h4 class="mb-4">All Staff Availability</h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Staff ID</th>
        <th>Name</th>
        <th>Available Dates</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Get all staff availability
      $sql = "SELECT s.staffID, s.name, GROUP_CONCAT(r.dateTimeFrom SEPARATOR ', ') AS available_dates
              FROM staff s
              LEFT JOIN availability a ON s.staffID = a.staffID
              LEFT JOIN roster r ON a.rosterID = r.rosterID
              GROUP BY s.staffID, s.name";
      $result = $connection->query($sql);
      while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['staffID']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['available_dates'] ?? 'None') ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <h4 class="mb-4 mt-5">My Availability</h4>
  <div class="mb-4">
    <h5>My Current Availability:</h5>
    <ul>
      <?php
      // Get user's own availability
      $sql = "SELECT r.dateTimeFrom, r.dateTimeTo 
              FROM availability a
              JOIN roster r ON a.rosterID = r.rosterID
              WHERE a.staffID = ?";
      $stmt = $connection->prepare($sql);
      $stmt->bind_param("i", $staffID);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo '<li>' . htmlspecialchars($row['dateTimeFrom']) . ' to ' . htmlspecialchars($row['dateTimeTo']) . '</li>';
          }
      } else {
          echo '<li>No availability scheduled</li>';
      }
      ?>
    </ul>
  </div>

  <h4 class="mb-4">Update Availability</h4>
  <?php if (!empty($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($errorMessage) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">SELECT YOUR AVAILABLE WORK TIMES</label>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Update</th>
            <th>Staff ID</th>
            <th>Name</th>
            <th>Roaster ID</th>
            <th>Start</th>
            <th>End</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rosterResult as $row): ?>
          <tr>
            <td>
              <input type="checkbox" name="roster[]" value="<?= $row['rosterID'] ?>"
                <?= in_array($row['rosterID'], $selected) ? 'checked' : '' ?>>
            </td>
            <td><?= htmlspecialchars($staffID) ?></td>
            <td><?= htmlspecialchars($staffName) ?></td>
            <td><?= htmlspecialchars($row['rosterID']) ?></td>
            <td><?= htmlspecialchars($row['dateTimeFrom']) ?></td>
            <td><?= htmlspecialchars($row['dateTimeTo']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="center-btns">
      <button type="submit" class="btn btn-dark">Submit</button>
      <a href="index.php" class="btn btn-dark">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
