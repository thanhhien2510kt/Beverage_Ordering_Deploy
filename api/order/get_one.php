<?php
/**
 * Get Single Order Detail API
 * For "Xem chi tiết" order modal
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
        throw new Exception('Bạn cần đăng nhập để xem đơn hàng');
    }

    $currentUser = getCurrentUser();
    $userId = $currentUser['id'] ?? null;
    if (!$userId) {
        throw new Exception('ID người dùng không tồn tại');
    }

    $orderId = (int)($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        throw new Exception('Mã đơn hàng không hợp lệ');
    }

    $pdo = getDBConnection();

    $sql = "SELECT o.*, s.TenStore
            FROM Orders o
            INNER JOIN Store s ON o.MaStore = s.MaStore
            WHERE o.MaOrder = ? AND o.MaUser = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }

    $order['OrderCode'] = '#MTF' . str_pad($order['MaOrder'], 5, '0', STR_PAD_LEFT);
    $order['NgayTaoFormatted'] = date('d/m/Y H:i:s', strtotime($order['NgayTao']));

    $paymentMethodName = 'Chưa xác định';
    $paymentId = $order['MaPayment'] ?? $_SESSION['order_payment_' . $orderId] ?? null;
    if ($paymentId) {
        $st = $pdo->prepare("SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?");
        $st->execute([$paymentId]);
        $pm = $st->fetch();
        if ($pm) {
            $paymentMethodName = $pm['TenPayment'];
        }
    }
    $order['PaymentMethod'] = $paymentMethodName;

    if (empty($order['NguoiNhan']) || empty($order['DienThoaiGiao'])) {
        $st = $pdo->prepare("SELECT Ho, Ten, DienThoai FROM User WHERE MaUser = ?");
        $st->execute([$order['MaUser']]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            if (empty($order['NguoiNhan'])) {
                $order['NguoiNhan'] = getFullName($u['Ho'] ?? '', $u['Ten'] ?? '');
            }
            if (empty($order['DienThoaiGiao'])) {
                $order['DienThoaiGiao'] = $u['DienThoai'] ?? '';
            }
        }
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
