<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'data' => [], 'message' => ''];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để truy cập');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Staff' && $userRole !== 'Admin') {
        throw new Exception('Bạn không có quyền truy cập trang này');
    }


    $pdo = getDBConnection();


    $sql = "SELECT ov.*, og.TenNhom 
            FROM Option_Value ov 
            INNER JOIN Option_Group og ON ov.MaOptionGroup = og.MaOptionGroup 
            WHERE og.MaOptionGroup = 3
            ORDER BY ov.MaOptionValue DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $toppings = $stmt->fetchAll();

    $response = [
        'success' => true,
        'data' => $toppings,
        'message' => 'Lấy danh sách topping thành công'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in toppings list: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
