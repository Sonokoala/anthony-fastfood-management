<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth($allowedRoles = []) {
    // Redirect to login if not authenticated
    if (!isset($_SESSION['staffID'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }

    // Get current user info
    $user_id = $_SESSION['staffID'] ?? null;
    $email = $_SESSION['email'] ?? null;
    $role = $_SESSION['roleID'] ?? null;

    // Basic user info validation
    if (empty($user_id) || empty($email) || empty($role)) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Check if role is allowed
    if (!empty($allowedRoles) && !in_array($role, $allowedRoles)) {
        header('Location: index.php');
        exit();
    }
}

// Initialize authentication check
checkAuth();
?>
