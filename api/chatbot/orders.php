<?php
/**
 * Chatbot API - Truy Xuất Đơn Hàng
 * Endpoint nội bộ dành cho AI truy vấn (Xác thực qua X-Chatbot-Secret)
 */
header('Content-Type: application/json; charset=utf-8');
require_once '../../database/config.php';

$secretKey = 'MeowTea_Secret_2026_@abcxyz';
$headers = getallheaders();
$providedSecret = $headers['X-Chatbot-Secret'] ?? $_SERVER['HTTP_X_CHATBOT_SECRET'] ?? '';

if ($providedSecret !== $secretKey) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$pdo = getDBConnection();

if ($action === 'recent') {
    $userId = (int)($_GET['user_id'] ?? 0);
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: Thiếu thông tin User ID.']);
        exit;
    }

    // Lấy 5 đơn hàng gần nhất
    try {
        $stmt = $pdo->prepare("SELECT MaOrder, NgayTao, TrangThai, TongTien FROM Orders WHERE MaUser = ? ORDER BY NgayTao DESC LIMIT 5");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'orders' => $orders]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
} 
elseif ($action === 'detail') {
    $orderId = (int)($_GET['order_id'] ?? 0);
    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Lỗi: Mã đơn hàng không hợp lệ.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT o.*, s.TenStore FROM Orders o LEFT JOIN Store s ON o.MaStore = s.MaStore WHERE o.MaOrder = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng.']);
            exit;
        }

        // Kèm thêm items
        $stmt = $pdo->prepare("SELECT oi.*, sp.TenSP FROM Order_Item oi INNER JOIN SanPham sp ON oi.MaSP = sp.MaSP WHERE oi.MaOrder = ?");
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $order['items'] = $items;

        echo json_encode(['success' => true, 'order' => $order]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit;
