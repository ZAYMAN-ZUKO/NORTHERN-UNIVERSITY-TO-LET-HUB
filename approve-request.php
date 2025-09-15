<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

// Check if user is logged in and is owner
if (!isLoggedIn() || $_SESSION['user_type'] != 'owner') {
    header('Location: login.php');
    exit();
}

$request_id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if ($request_id && in_array($status, ['approved', 'rejected'])) {
    if (updateRentRequestStatus($request_id, $status)) {
        $_SESSION['success'] = 'Request ' . $status . ' successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update request status.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: dashboard.php');
exit();
?>
