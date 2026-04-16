<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];
try {
    if (!isLoggedIn()) {
        throw new Exception('Vui lòng đăng nhập');
    }

    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

    if (!$orderId) {
        throw new Exception('Không tìm thấy mã đơn hàng');
    }

    $pdo = getDBConnection();

    // Verify order exists and belongs to user
    $user = getCurrentUser();
    $stmt = $pdo->prepare("SELECT TrangThai FROM Orders WHERE MaOrder = ? AND MaUser = ?");
    $stmt->execute([$orderId, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại hoặc không thuộc về bạn');
    }

    // Update status to Payment_Received
    if ($order['trangthai'] === 'Pending') {
        $updateStmt = $pdo->prepare("UPDATE Orders SET TrangThai = 'Payment_Received' WHERE MaOrder = ?");
        $updateStmt->execute([$orderId]);
    }

    $response['success'] = true;
    $response['message'] = 'Thanh toán thành công!';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
