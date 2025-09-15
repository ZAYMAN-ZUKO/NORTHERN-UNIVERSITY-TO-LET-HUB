<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$conn = (new Database())->getConnection();
$filters = [
    'type' => $_GET['type'] ?? '',
    'max_rent' => $_GET['max_rent'] ?? '',
    'min_rent' => $_GET['min_rent'] ?? '',
    'distance' => $_GET['distance'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'keyword' => $_GET['keyword'] ?? '',
    'limit' => ITEMS_PER_PAGE
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

$properties = searchProperties($filters);
$total_properties = count($properties);
$total_pages = ceil($total_properties / ITEMS_PER_PAGE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Browse available rental properties around Northern University Bangladesh. Find seats, rooms, flats, and houses for rent near NUB campus.">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
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
                        <a class="nav-link active" href="properties.php">Properties</a>
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

    <section class="py-5 bg-light" style="margin-top: 76px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="search-card">
                        <h3 class="text-center mb-4">Find Your Ideal Property</h3>
                        <form method="GET" action="properties.php" class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Property Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="seat" <?php echo ($filters['type'] == 'seat') ? 'selected' : ''; ?>>Seat</option>
                                    <option value="room" <?php echo ($filters['type'] == 'room') ? 'selected' : ''; ?>>Room</option>
                                    <option value="flat" <?php echo ($filters['type'] == 'flat') ? 'selected' : ''; ?>>Flat</option>
                                    <option value="house" <?php echo ($filters['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Rent (BDT)</label>
                                <input type="number" name="min_rent" class="form-control" 
                                       value="<?php echo htmlspecialchars($filters['min_rent']); ?>" placeholder="e.g., 5000">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Max Rent (BDT)</label>
                                <input type="number" name="max_rent" class="form-control" 
                                       value="<?php echo htmlspecialchars($filters['max_rent']); ?>" placeholder="e.g., 15000">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Distance (km)</label>
                                <select name="distance" class="form-select">
                                    <option value="">Any Distance</option>
                                    <option value="1" <?php echo ($filters['distance'] == '1') ? 'selected' : ''; ?>>Within 1 km</option>
                                    <option value="2" <?php echo ($filters['distance'] == '2') ? 'selected' : ''; ?>>Within 2 km</option>
                                    <option value="5" <?php echo ($filters['distance'] == '5') ? 'selected' : ''; ?>>Within 5 km</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Bedrooms</label>
                                <select name="bedrooms" class="form-select">
                                    <option value="">Any</option>
                                    <option value="1" <?php echo ($filters['bedrooms'] == '1') ? 'selected' : ''; ?>>1+ Bed</option>
                                    <option value="2" <?php echo ($filters['bedrooms'] == '2') ? 'selected' : ''; ?>>2+ Bed</option>
                                    <option value="3" <?php echo ($filters['bedrooms'] == '3') ? 'selected' : ''; ?>>3+ Bed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="keyword" class="form-control" 
                                           value="<?php echo htmlspecialchars($filters['keyword']); ?>" 
                                           placeholder="Search by title, description, or address...">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Available Properties</h2>
                <span class="text-muted"><?php echo $total_properties; ?> properties found</span>
            </div>

            <?php if (!empty($properties)): ?>
                <div class="row">
                    <?php foreach ($properties as $property): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card property-card h-100">
                                <div class="position-relative">
                                    <?php 
                                    $image_url = getPropertyImage($property['id']);
                                    // Temporary debug - remove after fixing
                                    if (strpos($image_url, 'placeholder') === false) {
                                        echo "<!-- Debug: Image URL for property " . $property['id'] . ": " . $image_url . " -->";
                                    }
                                    ?>
                                    <img src="<?php echo $image_url; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>"
                                         loading="lazy"
                                         onerror="console.log('Image failed to load:', this.src); this.src='https://via.placeholder.com/400x300/007bff/ffffff?text=Image+Error';">
                                    <div class="property-badge">
                                        <?php echo ucfirst($property['property_type']); ?>
                                    </div>
                                    <?php if (isLoggedIn()): ?>
                                        <div class="property-actions">
                                            <button class="btn btn-sm btn-outline-light bookmark-btn" 
                                                    data-property-id="<?php echo $property['id']; ?>">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
                                           class="btn btn-outline-primary">View Details</a>
                                        <?php if (isLoggedIn() && $_SESSION['user_type'] == 'renter'): ?>
                                            <button class="btn btn-primary request-btn" 
                                                    data-property-id="<?php echo $property['id']; ?>">
                                                <i class="fas fa-envelope me-1"></i>Send Request
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Properties pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No properties found</h4>
                    <p class="text-muted">Try adjusting your search criteria or browse all properties.</p>
                    <a href="properties.php" class="btn btn-primary">View All Properties</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy load images
            const images = document.querySelectorAll('img[loading="lazy"]');
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src || img.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                images.forEach(img => imageObserver.observe(img));
            }

            // Bookmark functionality
            document.querySelectorAll('.bookmark-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const propertyId = this.dataset.propertyId;
                    // Add bookmark functionality here
                });
            });

            // Request functionality
            document.querySelectorAll('.request-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const propertyId = this.dataset.propertyId;
                    // Add request functionality here
                });
            });
        });
    </script>
</body>
</html>
