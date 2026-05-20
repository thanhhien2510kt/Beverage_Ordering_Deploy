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
    $ho = trim($_POST['ho'] ?? '');
    $ten = trim($_POST['ten'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $dienthoai = trim($_POST['dienthoai'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $role = (int)($_POST['role'] ?? 3);

    if (empty($ho) || empty($ten) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
        exit;
    }

    $pdo = getDBConnection();
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM AppUser WHERE Username = ? OR Email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username hoặc Email đã tồn tại']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO AppUser (Username, Password, Ho, Ten, GioiTinh, Email, DienThoai, DiaChi, MaRole, TrangThai) 
            VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);
    
    $result = $stmt->execute([
        $username,
        $hashedPassword,
        $ho,
        $ten,
        $email,
        $dienthoai,
        $diachi,
        $role
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Thêm người dùng thất bại']);
    }

} catch (PDOException $e) {
    error_log("Database error in create user API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
}
