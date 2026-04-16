<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => '', 'promotion_id' => null];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Chỉ Admin mới có quyền thêm khuyến mãi mới');
    }


    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    $loaiGiamGia = isset($_POST['loai_giam_gia']) ? trim($_POST['loai_giam_gia']) : 'Percentage';
    $giaTri = isset($_POST['gia_tri']) ? trim($_POST['gia_tri']) : '';
    $giaTriToiDa = isset($_POST['gia_tri_toi_da']) && !empty($_POST['gia_tri_toi_da']) ? trim($_POST['gia_tri_toi_da']) : null;
    $ngayBatDau = isset($_POST['ngay_bat_dau']) ? trim($_POST['ngay_bat_dau']) : null;
    $ngayKetThuc = isset($_POST['ngay_ket_thuc']) ? trim($_POST['ngay_ket_thuc']) : null;
    $trangThai = isset($_POST['trang_thai']) ? (int)$_POST['trang_thai'] : 1;


    if (empty($code)) {
        throw new Exception('Vui lòng nhập mã khuyến mãi');
    }

    if (empty($giaTri) || !is_numeric($giaTri) || $giaTri < 0) {
        throw new Exception('Giá trị giảm giá không hợp lệ');
    }

    if ($loaiGiamGia === 'Percentage' && ($giaTri > 100 || $giaTri < 0)) {
        throw new Exception('Phần trăm giảm giá phải từ 0 đến 100');
    }


    if ($loaiGiamGia === 'Percentage' && $giaTriToiDa !== null) {
        $giaTriToiDaFloat = (float)$giaTriToiDa;
        if ($giaTriToiDaFloat < 0) {
            throw new Exception('Giá trị tối đa phải lớn hơn hoặc bằng 0');
        }
    }


    if ($loaiGiamGia !== 'Percentage') {
        $giaTriToiDa = null;
    }


    if (!empty($ngayBatDau) && !empty($ngayKetThuc)) {
        $startDate = strtotime($ngayBatDau);
        $endDate = strtotime($ngayKetThuc);
        if ($startDate === false || $endDate === false) {
            throw new Exception('Ngày tháng không hợp lệ');
        }
        if ($endDate < $startDate) {
            throw new Exception('Ngày kết thúc phải sau ngày bắt đầu');
        }
    }


    $pdo = getDBConnection();


    $stmt = $pdo->prepare("SELECT MaPromotion FROM Promotion WHERE Code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        throw new Exception('Mã khuyến mãi đã tồn tại');
    }


    $ngayBatDauFormatted = !empty($ngayBatDau) ? date('Y-m-d H:i:s', strtotime($ngayBatDau)) : null;
    $ngayKetThucFormatted = !empty($ngayKetThuc) ? date('Y-m-d H:i:s', strtotime($ngayKetThuc)) : null;


    $giaTriToiDaFormatted = ($giaTriToiDa !== null && $loaiGiamGia === 'Percentage') ? (float)$giaTriToiDa : null;


    $sql = "INSERT INTO Promotion (Code, LoaiGiamGia, GiaTri, GiaTriToiDa, NgayBatDau, NgayKetThuc, TrangThai) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code, $loaiGiamGia, $giaTri, $giaTriToiDaFormatted, $ngayBatDauFormatted, $ngayKetThucFormatted, $trangThai]);

    $promotionId = $pdo->lastInsertId();

    $response = [
        'success' => true,
        'message' => 'Thêm khuyến mãi mới thành công',
        'promotion_id' => $promotionId
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in create promotion: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
