<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../database/config.php';
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user_role_name'] ?? '';
if ($userRole !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Fetch all users with their roles
    $sql = "SELECT u.MaUser, u.Username, u.Email, u.Ho, u.Ten, u.DienThoai, u.DiaChi, u.TrangThai, r.TenRole 
            FROM AppUser u 
            LEFT JOIN Role r ON u.MaRole = r.MaRole 
            ORDER BY u.MaUser DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (PDOException $e) {
    error_log("Database error in get users API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
}
