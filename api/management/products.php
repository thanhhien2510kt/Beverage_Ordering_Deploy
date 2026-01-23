<?php
/**
 * Management Products API
 * List all products for management page
 */

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


    $sql = "SELECT sp.*, c.TenCategory 
            FROM SanPham sp 
            INNER JOIN Category c ON sp.MaCategory = c.MaCategory 
            WHERE sp.TrangThai = 1
            ORDER BY sp.MaSP DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();

    $response = [
        'success' => true,
        'data' => $products,
        'message' => 'Lấy danh sách sản phẩm thành công'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in products list: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
