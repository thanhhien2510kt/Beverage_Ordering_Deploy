<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Chỉ Admin mới có quyền điều chỉnh giá bán');
    }


    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $newPrice = isset($_POST['price']) ? trim($_POST['price']) : '';


    if (!$productId) {
        throw new Exception('Mã sản phẩm không hợp lệ');
    }

    if (empty($newPrice) || !is_numeric($newPrice) || $newPrice < 0) {
        throw new Exception('Giá bán không hợp lệ');
    }


    $pdo = getDBConnection();


    $sql = "UPDATE SanPham SET GiaNiemYet = ? WHERE MaSP = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newPrice, $productId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Không tìm thấy sản phẩm hoặc giá không thay đổi');
    }

    $response = [
        'success' => true,
        'message' => 'Cập nhật giá bán thành công'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in update price: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
