<?php
session_start();

define('SITE_NAME', 'Northern To-Let Hub');
define('SITE_URL', 'http://localhost/NUB-Tolet');
define('SITE_DESCRIPTION', 'Find and list rental properties around Northern University Bangladesh');

define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

define('UNIVERSITY_LAT', 23.8103);
define('UNIVERSITY_LNG', 90.4125);

define('ITEMS_PER_PAGE', 12);

date_default_timezone_set('Asia/Dhaka');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';
?>
