<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link active" href="about.php">About</a>
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

    <div class="container py-5" style="margin-top: 76px;">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary mb-3">About Northern To-Let Hub</h1>
                    <p class="lead text-muted">Connecting students with the best rental properties around Northern University Bangladesh</p>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-university fa-3x text-primary mb-3"></i>
                                <h4>For Students</h4>
                                <p class="text-muted">Find affordable and convenient housing options near your university campus with verified property owners.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-home fa-3x text-primary mb-3"></i>
                                <h4>For Property Owners</h4>
                                <p class="text-muted">List your properties and connect with verified students looking for accommodation near NUB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-body">
                        <h3 class="mb-4">Our Mission</h3>
                        <p class="lead">To create a safe, reliable, and user-friendly platform that bridges the gap between students and property owners in the Northern University Bangladesh community.</p>
                        
                        <h4 class="mt-4 mb-3">Key Features</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Verified user accounts</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Location-based search</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Property reviews and ratings</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Easy booking system</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Photo galleries</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Distance calculator</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Bookmark favorites</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Mobile-friendly design</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-5">
                    <div class="card-body">
                        <h3 class="mb-4">Why Choose Us?</h3>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                    <h5>Safe & Secure</h5>
                                    <p class="text-muted">All users are verified with Student ID or NID for your safety.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-map-marker-alt fa-2x text-primary mb-2"></i>
                                    <h5>Location Focused</h5>
                                    <p class="text-muted">Specialized for properties around Northern University Bangladesh.</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <h5>Community Driven</h5>
                                    <p class="text-muted">Built by students, for students and local property owners.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h3 class="mb-4">Get Started Today</h3>
                    <p class="lead mb-4">Join our community and find your perfect home or list your property.</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </a>
                        <a href="properties.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
