<?php
/**
 * Update Order Status API (Admin/Staff Only)
 * Update order status (accept or cancel)
 * POST: order_id, action (accept|cancel)
 */

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức yêu cầu không hợp lệ');
    }

    if (!isLoggedIn()) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }

    $currentUser = getCurrentUser();
    $userRole = $currentUser['role_name'] ?? '';
    
    // Check if user is admin or staff
    if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'staff') {
        throw new Exception('Bạn không có quyền thực hiện thao tác này');
    }

    $orderId = (int)($_POST['order_id'] ?? 0);
    $action = trim($_POST['action'] ?? '');

    if ($orderId <= 0) {
        throw new Exception('Mã đơn hàng không hợp lệ');
    }

    if (!in_array($action, ['accept', 'cancel'], true)) {
        throw new Exception('Hành động không hợp lệ. Phải là "accept" hoặc "cancel"');
    }

    $pdo = getDBConnection();

    // Get current order status
    $stmt = $pdo->prepare("SELECT TrangThai FROM Orders WHERE MaOrder = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }

    $currentStatus = strtolower($order['TrangThai']);

    // Only allow status update if current status is Payment_Received or Pending
    if ($currentStatus !== 'payment_received' && $currentStatus !== 'pending') {
        throw new Exception('Chỉ có thể cập nhật trạng thái đơn hàng đang ở trạng thái "Đã nhận thanh toán"');
    }

    $now = new DateTime();
    $deliverAt = (clone $now)->add(new DateInterval('PT' . rand(15, 20) . 'M'));
    $completeAt = (clone $deliverAt)->add(new DateInterval('PT' . rand(10, 15) . 'M'));
    
    $pdo->beginTransaction();

    // Determine new status and timestamps
    if ($action === 'accept') {
        $stmt = $pdo->prepare("
            UPDATE Orders 
            SET TrangThai = 'Processing',
                ThoiDiemNhanDon = ?,
                ThoiDiemGiaoHang = ?,
                ThoiDiemNhanHang = ?,
                ThoiDiemHuyDon = NULL
            WHERE MaOrder = ?
        ");
        $stmt->execute([
            $now->format('Y-m-d H:i:s'),
            $deliverAt->format('Y-m-d H:i:s'),
            $completeAt->format('Y-m-d H:i:s'),
            $orderId
        ]);
        $statusText = 'Đã nhận đơn';
        $newStatus = 'Processing';
    } else { // cancel
        $stmt = $pdo->prepare("
            UPDATE Orders 
            SET TrangThai = 'Store_Cancelled',
                ThoiDiemHuyDon = ?,
                ThoiDiemGiaoHang = NULL,
                ThoiDiemNhanHang = NULL,
                ThoiDiemNhanDon = NULL
            WHERE MaOrder = ?
        ");
        $stmt->execute([
            $now->format('Y-m-d H:i:s'),
            $orderId
        ]);
        $statusText = 'Đã hủy đơn';
        $newStatus = 'Store_Cancelled';
    }

    $pdo->commit();

    $response = [
        'success' => true,
        'message' => "Cập nhật trạng thái đơn hàng thành công: $statusText",
        'new_status' => $newStatus
    ];

    if ($action === 'accept') {
        $response['thoi_diem_nhan_don'] = $now->format('Y-m-d H:i:s');
        $response['thoi_diem_giao_hang'] = $deliverAt->format('Y-m-d H:i:s');
        $response['thoi_diem_nhan_hang'] = $completeAt->format('Y-m-d H:i:s');
    } else {
        $response['thoi_diem_huy_don'] = $now->format('Y-m-d H:i:s');
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
