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

$message = '';
$error = '';

// Handle property actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $property_id = $_POST['property_id'] ?? 0;
    
    switch ($action) {
        case 'toggle_availability':
            $stmt = $conn->prepare("SELECT is_available FROM properties WHERE id = ?");
            $stmt->execute([$property_id]);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($property) {
                $new_status = $property['is_available'] ? 0 : 1;
                $stmt = $conn->prepare("UPDATE properties SET is_available = ? WHERE id = ?");
                if ($stmt->execute([$new_status, $property_id])) {
                    $message = 'Property availability updated.';
                } else {
                    $error = 'Failed to update property availability.';
                }
            }
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
            if ($stmt->execute([$property_id])) {
                $message = 'Property deleted successfully.';
            } else {
                $error = 'Failed to delete property.';
            }
            break;
    }
}

// Get properties with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM properties");
$stmt->execute();
$total_properties = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$total_pages = ceil($total_properties / $limit);

// Get properties
$limit = (int)$limit; // Ensure it's an integer
$offset = (int)$offset; // Ensure it's an integer
$stmt = $conn->prepare("
    SELECT p.*, u.full_name as owner_name, u.email as owner_email,
           COUNT(pi.id) as image_count,
           COUNT(r.id) as review_count,
           AVG(r.rating) as avg_rating
    FROM properties p
    JOIN users u ON p.owner_id = u.id
    LEFT JOIN property_images pi ON p.id = pi.property_id
    LEFT JOIN reviews r ON p.id = r.property_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT " . $limit . " OFFSET " . $offset
);
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - Admin Panel</title>
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
        .property-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a class="nav-link active" href="properties.php">
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
                    <h2>Manage Properties</h2>
                    <div class="text-muted">
                        Total Properties: <strong><?php echo $total_properties; ?></strong>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Owner</th>
                                        <th>Type</th>
                                        <th>Rent</th>
                                        <th>Status</th>
                                        <th>Images</th>
                                        <th>Rating</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <?php
                                                        $image = getPropertyImage($property['id']);
                                                        ?>
                                                        <img src="<?php echo $image; ?>" 
                                                             alt="Property" class="property-image">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($property['title']); ?></h6>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars(substr($property['address'], 0, 50)); ?>
                                                            <?php if (strlen($property['address']) > 50): ?>...<?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($property['owner_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($property['owner_email']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo ucfirst($property['property_type']); ?></span>
                                            </td>
                                            <td>
                                                <strong>৳<?php echo number_format($property['rent_amount']); ?></strong><br>
                                                <small class="text-muted">/<?php echo $property['rent_period']; ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $property['is_available'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $property['is_available'] ? 'Available' : 'Rented'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $property['image_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($property['avg_rating']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-warning me-1">
                                                            <i class="fas fa-star"></i>
                                                        </span>
                                                        <span><?php echo number_format($property['avg_rating'], 1); ?></span>
                                                        <small class="text-muted ms-1">(<?php echo $property['review_count']; ?>)</small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No reviews</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($property['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="../property-details.php?id=<?php echo $property['id']; ?>" 
                                                       class="btn btn-info btn-sm" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle_availability">
                                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                                        <button type="submit" class="btn btn-<?php echo $property['is_available'] ? 'warning' : 'success'; ?> btn-sm" 
                                                                onclick="return confirm('<?php echo $property['is_available'] ? 'Mark as rented?' : 'Mark as available?'; ?>')">
                                                            <i class="fas fa-<?php echo $property['is_available'] ? 'times' : 'check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                                onclick="return confirm('Delete this property? This action cannot be undone.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Properties pagination">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
