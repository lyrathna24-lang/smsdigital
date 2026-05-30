<?php
// Database setup script for local development
echo "<h2 style='font-family: Arial, sans-serif;'>🔧 Setting up Database Tables...</h2>";

// First, create database connection without specifying database name
try {
    $dsn = "mysql:host=localhost;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS primary_school_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green; font-family: Arial, sans-serif;'>✅ Database 'primary_school_db' created or already exists</p>";
    
    // Now connect to the specific database
    $dsn = "mysql:host=localhost;dbname=primary_school_db;charset=utf8mb4";
    $db = new PDO($dsn, 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL file
    if (file_exists('database.sql')) {
        $sql = file_get_contents('database.sql');
        
        // Remove comments and split into statements
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $db->exec($statement);
                    if (strpos(strtoupper($statement), 'CREATE TABLE') !== false) {
                        echo "<p style='color: green; font-family: Arial, sans-serif;'>✅ Created table: " . substr($statement, 0, 50) . "...</p>";
                    }
                } catch(PDOException $e) {
                    // Ignore errors for tables that already exist
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p style='color: orange; font-family: Arial, sans-serif;'>⚠️ Error: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
        
        echo "<h3 style='color: green; font-family: Arial, sans-serif;'>🎉 Database setup completed!</h3>";
        echo "<p style='font-family: Arial, sans-serif;'>🔗 <a href='index.html'>Go to Main Application</a></p>";
        echo "<p style='font-family: Arial, sans-serif;'>🔗 <a href='database.php'>Test Database Connection</a></p>";
        echo "<p style='font-family: Arial, sans-serif;'>🌐 <a href='http://localhost:8080/school%20management/'>Open School Management System</a></p>";
        
    } else {
        echo "<p style='color: red; font-family: Arial, sans-serif;'>❌ database.sql file not found!</p>";
    }
    
} catch(PDOException $e) {
    echo "<h3 style='color: red; font-family: Arial, sans-serif;'>❌ Database Setup Failed</h3>";
    echo "<p style='font-family: Arial, sans-serif;'>Error: " . $e->getMessage() . "</p>";
    echo "<p style='font-family: Arial, sans-serif;'>Please ensure XAMPP MySQL is running.</p>";
} catch(Exception $e) {
    echo "<h3 style='color: red; font-family: Arial, sans-serif;'>❌ General Error</h3>";
    echo "<p style='font-family: Arial, sans-serif;'>Error: " . $e->getMessage() . "</p>";
}
?>
