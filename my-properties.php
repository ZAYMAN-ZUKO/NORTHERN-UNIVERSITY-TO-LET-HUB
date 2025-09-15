<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Check if user is logged in and is owner
if (!isLoggedIn() || $_SESSION['user_type'] != 'owner') {
    header('Location: login.php');
    exit();
}

$conn = (new Database())->getConnection();
$stmt = $conn->prepare("SELECT * FROM properties WHERE owner_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties - <?php echo SITE_NAME; ?></title>
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
                            <li><a class="dropdown-item active" href="my-properties.php">My Properties</a></li>
                            <li><a class="dropdown-item" href="requests.php">Rent Requests</a></li>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>My Properties</h1>
                    <a href="add-property.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Property
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($properties)): ?>
            <div class="row">
                <?php foreach ($properties as $property): ?>
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
                                <p class="card-text">
                                    <small class="text-muted">
                                        <?php echo substr($property['description'], 0, 100); ?>...
                                    </small>
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
                                <?php if ($property['distance_from_university']): ?>
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-walking me-1"></i>
                                            <?php echo $property['distance_from_university']; ?> km from NUB
                                        </small>
                                    </div>
                                <?php endif; ?>
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
                                    <div class="btn-group">
                                        <a href="toggle-availability.php?id=<?php echo $property['id']; ?>&status=<?php echo $property['is_available'] ? '0' : '1'; ?>" 
                                           class="btn btn-outline-<?php echo $property['is_available'] ? 'warning' : 'success'; ?> btn-sm">
                                            <i class="fas fa-<?php echo $property['is_available'] ? 'pause' : 'play'; ?> me-1"></i>
                                            <?php echo $property['is_available'] ? 'Mark as Rented' : 'Mark as Available'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No properties yet</h4>
                <p class="text-muted">Start by adding your first property to get started.</p>
                <a href="add-property.php" class="btn btn-primary">Add Your First Property</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
