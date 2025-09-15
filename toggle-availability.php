<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Check if user is logged in and is owner
if (!isLoggedIn() || $_SESSION['user_type'] != 'owner') {
    header('Location: login.php');
    exit();
}

$property_id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? 0;

if ($property_id && in_array($status, ['0', '1'])) {
    try {
        $conn = (new Database())->getConnection();
        
        // Check if property belongs to user
        $stmt = $conn->prepare("SELECT id FROM properties WHERE id = ? AND owner_id = ?");
        $stmt->execute([$property_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Update availability status
            $stmt = $conn->prepare("UPDATE properties SET is_available = ? WHERE id = ? AND owner_id = ?");
            $stmt->execute([$status, $property_id, $_SESSION['user_id']]);
            
            $status_text = $status ? 'available' : 'rented';
            $_SESSION['success'] = "Property marked as $status_text successfully.";
        } else {
            $_SESSION['error'] = 'Property not found or you do not have permission to modify it.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Failed to update property status. Please try again.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: my-properties.php');
exit();
?>
