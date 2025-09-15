<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Initialize database connection
$conn = (new Database())->getConnection();

$property_id = $_GET['id'] ?? 0;

if (!$property_id) {
    header('Location: properties.php');
    exit();
}

$property = getPropertyDetails($property_id);
if (!$property) {
    header('Location: properties.php');
    exit();
}

$images = getPropertyImages($property_id);
$reviews = getPropertyReviews($property_id);
$avg_rating = getAverageRating($property_id);
$is_bookmarked = isLoggedIn() ? isBookmarked($property_id, $_SESSION['user_id']) : false;

// Handle rent request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_request'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    $message = sanitizeInput($_POST['message']);
    if (sendRentRequest($property_id, $_SESSION['user_id'], $message)) {
        $success = 'Rent request sent successfully!';
    } else {
        $error = 'Failed to send rent request. Please try again.';
    }
}

// Handle bookmark toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_bookmark'])) {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    
    if ($is_bookmarked) {
        removeBookmark($property_id, $_SESSION['user_id']);
        $is_bookmarked = false;
    } else {
        addBookmark($property_id, $_SESSION['user_id']);
        $is_bookmarked = true;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn() || $_SESSION['user_type'] != 'renter') {
        header('Location: login.php');
        exit();
    }
    
    $rating = (int)$_POST['rating'];
    $comment = sanitizeInput($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5) {
        if (addReview($property_id, $_SESSION['user_id'], $rating, $comment)) {
            $success = 'Review submitted successfully!';
            // Refresh the page to show the new review
            header('Location: property-details.php?id=' . $property_id);
            exit();
        } else {
            $error = 'Failed to submit review. You may have already reviewed this property.';
        }
    } else {
        $error = 'Please select a valid rating.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - <?php echo SITE_NAME; ?></title>
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

    <div class="container py-5" style="margin-top: 76px;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="properties.php">Properties</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($property['title']); ?></li>
            </ol>
        </nav>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Property Images -->
            <div class="col-lg-8">
                <div class="property-gallery mb-4">
                    <?php if (!empty($images)): ?>
                        <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo $image; ?>" class="d-block w-100 property-image" 
                                             alt="Property Image" style="cursor: pointer; height: 400px; object-fit: cover;"
                                             data-bs-toggle="modal" data-bs-target="#imageModal" 
                                             data-image-src="<?php echo $image; ?>" data-image-index="<?php echo $index; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($images) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <?php if (count($images) > 1): ?>
                            <div class="thumbnail-gallery mt-3">
                                <div class="row g-2">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="col-3 col-md-2">
                                            <img src="<?php echo $image; ?>" class="img-fluid rounded thumbnail-img" 
                                                 alt="Thumbnail" style="cursor: pointer; height: 60px; object-fit: cover;"
                                                 data-bs-target="#propertyCarousel" data-bs-slide-to="<?php echo $index; ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/800x400/007bff/ffffff?text=Property+Image" 
                             class="img-fluid rounded property-image" alt="Property Image" 
                             style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#imageModal"
                             data-image-src="https://via.placeholder.com/800x400/007bff/ffffff?text=Property+Image">
                    <?php endif; ?>
                </div>

                <!-- Property Description -->
                <div class="property-info">
                    <h2 class="mb-3"><?php echo htmlspecialchars($property['title']); ?></h2>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($property['address']); ?>
                            </p>
                            <?php if ($property['distance_from_university']): ?>
                                <p class="text-success mb-2">
                                    <i class="fas fa-walking me-2"></i>
                                    <?php echo $property['distance_from_university']; ?> km from Northern University
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="property-price">
                                ৳<?php echo number_format($property['rent_amount']); ?>
                                <small class="text-muted">/<?php echo $property['rent_period']; ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <i class="fas fa-bed fa-2x text-primary mb-2"></i>
                                <h5><?php echo $property['bedrooms']; ?></h5>
                                <small class="text-muted">Bedrooms</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <i class="fas fa-bath fa-2x text-primary mb-2"></i>
                                <h5><?php echo $property['bathrooms']; ?></h5>
                                <small class="text-muted">Bathrooms</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <i class="fas fa-ruler-combined fa-2x text-primary mb-2"></i>
                                <h5><?php echo $property['floor_area']; ?></h5>
                                <small class="text-muted">Sq Ft</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center">
                                <i class="fas fa-home fa-2x text-primary mb-2"></i>
                                <h5><?php echo ucfirst($property['property_type']); ?></h5>
                                <small class="text-muted">Type</small>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">Description</h4>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>

                    <?php if ($property['facilities']): ?>
                        <h4 class="mb-3">Facilities</h4>
                        <div class="row">
                            <?php 
                            $facilities = json_decode($property['facilities'], true);
                            if ($facilities):
                            ?>
                                <?php foreach ($facilities as $facility): ?>
                                    <div class="col-md-6 mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <?php echo htmlspecialchars($facility); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($property['rules']): ?>
                        <h4 class="mb-3">Rules & Regulations</h4>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($property['rules'])); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Reviews Section -->
                <div class="property-info mt-4">
                    <h4 class="mb-3">Reviews & Ratings</h4>
                    
                    <?php if ($avg_rating['total_reviews'] > 0): ?>
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="display-4 fw-bold text-primary"><?php echo number_format($avg_rating['avg_rating'], 1); ?></div>
                                <div class="text-warning mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $avg_rating['avg_rating'] ? '' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">Based on <?php echo $avg_rating['total_reviews']; ?> reviews</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet. Be the first to review this property!</p>
                    <?php endif; ?>

                    <?php if (!empty($reviews)): ?>
                        <div class="reviews">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-card mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($review['reviewer_name']); ?></h6>
                                        <div class="text-warning">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                    <?php if ($review['comment']): ?>
                                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Review Form -->
                    <?php if (isLoggedIn() && $_SESSION['user_type'] == 'renter'): ?>
                        <div class="add-review-section mt-4">
                            <h5 class="mb-3">Write a Review</h5>
                            <form method="POST" id="reviewForm">
                                <div class="mb-3">
                                    <label class="form-label">Rating *</label>
                                    <div class="rating-input">
                                        <input type="hidden" name="rating" id="rating" value="0" required>
                                        <div class="stars">
                                            <i class="fas fa-star star" data-rating="1"></i>
                                            <i class="fas fa-star star" data-rating="2"></i>
                                            <i class="fas fa-star star" data-rating="3"></i>
                                            <i class="fas fa-star star" data-rating="4"></i>
                                            <i class="fas fa-star star" data-rating="5"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="4" 
                                              placeholder="Share your experience with this property..."></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary">
                                    <i class="fas fa-star me-2"></i>Submit Review
                                </button>
                            </form>
                        </div>
                    <?php elseif (!isLoggedIn()): ?>
                        <div class="mt-4">
                            <p class="text-muted">Please <a href="login.php">login</a> to write a review.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Property Actions & Owner Info -->
            <div class="col-lg-4">
                <div class="property-info">
                    <div class="d-grid gap-2 mb-4">
                        <?php if (isLoggedIn()): ?>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="toggle_bookmark" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-heart <?php echo $is_bookmarked ? 'text-danger' : ''; ?> me-2"></i>
                                    <?php echo $is_bookmarked ? 'Remove from Bookmarks' : 'Add to Bookmarks'; ?>
                                </button>
                            </form>
                            
                            <?php if ($_SESSION['user_type'] == 'renter'): ?>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
                                    <i class="fas fa-envelope me-2"></i>Send Rent Request
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Contact Owner
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Owner Information -->
                    <h5 class="mb-3">Property Owner</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($property['owner_name']); ?></h6>
                            <small class="text-muted">Property Owner</small>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?php echo $property['owner_phone']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($property['owner_phone']); ?>
                            </a>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?php echo $property['owner_email']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($property['owner_email']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rent Request Modal -->
    <?php if (isLoggedIn() && $_SESSION['user_type'] == 'renter'): ?>
        <div class="modal fade" id="requestModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Rent Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="message" class="form-label">Message to Owner</label>
                                <textarea class="form-control" id="message" name="message" rows="4" 
                                          placeholder="Tell the owner about yourself and your requirements..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="send_request" class="btn btn-primary">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Property Images</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="modalCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="modalCarouselInner">
                            <!-- Images will be loaded here -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#modalCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#modalCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Combined functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Global modal cleanup function
            window.cleanupModal = function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                document.body.style.overflow = '';
            };
            
            // Clean up any leftover modal backdrops on page load
            window.cleanupModal();
            
            // Image modal functionality
            const imageModal = document.getElementById('imageModal');
            const modalCarouselInner = document.getElementById('modalCarouselInner');
            const propertyImages = document.querySelectorAll('.property-image');
            const thumbnailImages = document.querySelectorAll('.thumbnail-img');
            
            // Handle main image clicks
            propertyImages.forEach(img => {
                img.addEventListener('click', function() {
                    const imageSrc = this.getAttribute('data-image-src');
                    const imageIndex = parseInt(this.getAttribute('data-image-index')) || 0;
                    showImageModal(imageSrc, imageIndex);
                });
            });
            
            // Handle thumbnail clicks
            thumbnailImages.forEach(img => {
                img.addEventListener('click', function() {
                    const slideIndex = parseInt(this.getAttribute('data-bs-slide-to'));
                    const carousel = document.getElementById('propertyCarousel');
                    const bsCarousel = new bootstrap.Carousel(carousel);
                    bsCarousel.to(slideIndex);
                });
            });
            
            // Handle modal close button clicks
            const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        window.cleanupModal();
                    }, 300);
                });
            });
            
            function showImageModal(imageSrc, imageIndex) {
                try {
                    // Clear previous images
                    modalCarouselInner.innerHTML = '';
                    
                    // Get all images from the property
                    const allImages = Array.from(propertyImages).map(img => img.getAttribute('data-image-src'));
                    
                    // Create carousel items
                    allImages.forEach((src, index) => {
                        const carouselItem = document.createElement('div');
                        carouselItem.className = `carousel-item ${index === imageIndex ? 'active' : ''}`;
                        carouselItem.innerHTML = `<img src="${src}" class="d-block w-100" alt="Property Image" style="max-height: 80vh; object-fit: contain;" onerror="this.src='https://via.placeholder.com/400x300/007bff/ffffff?text=Image+Not+Found'">`;
                        modalCarouselInner.appendChild(carouselItem);
                    });
                    
                    // Show modal
                    const modal = new bootstrap.Modal(imageModal);
                    modal.show();
                    
                    // Add event listener for modal close to clean up
                    imageModal.addEventListener('hidden.bs.modal', function() {
                        // Use global cleanup function
                        window.cleanupModal();
                        
                        // Force cleanup after a short delay
                        setTimeout(() => {
                            window.cleanupModal();
                        }, 100);
                    });
                } catch (error) {
                    console.error('Error showing image modal:', error);
                    alert('Error loading image. Please try again.');
                }
            }
            
            // Star rating functionality
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            
            if (stars.length > 0 && ratingInput) {
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = parseInt(this.getAttribute('data-rating'));
                        ratingInput.value = rating;
                        updateStars(rating);
                    });
                    
                    star.addEventListener('mouseenter', function() {
                        const rating = parseInt(this.getAttribute('data-rating'));
                        updateStars(rating);
                    });
                });
                
                const starsContainer = document.querySelector('.stars');
                if (starsContainer) {
                    starsContainer.addEventListener('mouseleave', function() {
                        const currentRating = parseInt(ratingInput.value);
                        updateStars(currentRating);
                    });
                }
                
                function updateStars(rating) {
                    stars.forEach((star, index) => {
                        if (index < rating) {
                            star.classList.remove('text-muted');
                            star.classList.add('text-warning');
                        } else {
                            star.classList.remove('text-warning');
                            star.classList.add('text-muted');
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
