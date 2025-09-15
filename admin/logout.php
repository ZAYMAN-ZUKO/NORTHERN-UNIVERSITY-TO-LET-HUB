<?php
session_start();

// Destroy admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);

// Redirect to admin login
header('Location: login.php');
exit();
?>