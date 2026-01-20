<?php
/**
 * Get Single Order Detail API (Admin/Staff Only)
 * For "Xem chi tiết" order modal in manage orders
 * Query: id (MaOrder)
 */

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'order' => null];

try {
    if (!isLoggedIn()) {
        throw new Exception('User not logged in');
    }

    $currentUser = getCurrentUser();
    $userRole = $currentUser['role_name'] ?? '';
    
    // Check if user is admin or staff
    if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'staff') {
        throw new Exception('Access denied. Admin or Staff role required.');
    }

    $orderId = (int)($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        throw new Exception('Invalid order id');
    }

    $pdo = getDBConnection();
    
    // Automatically progress orders based on scheduled timestamps
    try {
        $pdo->exec("
            UPDATE Orders
            SET TrangThai = 'Delivering'
            WHERE TrangThai IN ('Processing', 'Order_Received')
              AND ThoiDiemGiaoHang IS NOT NULL
              AND ThoiDiemGiaoHang <= NOW()
        ");

        $pdo->exec("
            UPDATE Orders
            SET TrangThai = 'Completed'
            WHERE TrangThai = 'Delivering'
              AND ThoiDiemNhanHang IS NOT NULL
              AND ThoiDiemNhanHang <= NOW()
        ");
    } catch (Exception $e) {
        error_log("Auto-progress error: " . $e->getMessage());
    }

    $sql = "SELECT o.*, s.TenStore, u.Username, u.Ho, u.Ten, u.Email, u.DienThoai
            FROM Orders o
            INNER JOIN Store s ON o.MaStore = s.MaStore
            INNER JOIN User u ON o.MaUser = u.MaUser
            WHERE o.MaOrder = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    $order['OrderCode'] = '#MTF' . str_pad($order['MaOrder'], 5, '0', STR_PAD_LEFT);
    $order['NgayTaoFormatted'] = date('d/m/Y H:i:s', strtotime($order['NgayTao']));

    // Format customer name
    $order['CustomerName'] = trim(($order['Ho'] ?? '') . ' ' . ($order['Ten'] ?? ''));
    if (empty($order['CustomerName'])) {
        $order['CustomerName'] = $order['Username'];
    }

    $paymentMethodName = 'Chưa xác định';
    $paymentId = $order['MaPayment'] ?? null;
    if ($paymentId) {
        $st = $pdo->prepare("SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?");
        $st->execute([$paymentId]);
        $pm = $st->fetch();
        if ($pm) {
            $paymentMethodName = $pm['TenPayment'];
        }
    }
    $order['PaymentMethod'] = $paymentMethodName;

    // Get receiver info from order or fallback to user info
    if (empty($order['NguoiNhan'])) {
        $order['NguoiNhan'] = $order['CustomerName'];
    }
    if (empty($order['DienThoaiGiao'])) {
        $order['DienThoaiGiao'] = $order['DienThoai'] ?? '';
    }

    $sql = "SELECT oi.*, sp.TenSP, sp.HinhAnh, sp.GiaCoBan AS GiaThamKhao
            FROM Order_Item oi
            INNER JOIN SanPham sp ON oi.MaSP = sp.MaSP
            WHERE oi.MaOrder = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    foreach ($items as &$item) {
        $st = $pdo->prepare("SELECT oio.GiaThem, ov.TenGiaTri, og.TenNhom
                FROM Order_Item_Option oio
                INNER JOIN Option_Value ov ON oio.MaOptionValue = ov.MaOptionValue
                INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup
                WHERE oio.MaOrderItem = ?");
        $st->execute([$item['MaOrderItem']]);
        $item['options'] = $st->fetchAll(PDO::FETCH_ASSOC);

        // Use GiaNiemYet (price at time of order) instead of GiaCoBan
        $item['GiaCoBan'] = $item['GiaNiemYet']; // For compatibility with frontend
        $itemTotal = (float)$item['GiaNiemYet'] * (int)$item['SoLuong'];
        foreach ($item['options'] as $opt) {
            $itemTotal += (float)$opt['GiaThem'] * (int)$item['SoLuong'];
        }
        $item['ItemTotal'] = $itemTotal;
        $subtotal += $itemTotal;
    }
    unset($item);

    $order['items'] = $items;
    $order['Subtotal'] = $subtotal;
    $order['PhiVanChuyen'] = (float)($order['PhiVanChuyen'] ?? 0);
    $order['GiamGia'] = (float)($order['GiamGia'] ?? 0);
    $order['TongTien'] = (float)$order['TongTien'];

    $response = ['success' => true, 'message' => 'OK', 'order' => $order];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
