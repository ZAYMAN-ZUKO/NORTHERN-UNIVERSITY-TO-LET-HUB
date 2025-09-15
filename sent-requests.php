<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

// Check if user is logged in and is renter
if (!isLoggedIn() || $_SESSION['user_type'] != 'renter') {
    header('Location: login.php');
    exit();
}

$sent_requests = getSentRequests($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sent Requests - <?php echo SITE_NAME; ?></title>
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
                            <li><a class="dropdown-item active" href="sent-requests.php">Sent Requests</a></li>
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
                <h1 class="mb-4">My Sent Requests</h1>
                <p class="text-muted mb-4">Track the status of your rental requests.</p>
            </div>
        </div>

        <?php if (!empty($sent_requests)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Rent</th>
                                    <th>Owner</th>
                                    <th>My Message</th>
                                    <th>Status</th>
                                    <th>Sent Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sent_requests as $request): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['property_title']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($request['address']); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-primary">৳<?php echo number_format($request['rent_amount']); ?></strong><br>
                                                <small class="text-muted">/<?php echo $request['rent_period']; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['owner_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i>
                                                    <a href="tel:<?php echo $request['owner_phone']; ?>" class="text-decoration-none">
                                                        <?php echo $request['owner_phone']; ?>
                                                    </a>
                                                </small>
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
                                                <?php echo date('M d, Y', strtotime($request['requested_at'])); ?><br>
                                                <?php echo date('h:i A', strtotime($request['requested_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="property-details.php?id=<?php echo $request['property_id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View Property
                                                </a>
                                                <?php if ($request['status'] == 'pending'): ?>
                                                    <button class="btn btn-outline-warning btn-sm" 
                                                            onclick="alert('Request is still pending. Please wait for the owner to respond.')">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </button>
                                                <?php elseif ($request['status'] == 'approved'): ?>
                                                    <button class="btn btn-outline-success btn-sm" 
                                                            onclick="alert('Congratulations! Your request has been approved. Contact the owner for further details.')">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-danger btn-sm" 
                                                            onclick="alert('Your request was rejected. You can try contacting the owner directly or look for other properties.')">
                                                        <i class="fas fa-times me-1"></i>Rejected
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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
                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No requests sent yet</h4>
                <p class="text-muted">Start browsing properties and send requests to property owners.</p>
                <a href="properties.php" class="btn btn-primary">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted">Connecting students with the best rental properties around Northern University Bangladesh.</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="properties.php" class="text-muted text-decoration-none">Browse Properties</a></li>
                        <li><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contact Info</h5>
                    <p class="text-muted">
                        <i class="fas fa-map-marker-alt me-2"></i>Northern University Bangladesh<br>
                        <i class="fas fa-envelope me-2"></i>info@northern.edu.bd
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted mb-0">&copy; 2024 Northern To-Let Hub. All rights reserved.</p>
                <p class="text-muted mb-0">Developed by MD. Abdullah Al Mamun (01576638020) & Nahim Masrur (01875668148)</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
