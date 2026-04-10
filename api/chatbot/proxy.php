<?php
/**
 * Chatbot PHP Proxy Bridge
 * Forward request từ browser → FastAPI service, đính kèm user context từ session
 */
header('Content-Type: application/json; charset=utf-8');

require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Đọc body JSON từ browser
$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Đính kèm thông tin user từ PHP session
$user = getCurrentUser();
$payload['user_id']   = $user['id'] ?? null;
$payload['user_role'] = $user['role_name'] ?? null;

if (empty($payload['session_id'])) {
    $payload['session_id'] = 'session_' . bin2hex(random_bytes(8));
}

try {
    $pdo = getDBConnection();
    // Lưu tin nhắn của user vào DB
    $stmt = $pdo->prepare("INSERT INTO Chat_Message (SessionID, Role, Content) VALUES (?, ?, ?)");
    $stmt->execute([$payload['session_id'], 'user', $payload['message']]);
} catch (Exception $e) {
    // Bỏ qua lỗi DB để không làm gián đoạn chatbot, có thể ghi log nếu cần
}

// FastAPI endpoint
$fastapiUrl = 'http://localhost:8000/chat';

// Secret key để xác thực với FastAPI (phải khớp với .env CHATBOT_SECRET_KEY)
$secretKey = 'MeowTea_Secret_2026_@abcxyz'; // TODO: Đọc từ config nếu muốn

// ĐÓNG SESSION LẠI TRƯỚC KHI CALL CURL ĐỂ TRÁNH LOCKING
session_write_close();

// Gọi FastAPI bằng cURL
$ch = curl_init($fastapiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-Chatbot-Secret: ' . $secretKey,
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 5,
]);

$result   = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    // LOG lỗi để debug
    $logMsg = date('Y-m-d H:i:s') . " | HTTP=$httpCode | cURL Error: $curlError\n";
    file_put_contents(__DIR__ . '/chatbot_debug.log', $logMsg, FILE_APPEND | LOCK_EX);

    http_response_code(503);
    echo json_encode([
        'reply'      => 'Xin lỗi bạn, chatbot đang tạm ngưng hoạt động. Vui lòng thử lại sau! 🙏',
        'session_id' => $payload['session_id'] ?? '',
        'actions'    => [],
        'error'      => $curlError   // trả về lỗi thật để debug
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lưu tin nhắn của bot vào DB
if ($httpCode >= 200 && $httpCode < 300) {
    $responseJson = json_decode($result, true);
    if (isset($responseJson['reply'])) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO Chat_Message (SessionID, Role, Content) VALUES (?, ?, ?)");
            $stmt->execute([$payload['session_id'], 'assistant', $responseJson['reply']]);
        } catch (Exception $e) {
            // Bỏ qua lỗi DB
        }
    }
}

http_response_code($httpCode);
echo $result;
