<?php
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
    

    if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'staff') {
        throw new Exception('Bạn không có quyền thực hiện thao tác này');
    }

    $orderId = (int)($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        throw new Exception('Mã đơn hàng không hợp lệ');
    }

    $pdo = getDBConnection();
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
            INNER JOIN AppUser u ON o.MaUser = u.MaUser
            WHERE o.MaOrder = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Đơn hàng không tồn tại');
    }

    $order['OrderCode'] = '#MTF' . str_pad($order['maorder'], 5, '0', STR_PAD_LEFT);
    $order['NgayTaoFormatted'] = date('d/m/Y H:i:s', strtotime($order['ngaytao']));

    $order['CustomerName'] = trim(($order['ho'] ?? '') . ' ' . ($order['ten'] ?? ''));
    if (empty($order['CustomerName'])) {
        $order['CustomerName'] = $order['username'];
    }

    $paymentMethodName = 'Chưa xác định';
    $paymentId = $order['mapayment'] ?? null;
    if ($paymentId) {
        $st = $pdo->prepare("SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?");
        $st->execute([$paymentId]);
        $pm = $st->fetch();
        if ($pm) {
            $paymentMethodName = $pm['tenpayment'];
        }
    }
    $order['PaymentMethod'] = $paymentMethodName;
    if (empty($order['nguoinhan'])) {
        $order['nguoinhan'] = $order['CustomerName'];
    }
    if (empty($order['dienthoaigiao'])) {
        $order['dienthoaigiao'] = $order['dienthoai'] ?? '';
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
        $st->execute([$item['maorderitem']]);
        $item['options'] = $st->fetchAll(PDO::FETCH_ASSOC);
        $item['giacoban'] = $item['gianiemyet'];
        $itemTotal = (float)$item['gianiemyet'] * (int)$item['soluong'];
        foreach ($item['options'] as $opt) {
            $itemTotal += (float)$opt['giathem'] * (int)$item['soluong'];
        }
        $item['ItemTotal'] = $itemTotal;
        $subtotal += $itemTotal;
    }
    unset($item);

    $order['items'] = $items;
    $order['Subtotal'] = $subtotal;
    $order['phivanchuyen'] = (float)($order['phivanchuyen'] ?? 0);
    $order['giamgia'] = (float)($order['giamgia'] ?? 0);
    $order['tongtien'] = (float)$order['tongtien'];

    $response = ['success' => true, 'message' => 'OK', 'order' => $order];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
