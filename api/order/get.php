<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'orders' => [], 'total' => 0, 'total_pages' => 0, 'page' => 1, 'per_page' => 10];
try {
    if (!isLoggedIn()) {
        throw new Exception('Bạn cần đăng nhập để xem đơn hàng');
    }

    $user = getCurrentUser();
    $userId = $user['id'];

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = min(20, max(1, (int) ($_GET['per_page'] ?? 10)));
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $days = (int) ($_GET['days'] ?? 30);
    if (!in_array($days, [7, 30, 90], true)) {
        $days = 30;
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

    $where = ["o.MaUser = ?"];
    $params = [$userId];

    if ($days > 0) {
        $where[] = "o.NgayTao >= ?";
        $params[] = date('Y-m-d H:i:s', strtotime("-$days days"));
    }

    if ($status !== '') {
        $statusMap = [
            'payment_received' => ['Payment_Received', 'Pending'],
            'received' => ['Payment_Received', 'Pending'],
            'processing' => ['Processing', 'Order_Received'],
            'delivering' => ['Delivering'],
            'completed' => ['Completed'],
            'cancelled' => ['Cancelled', 'Store_Cancelled']
        ];
        if (isset($statusMap[$status])) {
            $placeholders = implode(',', array_fill(0, count($statusMap[$status]), '?'));
            $where[] = "o.TrangThai IN ($placeholders)";
            $params = array_merge($params, $statusMap[$status]);
        }
    }

    if ($search !== '') {
        $searchClean = preg_replace('/^#?MTF?/i', '', $search);
        $searchClean = ltrim($searchClean, '0');
        if (is_numeric($searchClean) && (int) $searchClean > 0) {
            $where[] = "o.MaOrder = ?";
            $params[] = (int) $searchClean;
        } else {
            $where[] = "o.MaOrder LIKE ?";
            $params[] = '%' . $search . '%';
        }
    }

    $whereClause = implode(' AND ', $where);

    $sqlCount = "SELECT COUNT(*) AS cnt FROM Orders o WHERE $whereClause";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $total = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
    $page = min($page, max(1, $totalPages));
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT o.*, s.TenStore
            FROM Orders o
            INNER JOIN Store s ON o.MaStore = s.MaStore
            WHERE $whereClause
            ORDER BY o.NgayTao DESC
            LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $orderId = $order['maorder'];
        $order['OrderCode'] = '#MTF' . str_pad($orderId, 5, '0', STR_PAD_LEFT);

        $paymentMethodName = 'Chưa xác định';
        $paymentId = $order['mapayment'] ?? $_SESSION['order_payment_' . $orderId] ?? null;
        if ($paymentId) {
            $st = $pdo->prepare("SELECT TenPayment FROM Payment_Method WHERE MaPayment = ?");
            $st->execute([$paymentId]);
            $pm = $st->fetch();
            if ($pm) {
                $paymentMethodName = $pm['tenpayment'];
            }
        }
        $order['PaymentMethod'] = $paymentMethodName;


        $st = $pdo->prepare("SELECT COALESCE(SUM(SoLuong), 0) AS n FROM Order_Item WHERE MaOrder = ?");
        $st->execute([$orderId]);
        $order['ItemCount'] = (int) $st->fetch(PDO::FETCH_ASSOC)['n'];

        $order['NgayTaoFormatted'] = date('d/m/Y', strtotime($order['ngaytao']));
        $order['NgayTaoTime'] = date('H:i:s', strtotime($order['ngaytao']));
    }
    unset($order);

    $response = [
        'success' => true,
        'message' => 'Lấy danh sách đơn hàng thành công',
        'orders' => $orders,
        'total' => $total,
        'total_pages' => $totalPages,
        'page' => $page,
        'per_page' => $perPage
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
