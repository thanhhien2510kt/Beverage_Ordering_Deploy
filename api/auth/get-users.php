<?php
/**
 * Get Users API (Admin/Staff Only)
 * Get list of all users for dropdown filter
 */

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'users' => []];

try {
    if (!isLoggedIn()) {
        throw new Exception('Bạn cần đăng nhập để xem danh sách người dùng');
    }

    $currentUser = getCurrentUser();
    $userRole = $currentUser['role_name'] ?? '';
    
    // Check if user is admin or staff
    if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'staff') {
        throw new Exception('Bạn không có quyền thực hiện thao tác này');
    }

    $pdo = getDBConnection();

    // Get all users (excluding admin/staff for cleaner filter)
    $sql = "SELECT u.MaUser, u.Username, u.Ho, u.Ten, r.TenRole
            FROM User u
            INNER JOIN Role r ON u.MaRole = r.MaRole
            WHERE u.TrangThai = 1
            ORDER BY u.Ho, u.Ten, u.Username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'message' => 'Lấy danh sách người dùng thành công',
        'users' => $users
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
