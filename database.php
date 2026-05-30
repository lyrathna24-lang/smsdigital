<?php
// ការកំណត់ Configuration របស់គេហទំព័រ
define('SITE_NAME', 'ប្រព័ន្ធគ្រប់គ្រងសាលារៀន');
// Local development configuration for localhost:8080
define('SITE_URL', 'http://localhost:8080/school%20management/');

// ការកំណត់ Database Configuration
// Local XAMPP database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'primary_school_db');

// Database connection framework
require_once 'class.Database.php';

// Create database connection object
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Enable error reporting for local development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Database connection is ready - no output for clean admin interface
} catch(Exception $e) {
    echo "<h3 style='color: red; font-family: Arial, sans-serif;'>❌ Database Connection Error</h3>";
    echo "<p style='font-family: Arial, sans-serif;'>Error: " . $e->getMessage() . "</p>";
    echo "<p style='font-family: Arial, sans-serif;'>Please ensure XAMPP MySQL is running and database 'primary_school_db' exists.</p>";
}
?>