<?php
require_once 'auth.php';
require_once 'db.php';
// 権限チェック: 管理者(1)・マネージャー(2)は全員編集可能、一般スタッフ(3)は自分のみ
if (!isset($_SESSION['staffID']) || 
    ($_SESSION['roleID'] == 3 && $_SESSION['staffID'] != ($_GET['id'] ?? 0))) {
    header("Location: index.php");
    exit();
}
require_once 'validation.php';


// Initialize variables
$error = '';
$success = '';
$staff = null;
$formData = [];
$searchTerm = $_GET['search'] ?? '';

// Get staff members based on role
try {
    $staffList = [];
    if ($_SESSION['roleID'] <= 2) { // Admin/Manager only
        $query = "SELECT s.staffID, s.name, s.email, s.mob, r.name as role 
                  FROM staff s 
                  LEFT JOIN role r ON s.roleID = r.roleID";
    } else { // Regular staff can only see themselves
        $query = "SELECT s.staffID, s.name, s.email, s.mob, r.name as role 
                  FROM staff s 
                  LEFT JOIN role r ON s.roleID = r.roleID
                  WHERE s.staffID = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $_SESSION['staffID']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $staffList[] = $row;
        }
    }
    
    // For regular staff, automatically get their own data
    if ($_SESSION['roleID'] == 3 && !isset($_GET['id'])) {
        header("Location: staff_update.php?id=" . $_SESSION['staffID']);
        exit();
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $staffList[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching staff list: " . $e->getMessage());
    $error = "Error loading staff list. Please try again.";
}

// Get staff data if ID is provided
if (isset($_GET['id'])) {
    $idValidation = validateId($_GET['id']);
    if ($idValidation['valid']) {
        try {
            $stmt = $connection->prepare("SELECT * FROM staff WHERE staffID = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $connection->error);
            }
            
            $stmt->bind_param("i", $idValidation['value']);
            if (!$stmt->execute()) {
                throw new Exception("Query error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $staff = $result->fetch_assoc();
            
            if (!$staff) {
                throw new Exception("Staff member not found");
            }
            
            // Store current values in formData
            $formData = $staff;
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error fetching staff: " . $e->getMessage());
            $error = "Error loading staff member details. Please try again.";
        }
    } else {
        $error = "Invalid staff ID provided";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate all inputs
    $validations = [
        'staffID' => validateId($_POST['staffID'] ?? ''),
        'name' => validateString($_POST['name'] ?? '', 2, 30),
        'email' => validateEmail($_POST['email'] ?? ''),
        'mob' => validatePhone($_POST['mob'] ?? ''),
        'address' => validateString($_POST['address'] ?? '', 5, 100)
    ];
    
    // Store sanitized values for form repopulation
    foreach ($validations as $field => $result) {
        $formData[$field] = $result['valid'] ? $result['value'] : ($_POST[$field] ?? '');
    }
    
    // Check for any validation errors
    $errors = array_filter($validations, function($result) {
        return !$result['valid'];
    });
    
    if (!empty($errors)) {
        $error = "Please correct the following errors:<ul>";
        foreach ($errors as $field => $result) {
            $error .= "<li>" . htmlspecialchars($result['error']) . "</li>";
        }
        $error .= "</ul>";
    } else {
        try {
            // Prepare the SQL statement
            $stmt = $connection->prepare(
                "UPDATE staff 
                 SET name = ?, email = ?, mob = ?, address = ? 
                 WHERE staffID = ?"
            );
            
            if (!$stmt) {
                throw new Exception("Database error: " . $connection->error);
            }
            
            // Bind parameters
            $stmt->bind_param(
                "ssssi",
                $validations['name']['value'],
                $validations['email']['value'],
                $validations['mob']['value'],
                $validations['address']['value'],
                $validations['staffID']['value']
            );
            
            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Error updating staff: " . $stmt->error);
            }
            
            // Log success
            error_log("Staff member updated: " . $validations['email']['value']);
            
            $success = 'Staff member updated successfully';
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Staff update error: " . $e->getMessage());
            $error = "An error occurred while updating the staff member. Please try again.";
        }
    }
}

// Get available roles for dropdown
try {
    $roles = [];
    $result = $connection->query("SELECT roleID, name FROM role ORDER BY name");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error fetching roles: " . $e->getMessage());
    $roles = [];
}

// Include header
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
  
  console.log('Staff update page dropdown initialization complete');
});
</script>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow mb-4">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Update Staff Member</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <!-- Staff Selection Section (Admin/Manager only) -->
                <?php if ($_SESSION['roleID'] <= 2): ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Select Staff Member</h4>
                        <form class="d-flex" method="get" action="">
                            <input type="search" name="search" class="form-control me-2" 
                                   placeholder="Search staff..." 
                                   value="<?= htmlspecialchars($searchTerm) ?>">
                            <button type="submit" class="btn btn-outline-primary">Search</button>
                            <?php if ($searchTerm): ?>
                                <a href="?" class="btn btn-outline-secondary ms-2">Clear</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staffList as $staffMember): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($staffMember['name']) ?></td>
                                        <td><?= htmlspecialchars($staffMember['email']) ?></td>
                                        <td><?= htmlspecialchars($staffMember['mob']) ?></td>
                                        <td><?= htmlspecialchars($staffMember['role'] ?? 'No Role') ?></td>
                                        <td>
                                            <a href="?id=<?= $staffMember['staffID'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                Select
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($staffList)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No staff members found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Update Form Section -->
                <?php if ($staff): ?>
                    <div class="mt-4">
                        <h4>Update Staff Details</h4>
                        <form method="post" class="mt-3">
                            <input type="hidden" name="staffID" value="<?= htmlspecialchars($staff['staffID']) ?>">
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mob" class="form-label">Mobile</label>
                                <input type="tel" class="form-control" id="mob" name="mob" 
                                       value="<?= htmlspecialchars($formData['mob'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" 
                                          rows="3" required><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Update Staff Member</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
