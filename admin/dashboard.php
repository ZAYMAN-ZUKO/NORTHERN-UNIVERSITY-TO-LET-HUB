<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total properties
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties");
$stmt->execute();
$stats['total_properties'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Available properties
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties WHERE is_available = 1");
$stmt->execute();
$stats['available_properties'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total rent requests
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM rent_requests");
$stmt->execute();
$stats['total_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Pending requests
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM rent_requests WHERE status = 'pending'");
$stmt->execute();
$stats['pending_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total reviews
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews");
$stmt->execute();
$stats['total_reviews'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Recent users
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent properties
$stmt = $conn->prepare("
    SELECT p.*, u.full_name as owner_name 
    FROM properties p 
    JOIN users u ON p.owner_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent rent requests
$stmt = $conn->prepare("
    SELECT r.*, p.title as property_title, u.full_name as renter_name 
    FROM rent_requests r 
    JOIN properties p ON r.property_id = p.id 
    JOIN users u ON r.renter_id = u.id 
    ORDER BY r.requested_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-shield-alt me-2"></i>Admin Panel
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a class="nav-link" href="properties.php">
                            <i class="fas fa-home me-2"></i>Properties
                        </a>
                        <a class="nav-link" href="requests.php">
                            <i class="fas fa-envelope me-2"></i>Rent Requests
                        </a>
                        <a class="nav-link" href="reviews.php">
                            <i class="fas fa-star me-2"></i>Reviews
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <hr class="text-white">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>View Site
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Overview</h2>
                    <div class="text-muted">
                        Welcome back, <strong><?php echo $_SESSION['admin_username']; ?></strong>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h3><?php echo $stats['total_users']; ?></h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-home fa-2x mb-2"></i>
                                <h3><?php echo $stats['total_properties']; ?></h3>
                                <p class="mb-0">Total Properties</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x mb-2"></i>
                                <h3><?php echo $stats['total_requests']; ?></h3>
                                <p class="mb-0">Rent Requests</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h3><?php echo $stats['total_reviews']; ?></h3>
                                <p class="mb-0">Reviews</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Users -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Users</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_users)): ?>
                                    <?php foreach ($recent_users as $user): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo ucfirst($user['user_type']); ?> • 
                                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-<?php echo $user['is_verified'] ? 'success' : 'warning'; ?>">
                                                    <?php echo $user['is_verified'] ? 'Verified' : 'Pending'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No users found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Properties -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Properties</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_properties)): ?>
                                    <?php foreach ($recent_properties as $property): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-home text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($property['title']); ?></h6>
                                                <small class="text-muted">
                                                    by <?php echo htmlspecialchars($property['owner_name']); ?> • 
                                                    ৳<?php echo number_format($property['rent_amount']); ?>/<?php echo $property['rent_period']; ?>
                                                </small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-<?php echo $property['is_available'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $property['is_available'] ? 'Available' : 'Rented'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No properties found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Requests -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Requests</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($recent_requests)): ?>
                                    <?php foreach ($recent_requests as $request): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-envelope text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($request['property_title']); ?></h6>
                                                <small class="text-muted">
                                                    by <?php echo htmlspecialchars($request['renter_name']); ?> • 
                                                    <?php echo date('M d, Y', strtotime($request['requested_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="badge bg-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'approved' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No requests found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>