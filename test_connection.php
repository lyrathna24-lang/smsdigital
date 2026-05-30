<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test - School Management System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .code { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 School Management System - Connection Test</h1>
    
    <h2>📋 XAMPP Status Check</h2>
    <p class="info">Please check your XAMPP Control Panel:</p>
    <ul>
        <li>✅ Apache should be <strong>running</strong> (green)</li>
        <li>✅ MySQL should be <strong>running</strong> (green)</li>
        <li>❌ If either is stopped, click 'Start' button</li>
    </ul>
    
    <h2>🌐 URL Test Results</h2>
    <?php
    $testUrls = [
        'http://localhost/school%20management/',
        'http://localhost:8080/school%20management/',
        'http://127.0.0.1/school%20management/',
        'http://127.0.0.1:8080/school%20management/'
    ];
    
    foreach ($testUrls as $url) {
        $parts = parse_url($url);
        $host = $parts['host'];
        $port = $parts['port'] ?? 80;
        
        echo "<p>Testing: <strong>$url</strong></p>";
        
        if ($socket = @fsockopen($host, $port, $errno, $errstr, 2)) {
            echo "<p class='success'>✅ Connection successful on port $port</p>";
            fclose($socket);
        } else {
            echo "<p class='error'>❌ Connection failed: $errstr</p>";
        }
    }
    ?>
    
    <h2>🛠️ Solutions</h2>
    
    <h3>1. Start XAMPP Services</h3>
    <div class="code">
        1. Open XAMPP Control Panel<br>
        2. Click 'Start' next to Apache<br>
        3. Click 'Start' next to MySQL<br>
        4. Wait for both to turn green
    </div>
    
    <h3>2. Check Port Usage</h3>
    <div class="code">
        If port 80 is blocked, Apache might use port 8080:<br>
        Try: http://localhost:8080/school%20management/
    </div>
    
    <h3>3. Alternative Access Methods</h3>
    <div class="code">
        Direct file access (bypass URL rewriting):<br>
        http://localhost/school%20management/index.html
    </div>
    
    <h2>🔗 Quick Access Links</h2>
    <ul>
        <li><a href="index.html">📄 Direct to index.html</a></li>
        <li><a href="database.php">🗄️ Test Database</a></li>
        <li><a href="setup_database.php">⚙️ Setup Database</a></li>
    </ul>
    
    <h2>📞 Next Steps</h2>
    <p>1. Ensure XAMPP Apache is running</p>
    <p>2. Try the direct link above to index.html</p>
    <p>3. If that works, your system is ready!</p>
    
</body>
</html>
