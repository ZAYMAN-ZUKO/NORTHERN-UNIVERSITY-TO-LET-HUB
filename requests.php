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

$rent_requests = getRentRequests($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Requests - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="add-property.php">Add Property</a></li>
                            <li><a class="dropdown-item active" href="requests.php">Rent Requests</a></li>
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
                <h1 class="mb-4">Rent Requests</h1>
                <p class="text-muted mb-4">Manage rental requests for your properties.</p>
            </div>
        </div>

        <?php if (!empty($rent_requests)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Renter</th>
                                    <th>Contact</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rent_requests as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['property_title']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['renter_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-phone me-1"></i>
                                                <a href="tel:<?php echo $request['renter_phone']; ?>" class="text-decoration-none">
                                                    <?php echo $request['renter_phone']; ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="message-preview" style="max-width: 200px;">
                                                <?php echo htmlspecialchars(substr($request['message'], 0, 100)); ?>
                                                <?php if (strlen($request['message']) > 100): ?>
                                                    <span class="text-muted">...</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $request['status'] == 'pending' ? 'warning' : ($request['status'] == 'approved' ? 'success' : 'danger'); ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($request['requested_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] == 'pending'): ?>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="approve-request.php?id=<?php echo $request['id']; ?>&status=approved" 
                                                       class="btn btn-success btn-sm"
                                                       onclick="return confirm('Approve this request?')">
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </a>
                                                    <a href="approve-request.php?id=<?php echo $request['id']; ?>&status=rejected" 
                                                       class="btn btn-danger btn-sm"
                                                       onclick="return confirm('Reject this request?')">
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">
                                                    Responded on <?php echo date('M d', strtotime($request['responded_at'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No rent requests yet</h4>
                <p class="text-muted">When students send requests for your properties, they will appear here.</p>
                <a href="add-property.php" class="btn btn-primary">Add Your First Property</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
