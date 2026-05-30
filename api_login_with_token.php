<?php
header('Content-Type: application/json');
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data['device_token'])) {
        try {
            $stmt = $db->prepare("SELECT dt.user_id, dt.expires_at, u.username, u.phone FROM device_tokens dt JOIN users u ON dt.user_id = u.id WHERE dt.token = ?");
            $stmt->execute([$data['device_token']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $now = date('Y-m-d H:i:s');
                if ($row['expires_at'] >= $now) {
                    echo json_encode(['success' => true, 'user' => ['id' => $row['user_id'], 'username' => $row['username'], 'phone' => $row['phone']]]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Token expired']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid token']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing device_token']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>