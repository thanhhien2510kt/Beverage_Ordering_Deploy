<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Chỉ Admin mới có quyền xóa sản phẩm');
    }


    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;


    if (!$productId || $productId <= 0) {
        throw new Exception('Mã sản phẩm không hợp lệ');
    }


    $pdo = getDBConnection();


    $stmt = $pdo->prepare("SELECT MaSP, TenSP FROM SanPham WHERE MaSP = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Sản phẩm không tồn tại');
    }


    $sql = "UPDATE SanPham SET TrangThai = 0 WHERE MaSP = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);

    $response = [
        'success' => true,
        'message' => 'Xóa sản phẩm "' . $product['tensp'] . '" thành công'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in delete product: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
