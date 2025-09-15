<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$property_id = $_POST['property_id'] ?? 0;

if ($property_id && removeBookmark($property_id, $_SESSION['user_id'])) {
    $_SESSION['success'] = 'Property removed from bookmarks.';
} else {
    $_SESSION['error'] = 'Failed to remove bookmark.';
}

header('Location: bookmarks.php');
exit();
?>
