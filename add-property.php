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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $property_type = $_POST['property_type'];
    $rent_amount = (float)$_POST['rent_amount'];
    $rent_period = $_POST['rent_period'];
    $address = sanitizeInput($_POST['address']);
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $bedrooms = (int)$_POST['bedrooms'];
    $bathrooms = (int)$_POST['bathrooms'];
    $floor_area = (float)$_POST['floor_area'];
    $facilities = $_POST['facilities'] ?? [];
    $rules = sanitizeInput($_POST['rules']);
    
    // Debug: Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $error = 'User not logged in. Please login and try again.';
    }
    // Validation
    elseif (empty($title) || empty($description) || empty($address) || empty($property_type) || $rent_amount <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $conn = (new Database())->getConnection();
            
            // Calculate distance from university if coordinates provided
            $distance_from_university = null;
            if ($latitude && $longitude) {
                $distance_from_university = calculateDistance($latitude, $longitude, UNIVERSITY_LAT, UNIVERSITY_LNG);
            }
            
            // Insert property
            $stmt = $conn->prepare("
                INSERT INTO properties (owner_id, title, description, property_type, rent_amount, rent_period, 
                                      address, latitude, longitude, distance_from_university, bedrooms, bathrooms, 
                                      floor_area, facilities, rules) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $facilities_json = json_encode($facilities);
            
            $execute_result = $stmt->execute([
                $_SESSION['user_id'], $title, $description, $property_type, $rent_amount, $rent_period,
                $address, $latitude, $longitude, $distance_from_university, $bedrooms, $bathrooms,
                $floor_area, $facilities_json, $rules
            ]);
            
            if ($execute_result) {
                $property_id = $conn->lastInsertId();
                
                // Handle image uploads
                $uploaded_images = [];
                if (!empty($_FILES['images']['name'][0])) {
                    foreach ($_FILES['images']['name'] as $key => $filename) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $uploaded_path = uploadFile($file, UPLOAD_PATH . 'properties/');
                        if ($uploaded_path) {
                            $uploaded_images[] = $uploaded_path;
                        }
                    }
                }
                
                // Insert property images
                if (!empty($uploaded_images)) {
                    $stmt = $conn->prepare("INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)");
                    foreach ($uploaded_images as $index => $image_path) {
                        $is_primary = $index === 0 ? 1 : 0;
                        $stmt->execute([$property_id, $image_path, $is_primary]);
                    }
                }
                
                $success = 'Property added successfully!';
                // Redirect to property details or dashboard
                header('Location: property-details.php?id=' . $property_id);
                exit();
            } else {
                $error = 'Failed to add property. Please try again.';
                // Debug: Show SQL error
                $error_info = $stmt->errorInfo();
                $error .= ' SQL Error: ' . $error_info[2];
            }
        } catch (Exception $e) {
            $error = 'Failed to add property: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - <?php echo SITE_NAME; ?></title>
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
                            <li><a class="dropdown-item active" href="add-property.php">Add Property</a></li>
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-plus me-2"></i>Add New Property
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Basic Information -->
                            <h5 class="mb-3 text-primary">Basic Information</h5>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Property Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="property_type" class="form-label">Property Type *</label>
                                    <select class="form-select" id="property_type" name="property_type" required>
                                        <option value="">Select Type</option>
                                        <option value="seat" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'seat') ? 'selected' : ''; ?>>Seat</option>
                                        <option value="room" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'room') ? 'selected' : ''; ?>>Room</option>
                                        <option value="flat" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'flat') ? 'selected' : ''; ?>>Flat</option>
                                        <option value="house" <?php echo (isset($_POST['property_type']) && $_POST['property_type'] == 'house') ? 'selected' : ''; ?>>House</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required
                                          placeholder="Describe your property in detail..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <!-- Rent Information -->
                            <h5 class="mb-3 text-primary mt-4">Rent Information</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rent_amount" class="form-label">Rent Amount (BDT) *</label>
                                    <input type="number" class="form-control" id="rent_amount" name="rent_amount" 
                                           value="<?php echo isset($_POST['rent_amount']) ? htmlspecialchars($_POST['rent_amount']) : ''; ?>" 
                                           min="0" step="0.01" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="rent_period" class="form-label">Rent Period *</label>
                                    <select class="form-select" id="rent_period" name="rent_period" required>
                                        <option value="">Select Period</option>
                                        <option value="daily" <?php echo (isset($_POST['rent_period']) && $_POST['rent_period'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                                        <option value="weekly" <?php echo (isset($_POST['rent_period']) && $_POST['rent_period'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                        <option value="monthly" <?php echo (isset($_POST['rent_period']) && $_POST['rent_period'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Location Information -->
                            <h5 class="mb-3 text-primary mt-4">Location Information</h5>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required
                                          placeholder="Enter the full address of your property"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitude (Optional)</label>
                                    <input type="number" class="form-control" id="latitude" name="latitude" 
                                           value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>" 
                                           step="any" placeholder="e.g., 23.8103">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitude (Optional)</label>
                                    <input type="number" class="form-control" id="longitude" name="longitude" 
                                           value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>" 
                                           step="any" placeholder="e.g., 90.4125">
                                </div>
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Location Tip:</strong> You can get coordinates from Google Maps by right-clicking on your property location.
                            </div>

                            <!-- Property Details -->
                            <h5 class="mb-3 text-primary mt-4">Property Details</h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="bedrooms" class="form-label">Bedrooms</label>
                                    <input type="number" class="form-control" id="bedrooms" name="bedrooms" 
                                           value="<?php echo isset($_POST['bedrooms']) ? htmlspecialchars($_POST['bedrooms']) : '0'; ?>" 
                                           min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="bathrooms" class="form-label">Bathrooms</label>
                                    <input type="number" class="form-control" id="bathrooms" name="bathrooms" 
                                           value="<?php echo isset($_POST['bathrooms']) ? htmlspecialchars($_POST['bathrooms']) : '0'; ?>" 
                                           min="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="floor_area" class="form-label">Floor Area (Sq Ft)</label>
                                    <input type="number" class="form-control" id="floor_area" name="floor_area" 
                                           value="<?php echo isset($_POST['floor_area']) ? htmlspecialchars($_POST['floor_area']) : ''; ?>" 
                                           min="0" step="0.01">
                                </div>
                            </div>

                            <!-- Facilities -->
                            <h5 class="mb-3 text-primary mt-4">Facilities</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="WiFi" id="wifi">
                                        <label class="form-check-label" for="wifi">WiFi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Air Conditioning" id="ac">
                                        <label class="form-check-label" for="ac">Air Conditioning</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Furnished" id="furnished">
                                        <label class="form-check-label" for="furnished">Furnished</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Parking" id="parking">
                                        <label class="form-check-label" for="parking">Parking</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Security" id="security">
                                        <label class="form-check-label" for="security">24/7 Security</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Lift" id="lift">
                                        <label class="form-check-label" for="lift">Lift</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="Generator" id="generator">
                                        <label class="form-check-label" for="generator">Generator</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="CCTV" id="cctv">
                                        <label class="form-check-label" for="cctv">CCTV</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Rules -->
                            <div class="mb-3 mt-4">
                                <label for="rules" class="form-label">Rules & Regulations</label>
                                <textarea class="form-control" id="rules" name="rules" rows="3"
                                          placeholder="Any specific rules or regulations for tenants..."><?php echo isset($_POST['rules']) ? htmlspecialchars($_POST['rules']) : ''; ?></textarea>
                            </div>

                            <!-- Images -->
                            <h5 class="mb-3 text-primary mt-4">Property Images</h5>
                            <div class="mb-3">
                                <label for="images" class="form-label">Upload Images</label>
                                <div class="image-upload-container">
                                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" onchange="previewImages(this)">
                                    <div class="form-text">You can select multiple images (JPG, PNG, GIF). First image will be used as the main image.</div>
                                    
                                    <!-- Image Preview Container -->
                                    <div id="imagePreview" class="row mt-3" style="display: none;">
                                        <div class="col-12">
                                            <h6>Selected Images:</h6>
                                            <div id="previewContainer" class="row g-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Add Property
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('rent_amount').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });

        document.getElementById('bedrooms').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });

        document.getElementById('bathrooms').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });

        document.getElementById('floor_area').addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });

        // Image preview functionality
        function previewImages(input) {
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            
            // Clear previous previews
            previewContainer.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                imagePreview.style.display = 'block';
                
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 col-sm-4 col-6';
                            
                            col.innerHTML = `
                                <div class="position-relative">
                                    <img src="${e.target.result}" class="img-fluid rounded preview-image" 
                                         alt="Preview ${index + 1}" style="height: 120px; width: 100%; object-fit: cover;">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge ${index === 0 ? 'bg-primary' : 'bg-secondary'}">${index === 0 ? 'Main' : index + 1}</span>
                                    </div>
                                </div>
                            `;
                            
                            previewContainer.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                imagePreview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
