<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

// Get featured properties
$featured_properties = getFeaturedProperties();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Find Your Perfect Home Near NUB</title>
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="properties.php">Properties</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <?php if ($_SESSION['user_type'] == 'owner'): ?>
                                    <li><a class="dropdown-item" href="add-property.php">Add Property</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="bookmarks.php">Bookmarks</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Find Your Perfect Home Near <span class="text-warning">Northern University</span>
                    </h1>
                    <p class="lead text-white mb-4">
                        Connect with local property owners and find the best rental properties around NUB campus. 
                        Whether you're looking for a seat, room, or flat - we've got you covered!
                    </p>
                    <div class="d-flex gap-3">
                        <a href="properties.php" class="btn btn-warning btn-lg px-4">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                        <a href="register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>Get Started
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="nub-campus-image">
                            <i class="fas fa-university fa-10x text-white opacity-75"></i>
                            <h3 class="text-white mt-3">Northern University Bangladesh</h3>
                            <p class="text-white-50">Your Gateway to Quality Education</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="search-card">
                        <h3 class="text-center mb-4">Find Your Ideal Property</h3>
                        <form action="properties.php" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Property Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="seat">Seat</option>
                                    <option value="room">Room</option>
                                    <option value="flat">Flat</option>
                                    <option value="house">House</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Max Rent (BDT)</label>
                                <input type="number" name="max_rent" class="form-control" placeholder="e.g., 15000">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Distance from NUB (km)</label>
                                <select name="distance" class="form-select">
                                    <option value="">Any Distance</option>
                                    <option value="1">Within 1 km</option>
                                    <option value="2">Within 2 km</option>
                                    <option value="5">Within 5 km</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Featured Properties</h2>
            <div class="row">
                <?php if (!empty($featured_properties)): ?>
                    <?php foreach ($featured_properties as $property): ?>
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
                                            <span class="h5 text-primary">৳<?php echo number_format($property['rent_amount']); ?></span>
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
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No featured properties available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="properties.php" class="btn btn-primary btn-lg">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Northern To-Let Hub?</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-map-marked-alt fa-3x text-primary"></i>
                        </div>
                        <h4>Location-Based Search</h4>
                        <p class="text-muted">Find properties within walking distance from Northern University Bangladesh campus.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <h4>Verified Users</h4>
                        <p class="text-muted">All users are verified with either Student ID or NID for your safety and security.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-star fa-3x text-primary"></i>
                        </div>
                        <h4>Reviews & Ratings</h4>
                        <p class="text-muted">Read genuine reviews from other students and make informed decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                <p class="text-muted mb-0">&copy; 2025 Northern To-Let Hub. All rights reserved.</p>
                <p class="text-muted mb-0">Developed by MD. Abdullah Al Mamun (01576638020) & Nahim Masrur (01875668148)</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
