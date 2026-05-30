<?php
header('Content-Type: application/json');
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data['username']) && !empty($data['password'])) {
        try {
            $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($data['password'], $user['password'])) {
                // create table if not exists
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

                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 24 * 3600);
                $ins = $db->prepare("INSERT INTO device_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $ins->execute([$user['id'], $token, $expires]);

                echo json_encode(['success' => true, 'device_token' => $token, 'expires_at' => $expires]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing username or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>