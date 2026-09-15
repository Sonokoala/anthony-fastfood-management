<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this script from the command line.\n");
}

require __DIR__ . '/../db.php';

$email = getenv('ADMIN_EMAIL');
$password = getenv('ADMIN_PASSWORD');
$name = getenv('ADMIN_NAME') ?: 'System Administrator';

if (!$email || !$password || strlen($password) < 12) {
    exit("Set ADMIN_EMAIL and ADMIN_PASSWORD (minimum 12 characters) before running this script.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$address = 'Local setup';
$birthDate = '1990-01-01';
$mobile = '0400000000';
$roleId = 1;

$stmt = $connection->prepare(
    'INSERT INTO staff (name, address, dateOfBirth, email, mob, password_hash, roleID)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssssi', $name, $address, $birthDate, $email, $mobile, $hash, $roleId);
$stmt->execute();

echo "Administrator account created.\n";
