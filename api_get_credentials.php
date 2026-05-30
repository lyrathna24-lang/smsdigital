<?php
header('Content-Type: application/json');
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // We'll provide a temporary one-time device token instead of returning plaintext passwords
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
    } catch (PDOException $e) {
        // ignore creation errors
    }

    if (!empty($data['phone'])) {
        try {
            $stmt = $db->prepare("SELECT id, username, phone FROM users WHERE phone = ?");
            $stmt->execute([$data['phone']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // generate a temporary token valid for 24 hours
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 24 * 3600);

                $ins = $db->prepare("INSERT INTO device_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $ins->execute([$user['id'], $token, $expires]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Temporary device login token generated.',
                    'credentials' => [
                        'username' => $user['username'],
                        'phone' => $user['phone'],
                        'device_token' => $token,
                        'expires_at' => $expires
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'មិនបានរកឃើញលេខទូរស័ព្ទនេះក្នុងប្រព័ន្ធ!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } elseif (!empty($data['username']) && !empty($data['phone'])) {
        try {
            $stmt = $db->prepare("SELECT id, username, phone FROM users WHERE username = ? AND phone = ?");
            $stmt->execute([$data['username'], $data['phone']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 24 * 3600);
                $ins = $db->prepare("INSERT INTO device_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $ins->execute([$user['id'], $token, $expires]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Temporary device login token generated.',
                    'credentials' => [
                        'username' => $user['username'],
                        'phone' => $user['phone'],
                        'device_token' => $token,
                        'expires_at' => $expires
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ឈ្មោះប្រើប្រាស់ ឬលេខទូរស័ព្ទខុស!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'សូមផ្តល់ឱ្យលេខទូរស័ព្ទ ឬឈ្មោះប្រើប្រាស់ដើម្បីស្វាគមន៍គណនី']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
