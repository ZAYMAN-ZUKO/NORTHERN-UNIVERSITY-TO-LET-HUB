<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Check if user is logged in and is owner
if (!isLoggedIn() || $_SESSION['user_type'] != 'owner') {
    header('Location: login.php');
    exit();
}

$property_id = $_GET['id'] ?? 0;

if ($property_id) {
    try {
        $conn = (new Database())->getConnection();
        
        // Check if property belongs to user
        $stmt = $conn->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ?");
        $stmt->execute([$property_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Delete property images first
            $stmt = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
            $stmt->execute([$property_id]);
            
            // Delete related records
            $stmt = $conn->prepare("DELETE FROM rent_requests WHERE property_id = ?");
            $stmt->execute([$property_id]);
            
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE property_id = ?");
            $stmt->execute([$property_id]);
            
            $stmt = $conn->prepare("DELETE FROM reviews WHERE property_id = ?");
            $stmt->execute([$property_id]);
            
            // Delete property
            $stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND owner_id = ?");
            $stmt->execute([$property_id, $_SESSION['user_id']]);
            
            $_SESSION['success'] = 'Property deleted successfully.';
        } else {
            $_SESSION['error'] = 'Property not found or you do not have permission to delete it.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to delete property. Please try again.';
    }
} else {
    $_SESSION['error'] = 'Invalid property ID.';
}

header('Location: my-properties.php');
exit();
?>
