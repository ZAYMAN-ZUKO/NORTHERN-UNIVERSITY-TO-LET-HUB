<?php
// Northern To-Let Hub - Installation Script
// Run this file once to set up the database and initial configuration

// Check if already installed
if (file_exists('config/installed.txt')) {
    die('Application is already installed. Delete config/installed.txt to reinstall.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'northern_tolet_hub';
    $db_username = $_POST['db_username'] ?? 'root';
    $db_password = $_POST['db_password'] ?? '';
    $site_url = $_POST['site_url'] ?? 'http://localhost/NUB-Tolet';
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_email = $_POST['admin_email'] ?? 'admin@northern.edu.bd';
    $admin_password = $_POST['admin_password'] ?? '';
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host=$db_host", $db_username, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");
        
        // Read and execute schema
        $schema = file_get_contents('database/schema.sql');
        $pdo->exec($schema);
        
        // Update config files
        $config_content = file_get_contents('config/database.php');
        $config_content = str_replace("private \$host = 'localhost';", "private \$host = '$db_host';", $config_content);
        $config_content = str_replace("private \$db_name = 'northern_tolet_hub';", "private \$db_name = '$db_name';", $config_content);
        $config_content = str_replace("private \$username = 'root';", "private \$username = '$db_username';", $config_content);
        $config_content = str_replace("private \$password = '';", "private \$password = '$db_password';", $config_content);
        file_put_contents('config/database.php', $config_content);
        
        $config_content = file_get_contents('config/config.php');
        $config_content = str_replace("define('SITE_URL', 'http://localhost/NUB-Tolet');", "define('SITE_URL', '$site_url');", $config_content);
        file_put_contents('config/config.php', $config_content);
        
        // Create admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = 1");
        $stmt->execute([$admin_username, $admin_email, $hashed_password]);
        
        // Create uploads directory
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        if (!is_dir('uploads/properties')) {
            mkdir('uploads/properties', 0755, true);
        }
        
        // Mark as installed
        file_put_contents('config/installed.txt', date('Y-m-d H:i:s'));
        
        $success = 'Installation completed successfully! You can now access the application.';
        
    } catch (Exception $e) {
        $error = 'Installation failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Northern To-Let Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Install Northern To-Let Hub
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="index.php" class="btn btn-success">Go to Application</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <h5 class="mb-3">Database Configuration</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Host</label>
                                        <input type="text" class="form-control" name="db_host" value="localhost" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Name</label>
                                        <input type="text" class="form-control" name="db_name" value="northern_tolet_hub" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Username</label>
                                        <input type="text" class="form-control" name="db_username" value="root" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Database Password</label>
                                        <input type="password" class="form-control" name="db_password">
                                    </div>
                                </div>
                                
                                <h5 class="mb-3 mt-4">Site Configuration</h5>
                                <div class="mb-3">
                                    <label class="form-label">Site URL</label>
                                    <input type="url" class="form-control" name="site_url" value="http://localhost/NUB-Tolet" required>
                                </div>
                                
                                <h5 class="mb-3 mt-4">Admin Account</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Admin Username</label>
                                        <input type="text" class="form-control" name="admin_username" value="admin" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Admin Email</label>
                                        <input type="email" class="form-control" name="admin_email" value="admin@northern.edu.bd" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Password</label>
                                    <input type="password" class="form-control" name="admin_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-download me-2"></i>Install Application
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
