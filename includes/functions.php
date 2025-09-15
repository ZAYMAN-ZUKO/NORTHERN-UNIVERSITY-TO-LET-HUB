<?php
// Helper functions for Northern To-Let Hub

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get user information
function getUserInfo($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get featured properties
function getFeaturedProperties($limit = 6) {
    global $conn;
    $limit = (int)$limit; // Ensure it's an integer
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name as owner_name, u.phone as owner_phone
        FROM properties p 
        JOIN users u ON p.owner_id = u.id 
        WHERE p.is_available = 1 
        ORDER BY p.created_at DESC 
        LIMIT " . $limit
    );
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get property image
function getPropertyImage($property_id, $primary = true) {
    global $conn;
    $condition = $primary ? "AND is_primary = 1" : "";
    $stmt = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ? $condition ORDER BY is_primary DESC LIMIT 1");
    $stmt->execute([$property_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && file_exists($result['image_path'])) {
        return $result['image_path'];
    }
    
    // Return a placeholder image if no image exists
    return 'https://via.placeholder.com/400x300/007bff/ffffff?text=Property+Image';
}

// Get all property images
function getPropertyImages($property_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // If no images, return a placeholder
    if (empty($images)) {
        return ['https://via.placeholder.com/400x300/007bff/ffffff?text=Property+Image'];
    }
    
    return $images;
}

// Search properties
function searchProperties($filters = []) {
    global $conn;
    
    $sql = "SELECT p.*, u.full_name as owner_name, u.phone as owner_phone 
            FROM properties p 
            JOIN users u ON p.owner_id = u.id 
            WHERE p.is_available = 1";
    
    $params = [];
    
    if (!empty($filters['type'])) {
        $sql .= " AND p.property_type = ?";
        $params[] = $filters['type'];
    }
    
    if (!empty($filters['max_rent'])) {
        $sql .= " AND p.rent_amount <= ?";
        $params[] = $filters['max_rent'];
    }
    
    if (!empty($filters['min_rent'])) {
        $sql .= " AND p.rent_amount >= ?";
        $params[] = $filters['min_rent'];
    }
    
    if (!empty($filters['distance'])) {
        $sql .= " AND p.distance_from_university <= ?";
        $params[] = $filters['distance'];
    }
    
    if (!empty($filters['bedrooms'])) {
        $sql .= " AND p.bedrooms >= ?";
        $params[] = $filters['bedrooms'];
    }
    
    if (!empty($filters['keyword'])) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
        $keyword = '%' . $filters['keyword'] . '%';
        $params[] = $keyword;
        $params[] = $keyword;
        $params[] = $keyword;
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if (!empty($filters['limit'])) {
        $limit = (int)$filters['limit']; // Ensure it's an integer
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get property details
function getPropertyDetails($property_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name as owner_name, u.phone as owner_phone, u.email as owner_email
        FROM properties p 
        JOIN users u ON p.owner_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$property_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if property is bookmarked by user
function isBookmarked($property_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM bookmarks WHERE property_id = ? AND user_id = ?");
    $stmt->execute([$property_id, $user_id]);
    return $stmt->fetch() !== false;
}

// Add bookmark
function addBookmark($property_id, $user_id) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO bookmarks (property_id, user_id) VALUES (?, ?)");
        return $stmt->execute([$property_id, $user_id]);
    } catch (PDOException $e) {
        return false; // Already bookmarked
    }
}

// Remove bookmark
function removeBookmark($property_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE property_id = ? AND user_id = ?");
    return $stmt->execute([$property_id, $user_id]);
}

// Get user bookmarks
function getUserBookmarks($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name as owner_name, u.phone as owner_phone
        FROM bookmarks b
        JOIN properties p ON b.property_id = p.id
        JOIN users u ON p.owner_id = u.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get property reviews
function getPropertyReviews($property_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, u.full_name as reviewer_name
        FROM reviews r
        JOIN users u ON r.reviewer_id = u.id
        WHERE r.property_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$property_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get average rating
function getAverageRating($property_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE property_id = ?");
    $stmt->execute([$property_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Add review
function addReview($property_id, $user_id, $rating, $comment) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO reviews (property_id, reviewer_id, rating, comment) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$property_id, $user_id, $rating, $comment]);
    } catch (PDOException $e) {
        return false; // Already reviewed
    }
}

// Get rent requests for property owner
function getRentRequests($owner_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, p.title as property_title, u.full_name as renter_name, u.phone as renter_phone
        FROM rent_requests r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON r.renter_id = u.id
        WHERE p.owner_id = ?
        ORDER BY r.requested_at DESC
    ");
    $stmt->execute([$owner_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get sent requests for renter
function getSentRequests($renter_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT r.*, p.title as property_title, p.rent_amount, p.rent_period, 
               p.address, u.full_name as owner_name, u.phone as owner_phone
        FROM rent_requests r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON p.owner_id = u.id
        WHERE r.renter_id = ?
        ORDER BY r.requested_at DESC
    ");
    $stmt->execute([$renter_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Send rent request
function sendRentRequest($property_id, $renter_id, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO rent_requests (property_id, renter_id, message) VALUES (?, ?, ?)");
    return $stmt->execute([$property_id, $renter_id, $message]);
}

// Update rent request status
function updateRentRequestStatus($request_id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE rent_requests SET status = ?, responded_at = NOW() WHERE id = ?");
    return $stmt->execute([$status, $request_id]);
}

// Upload file
function uploadFile($file, $directory = 'uploads/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return false;
        default:
            return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $directory . $filename;
    
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Calculate distance between two coordinates
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}
?>
