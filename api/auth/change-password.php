<?php
header('Content-Type: application/json');
require_once '../../database/config.php';
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $response['message'] = 'Bạn cần đăng nhập để thực hiện thao tác này';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        throw new Exception('Không tìm thấy thông tin người dùng');
    }

    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($currentPassword)) {
        throw new Exception('Vui lòng nhập mật khẩu hiện tại');
    }

    if (empty($newPassword)) {
        throw new Exception('Vui lòng nhập mật khẩu mới');
    }

    if (strlen($newPassword) < 6) {
        throw new Exception('Mật khẩu mới phải có ít nhất 6 ký tự');
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception('Mật khẩu xác nhận không khớp');
    }

    if ($currentPassword === $newPassword) {
        throw new Exception('Mật khẩu mới phải khác mật khẩu hiện tại');
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT Password FROM AppUser WHERE MaUser = ? AND TrangThai = 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Không tìm thấy người dùng');
    }

    $passwordMatch = false;
    
    if (strpos($user['password'], '$2y$') === 0) {
        $passwordMatch = password_verify($currentPassword, $user['password']);
    } else {
        $passwordMatch = ($user['password'] === $currentPassword);
    }

    if (!$passwordMatch) {
        throw new Exception('Mật khẩu hiện tại không đúng');
    }

    $hashedPassword = hashPassword($newPassword);

    $stmt = $pdo->prepare("UPDATE AppUser SET Password = ? WHERE MaUser = ?");
    $stmt->execute([$hashedPassword, $userId]);

    $response = [
        'success' => true,
        'message' => 'Đổi mật khẩu thành công!'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in change-password: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
