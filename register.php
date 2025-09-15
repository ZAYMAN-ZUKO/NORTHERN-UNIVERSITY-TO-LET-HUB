<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$conn = (new Database())->getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitizeInput($_POST['full_name']);
    $phone = sanitizeInput($_POST['phone']);
    $user_type = $_POST['user_type'];
    $verification_type = $_POST['verification_type'];
    $verification_id = sanitizeInput($_POST['verification_id']);
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($phone) || empty($verification_id)) {
        $error = 'All fields are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            $conn = (new Database())->getConnection();
            
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, password, full_name, phone, user_type, verification_type, verification_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $user_type, $verification_type, $verification_id])) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i><?php echo SITE_NAME; ?>
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">Create Account</h2>
                            <p class="text-muted">Join Northern To-Let Hub today</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="login.php" class="btn btn-success btn-sm">Login Now</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_type" class="form-label">I want to *</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="">Select your role</option>
                                    <option value="owner" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'owner') ? 'selected' : ''; ?>>List Properties (Owner)</option>
                                    <option value="renter" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'renter') ? 'selected' : ''; ?>>Find Properties (Renter)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="verification_type" class="form-label">Verification Type *</label>
                                <select class="form-select" id="verification_type" name="verification_type" required>
                                    <option value="">Select verification type</option>
                                    <option value="student" <?php echo (isset($_POST['verification_type']) && $_POST['verification_type'] == 'student') ? 'selected' : ''; ?>>Student ID</option>
                                    <option value="nid" <?php echo (isset($_POST['verification_type']) && $_POST['verification_type'] == 'nid') ? 'selected' : ''; ?>>National ID (NID)</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="verification_id" class="form-label">Verification ID *</label>
                                <input type="text" class="form-control" id="verification_id" name="verification_id" 
                                       placeholder="Enter your Student ID or NID number" 
                                       value="<?php echo isset($_POST['verification_id']) ? htmlspecialchars($_POST['verification_id']) : ''; ?>" required>
                                <div class="form-text">This will be used for verification purposes only.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">Already have an account? 
                                <a href="login.php" class="text-primary text-decoration-none fw-bold">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
