<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../database/config.php';
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['user_role_name'] ?? '') !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $id = (int)($_POST['id'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }

    // Protect admin from locking themselves out
    if ($id == $_SESSION['user_id'] && $status == 0) {
        echo json_encode(['success' => false, 'message' => 'Không thể khóa chính tài khoản của bạn']);
        exit;
    }

    $pdo = getDBConnection();
    
    $sql = "UPDATE AppUser SET TrangThai = ? WHERE MaUser = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$status, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái thất bại']);
    }

} catch (PDOException $e) {
    error_log("Database error in update user status API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
}
