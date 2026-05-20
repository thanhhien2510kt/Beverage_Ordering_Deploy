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
    $ho = trim($_POST['ho'] ?? '');
    $ten = trim($_POST['ten'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $dienthoai = trim($_POST['dienthoai'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $role = (int)($_POST['role'] ?? 0);

    if ($id <= 0 || empty($ho) || empty($ten) || empty($username) || empty($email) || $role <= 0) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc']);
        exit;
    }

    $pdo = getDBConnection();
    
    // Check if username or email already exists for another user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM AppUser WHERE (Username = ? OR Email = ?) AND MaUser != ?");
    $stmt->execute([$username, $email, $id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username hoặc Email đã tồn tại']);
        exit;
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE AppUser SET Username = ?, Password = ?, Ho = ?, Ten = ?, Email = ?, DienThoai = ?, DiaChi = ?, MaRole = ? WHERE MaUser = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$username, $hashedPassword, $ho, $ten, $email, $dienthoai, $diachi, $role, $id]);
    } else {
        $sql = "UPDATE AppUser SET Username = ?, Ho = ?, Ten = ?, Email = ?, DienThoai = ?, DiaChi = ?, MaRole = ? WHERE MaUser = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$username, $ho, $ten, $email, $dienthoai, $diachi, $role, $id]);
    }

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật người dùng thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật người dùng thất bại']);
    }

} catch (PDOException $e) {
    error_log("Database error in update user API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
}
