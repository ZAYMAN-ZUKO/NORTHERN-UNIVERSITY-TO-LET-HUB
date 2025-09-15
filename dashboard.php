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

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get user properties (for owners)
$user_properties = [];
$rent_requests = [];
$bookmarks = [];

if ($user_type == 'owner') {
    $conn = (new Database())->getConnection();
    $stmt = $conn->prepare("SELECT * FROM properties WHERE owner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $user_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rent_requests = getRentRequests($user_id);
} else {
    $bookmarks = getUserBookmarks($user_id);
    $sent_requests = getSentRequests($user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <?php if ($_SESSION['user_type'] == 'owner'): ?>
                                <li><a class="dropdown-item" href="add-property.php">Add Property</a></li>
                                <li><a class="dropdown-item" href="requests.php">Rent Requests</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="sent-requests.php">Sent Requests</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="bookmarks.php">Bookmarks</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5" style="margin-top: 76px;">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Welcome back, <?php echo $_SESSION['full_name']; ?>!</h1>
                <p class="text-muted mb-4">Manage your <?php echo $user_type == 'owner' ? 'properties and requests' : 'bookmarks and searches'; ?> from here.</p>
            </div>
        </div>

        <?php if ($user_type == 'owner'): ?>
            <!-- Owner Dashboard -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($user_properties); ?></h3>
                        <p>Total Properties</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count(array_filter($user_properties, function($p) { return $p['is_available']; })); ?></h3>
                        <p>Available Properties</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($rent_requests); ?></h3>
                        <p>Total Requests</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count(array_filter($rent_requests, function($r) { return $r['status'] == 'pending'; })); ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
            </div>

            <!-- Recent Rent Requests -->
            <?php if (!empty($rent_requests)): ?>
                <div class="dashboard-card">
                    <h4 class="mb-4">Recent Rent Requests</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Renter</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($rent_requests, 0, 5) as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['property_title']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($request['renter_name']); ?><br>
                                            <small class="text-muted"><?php echo $request['renter_phone']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($request['message'], 0, 50)); ?>
                                            <?php if (strlen($request['message']) > 50): ?>...<?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'approved' ? 'success' : 'danger'); ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($request['requested_at'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] == 'pending'): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="approve-request.php?id=<?php echo $request['id']; ?>&status=approved" 
                                                       class="btn btn-success btn-sm">Approve</a>
                                                    <a href="approve-request.php?id=<?php echo $request['id']; ?>&status=rejected" 
                                                       class="btn btn-danger btn-sm">Reject</a>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">Responded</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="requests.php" class="btn btn-outline-primary">View All Requests</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Properties -->
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>My Properties</h4>
                    <a href="add-property.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Property
                    </a>
                </div>
                
                <?php if (!empty($user_properties)): ?>
                    <div class="row">
                        <?php foreach (array_slice($user_properties, 0, 6) as $property): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card property-card h-100">
                                    <div class="position-relative">
                                        <img src="<?php echo getPropertyImage($property['id']); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        <div class="property-badge">
                                            <?php echo ucfirst($property['property_type']); ?>
                                        </div>
                                        <div class="property-status">
                                            <span class="badge bg-<?php echo $property['is_available'] ? 'success' : 'danger'; ?>">
                                                <?php echo $property['is_available'] ? 'Available' : 'Rented'; ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($property['address']); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="rent-price">
                                                <span class="h6 text-primary">৳<?php echo number_format($property['rent_amount']); ?></span>
                                                <small class="text-muted">/<?php echo $property['rent_period']; ?></small>
                                            </div>
                                            <div class="property-stats">
                                                <small class="text-muted">
                                                    <i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> Bed
                                                    <i class="fas fa-bath ms-2 me-1"></i><?php echo $property['bathrooms']; ?> Bath
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="d-grid gap-2">
                                            <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">View Details</a>
                                            <div class="btn-group">
                                                <a href="edit-property.php?id=<?php echo $property['id']; ?>" 
                                                   class="btn btn-outline-secondary btn-sm">Edit</a>
                                                <a href="delete-property.php?id=<?php echo $property['id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="my-properties.php" class="btn btn-outline-primary">View All Properties</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-home fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No properties yet</h5>
                        <p class="text-muted">Start by adding your first property to get started.</p>
                        <a href="add-property.php" class="btn btn-primary">Add Your First Property</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Renter Dashboard -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($bookmarks); ?></h3>
                        <p>Bookmarked Properties</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($sent_requests); ?></h3>
                        <p>Sent Requests</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count(array_filter($sent_requests, function($r) { return $r['status'] == 'approved'; })); ?></h3>
                        <p>Approved Requests</p>
                    </div>
                </div>
            </div>

            <!-- Recent Sent Requests -->
            <?php if (!empty($sent_requests)): ?>
                <div class="dashboard-card">
                    <h4 class="mb-4">Recent Sent Requests</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Sent Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($sent_requests, 0, 5) as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['property_title']); ?></strong><br>
                                            <small class="text-muted">৳<?php echo number_format($request['rent_amount']); ?>/<?php echo $request['rent_period']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($request['owner_name']); ?><br>
                                            <small class="text-muted"><?php echo $request['owner_phone']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'approved' ? 'success' : 'danger'); ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($request['requested_at'])); ?>
                                        </td>
                                        <td>
                                            <a href="property-details.php?id=<?php echo $request['property_id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">View Property</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="sent-requests.php" class="btn btn-outline-primary">View All Sent Requests</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bookmarked Properties -->
            <div class="dashboard-card">
                <h4 class="mb-4">My Bookmarked Properties</h4>
                
                <?php if (!empty($bookmarks)): ?>
                    <div class="row">
                        <?php foreach (array_slice($bookmarks, 0, 6) as $property): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card property-card h-100">
                                    <div class="position-relative">
                                        <img src="<?php echo getPropertyImage($property['id']); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        <div class="property-badge">
                                            <?php echo ucfirst($property['property_type']); ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($property['address']); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="rent-price">
                                                <span class="h6 text-primary">৳<?php echo number_format($property['rent_amount']); ?></span>
                                                <small class="text-muted">/<?php echo $property['rent_period']; ?></small>
                                            </div>
                                            <div class="property-stats">
                                                <small class="text-muted">
                                                    <i class="fas fa-bed me-1"></i><?php echo $property['bedrooms']; ?> Bed
                                                    <i class="fas fa-bath ms-2 me-1"></i><?php echo $property['bathrooms']; ?> Bath
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                           class="btn btn-outline-primary w-100">View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="bookmarks.php" class="btn btn-outline-primary">View All Bookmarks</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No bookmarked properties</h5>
                        <p class="text-muted">Start browsing properties and bookmark your favorites.</p>
                        <a href="properties.php" class="btn btn-primary">Browse Properties</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
