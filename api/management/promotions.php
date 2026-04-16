<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'data' => [], 'message' => ''];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để truy cập');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Bạn không có quyền truy cập trang này');
    }


    $pdo = getDBConnection();


    $sql = "SELECT * FROM Promotion ORDER BY MaPromotion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $promotions = $stmt->fetchAll();

    $response = [
        'success' => true,
        'data' => $promotions,
        'message' => 'Lấy danh sách khuyến mãi thành công'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in promotions list: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
