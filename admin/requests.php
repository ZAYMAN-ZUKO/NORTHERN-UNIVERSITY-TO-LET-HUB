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

// Handle request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? 0;
    
    switch ($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE rent_requests SET status = 'approved', responded_at = NOW() WHERE id = ?");
            if ($stmt->execute([$request_id])) {
                $message = 'Request approved successfully.';
            } else {
                $error = 'Failed to approve request.';
            }
            break;
            
        case 'reject':
            $stmt = $conn->prepare("UPDATE rent_requests SET status = 'rejected', responded_at = NOW() WHERE id = ?");
            if ($stmt->execute([$request_id])) {
                $message = 'Request rejected.';
            } else {
                $error = 'Failed to reject request.';
            }
            break;
            
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM rent_requests WHERE id = ?");
            if ($stmt->execute([$request_id])) {
                $message = 'Request deleted successfully.';
            } else {
                $error = 'Failed to delete request.';
            }
            break;
    }
}

// Get requests with pagination
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM rent_requests");
$stmt->execute();
$total_requests = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$total_pages = ceil($total_requests / $limit);

// Get requests
$limit = (int)$limit; // Ensure it's an integer
$offset = (int)$offset; // Ensure it's an integer
$stmt = $conn->prepare("
    SELECT r.*, 
           p.title as property_title, p.rent_amount, p.rent_period,
           u1.full_name as renter_name, u1.email as renter_email, u1.phone as renter_phone,
           u2.full_name as owner_name, u2.email as owner_email, u2.phone as owner_phone
    FROM rent_requests r
    JOIN properties p ON r.property_id = p.id
    JOIN users u1 ON r.renter_id = u1.id
    JOIN users u2 ON p.owner_id = u2.id
    ORDER BY r.requested_at DESC
    LIMIT " . $limit . " OFFSET " . $offset
);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rent Requests - Admin Panel</title>
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
                        <a class="nav-link" href="properties.php">
                            <i class="fas fa-home me-2"></i>Properties
                        </a>
                        <a class="nav-link active" href="requests.php">
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
                    <h2>Manage Rent Requests</h2>
                    <div class="text-muted">
                        Total Requests: <strong><?php echo $total_requests; ?></strong>
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
                                        <th>Renter</th>
                                        <th>Owner</th>
                                        <th>Message</th>
                                        <th>Status</th>
                                        <th>Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['property_title']); ?></strong><br>
                                                    <small class="text-muted">
                                                        ৳<?php echo number_format($request['rent_amount']); ?>/<?php echo $request['rent_period']; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['renter_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['renter_email']); ?></small><br>
                                                    <small class="text-muted"><?php echo $request['renter_phone']; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['owner_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['owner_email']); ?></small><br>
                                                    <small class="text-muted"><?php echo $request['owner_phone']; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px;">
                                                    <?php echo htmlspecialchars(substr($request['message'], 0, 100)); ?>
                                                    <?php if (strlen($request['message']) > 100): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'approved' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                                <?php if ($request['responded_at']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($request['responded_at'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($request['requested_at'])); ?><br>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($request['requested_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($request['status'] == 'pending'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <button type="submit" class="btn btn-success btn-sm" 
                                                                    onclick="return confirm('Approve this request?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Reject this request?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                                onclick="return confirm('Delete this request? This action cannot be undone.')">
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
                            <nav aria-label="Requests pagination">
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
