<?php
header('Content-Type: application/json');
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Ensure device_tokens table exists
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS device_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } catch (PDOException $e) {}
    
    if (!empty($data['username']) && !empty($data['password'])) {
        try {
            $stmt = $db->prepare("SELECT id, username, password, phone FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($data['password'], $user['password'])) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login successful!',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'phone' => $user['phone']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ឈ្មោះ ឬលេខសម្ងាត់ខុស!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'បញ្ចូលទិន្នន័យមិនគ្រប់គ្រាន់']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
