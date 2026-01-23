<?php
header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'discount' => 0, 'promotion' => null];
try {
    if (!isLoggedIn()) {
        throw new Exception('Bạn cần đăng nhập để sử dụng mã khuyến mãi');
    }

    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;

    if (empty($code)) {
        throw new Exception('Vui lòng nhập mã khuyến mãi');
    }

    if ($subtotal <= 0) {
        throw new Exception('Giá trị đơn hàng không hợp lệ');
    }

    $pdo = getDBConnection();

    $sql = "SELECT * FROM Promotion 
            WHERE Code = ? AND TrangThai = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $promotion = $stmt->fetch();

    if (!$promotion) {
        throw new Exception('Mã khuyến mãi không tồn tại hoặc đã bị vô hiệu hóa');
    }

    $now = new DateTime();
    
    if (!empty($promotion['NgayBatDau'])) {
        $startDate = new DateTime($promotion['NgayBatDau']);
        if ($now < $startDate) {
            throw new Exception('Mã khuyến mãi chưa có hiệu lực');
        }
    }

    if (!empty($promotion['NgayKetThuc'])) {
        $endDate = new DateTime($promotion['NgayKetThuc']);
        if ($now > $endDate) {
            throw new Exception('Mã khuyến mãi đã hết hạn');
        }
    }

    $loaiGiamGia = $promotion['LoaiGiamGia'] ?? 'Percentage';
    $giaTri = (float)$promotion['GiaTri'];
    $giaTriToiDa = isset($promotion['GiaTriToiDa']) && $promotion['GiaTriToiDa'] !== null ? (float)$promotion['GiaTriToiDa'] : null;
    $discount = 0;

    if ($loaiGiamGia === 'Percentage') {
        $discount = ($subtotal * $giaTri) / 100;
        
        if ($giaTriToiDa !== null && $giaTriToiDa > 0) {
            if ($discount > $giaTriToiDa) {
                $discount = $giaTriToiDa;
            }
        }
        
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
    } else {
        $discount = $giaTri;
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
    }

    $response = [
        'success' => true,
        'message' => 'Áp dụng mã khuyến mãi thành công',
        'discount' => $discount,
        'promotion' => [
            'id' => $promotion['MaPromotion'],
            'code' => $promotion['Code'],
            'loai_giam_gia' => $loaiGiamGia,
            'gia_tri' => $giaTri,
            'discount_amount' => $discount
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in validate promotion: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
