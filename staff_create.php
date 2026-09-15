<?php
require_once 'auth.php';
checkAuth();
require_once "db.php";
require_once "validation.php";

// Initialize variables
$error = '';
$success = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log POST data for debugging
    error_log("POST data: " . print_r($_POST, true));
    
    // Validate all inputs
    $validations = [
        'name' => validateString($_POST['name'] ?? '', 2, 30),
        'email' => validateEmail($_POST['email'] ?? ''),
        'mob' => validatePhone($_POST['mob'] ?? ''),
        'address' => validateString($_POST['address'] ?? '', 5, 100),
        'dateOfBirth' => validateDate($_POST['dateOfBirth'] ?? '', '1950-01-01', date('Y-m-d')),
        'password' => validatePassword($_POST['password'] ?? ''),
        'roleID' => validateId($_POST['roleID'] ?? '')
    ];
    
    // Log validation results
    error_log("Validation results: " . print_r($validations, true));
    
    // Store sanitized values for form repopulation
    foreach ($validations as $field => $result) {
        $formData[$field] = $result['valid'] ? $result['value'] : ($_POST[$field] ?? '');
    }
    
    // Check for any validation errors
    $errors = array_filter($validations, function($result) {
        return !$result['valid'];
    });
    
    if (!empty($errors)) {
            $error = "Please fix the following errors:<ul>";
            foreach ($errors as $field => $result) {
                $error .= "<li>" . htmlspecialchars($result['error']) . "</li>";
            }
            $error .= "</ul>";
    } else {
        try {
            // Hash the password
            $passwordHash = password_hash($validations['password']['value'], PASSWORD_BCRYPT);
            
            // Log the SQL parameters
            error_log("SQL Parameters: " . print_r([
                'name' => $validations['name']['value'],
                'email' => $validations['email']['value'],
                'mob' => $validations['mob']['value'],
                'address' => $validations['address']['value'],
                'dateOfBirth' => $validations['dateOfBirth']['value'],
                'roleID' => $validations['roleID']['value']
            ], true));
            
            // Prepare the SQL statement
            $stmt = $connection->prepare(
                "INSERT INTO staff (name, email, mob, address, dateOfBirth, password_hash, roleID) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            if (!$stmt) {
                throw new Exception("Database error: " . $connection->error);
            }
            
            // Bind parameters
            $stmt->bind_param(
                "ssssssi",
                $validations['name']['value'],
                $validations['email']['value'],
                $validations['mob']['value'],
                $validations['address']['value'],
                $validations['dateOfBirth']['value'],
                $passwordHash,
                $validations['roleID']['value']
            );
            
            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Error creating staff: " . $stmt->error);
            }
            
            // Log success
            error_log("New staff member created: " . $validations['email']['value']);
            
            $success = 'Staff registration completed successfully';
            $formData = []; // Clear form on success
            
            $stmt->close();
        } catch (Exception $e) {
            error_log("Staff creation error: " . $e->getMessage());
            $error = "An error occurred while creating the staff member: " . $e->getMessage();
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration - Anthony's Fast Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once "navbar.php"; ?>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Register New Staff</h2>
                        
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
                        
                        <form method="post" autocomplete="off" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email*</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mob" class="form-label">Mobile Number*</label>
                                    <input type="tel" class="form-control" id="mob" name="mob" 
                                           value="<?= htmlspecialchars($formData['mob'] ?? '') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="dateOfBirth" class="form-label">Date of Birth*</label>
<input type="text" 
       class="form-control" 
       id="dateOfBirth" 
       name="dateOfBirth" 
       value="<?= htmlspecialchars($formData['dateOfBirth'] ?? '') ?>" 
       placeholder="YYYY-MM-DD"
       pattern="\d{4}-\d{2}-\d{2}"
       required>
                                    <div class="form-text">Please select your date of birth (Format: YYYY-MM-DD)</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address*</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?= htmlspecialchars($formData['address'] ?? '') ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password*</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">
                                        Password must be at least 8 characters long and contain uppercase, lowercase, 
                                        number, and special character.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="roleID" class="form-label">Role*</label>
                                    <select class="form-select" id="roleID" name="roleID" required>
                                        <option value="">Select a role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= htmlspecialchars($role['roleID']) ?>"
                                                    <?= (($formData['roleID'] ?? '') == $role['roleID'] ? 'selected' : '') ?>>
                                                <?= htmlspecialchars($role['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Create Staff</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var dateInput = document.getElementById('dateOfBirth');
        dateInput.addEventListener('change', function() {
            if(this.value) {
                var date = new Date(this.value);
                var year = date.getFullYear();
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var day = String(date.getDate()).padStart(2, '0');
                this.value = year + '-' + month + '-' + day;
            }
        });
    });
    </script>
</body>
</html>
