<?php
header('Content-Type: application/json');
require_once '../../database/config.php';
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    $gioiTinh = isset($_POST['gioi_tinh']) ? trim($_POST['gioi_tinh']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $dienThoai = isset($_POST['dien_thoai']) ? trim($_POST['dien_thoai']) : null;
    $diaChi = isset($_POST['dia_chi']) ? trim($_POST['dia_chi']) : null;

    if ($gioiTinh !== null && $gioiTinh !== '' && !in_array($gioiTinh, ['M', 'F', 'O'])) {
        throw new Exception('Giới tính không hợp lệ');
    }

    if ($email !== null && $email !== '') {
        if (strlen($email) > 100) {
            throw new Exception('Email không được vượt quá 100 ký tự');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
    }

    if ($dienThoai !== null && $dienThoai !== '') {
        if (strlen($dienThoai) > 20) {
            throw new Exception('Số điện thoại không được vượt quá 20 ký tự');
        }
        if (!preg_match('/^[0-9+\-\s()]+$/', $dienThoai)) {
            throw new Exception('Số điện thoại không hợp lệ');
        }
    }

    if ($diaChi !== null && $diaChi !== '') {
        if (strlen($diaChi) > 500) {
            throw new Exception('Địa chỉ không được vượt quá 500 ký tự');
        }
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT u.Email, u.DienThoai, u.DiaChi, r.TenRole 
                          FROM User u 
                          INNER JOIN Role r ON u.MaRole = r.MaRole 
                          WHERE u.MaUser = ? AND u.TrangThai = 1");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();

    if (!$currentUser) {
        throw new Exception('Không tìm thấy người dùng');
    }

    if ($email !== null && $email !== '' && $email !== $currentUser['Email']) {
        $stmt = $pdo->prepare("SELECT MaUser FROM User WHERE Email = ? AND MaUser != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            throw new Exception('Email đã được sử dụng. Vui lòng sử dụng email khác');
        }
    }

    if ($dienThoai !== null && $dienThoai !== '' && $dienThoai !== $currentUser['DienThoai']) {
        $stmt = $pdo->prepare("SELECT MaUser FROM User WHERE DienThoai = ? AND MaUser != ?");
        $stmt->execute([$dienThoai, $userId]);
        if ($stmt->fetch()) {
            throw new Exception('Số điện thoại đã được sử dụng. Vui lòng sử dụng số khác');
        }
    }

    $updateFields = [];
    $updateValues = [];

    if ($gioiTinh !== null) {
        $updateFields[] = "GioiTinh = ?";
        $updateValues[] = ($gioiTinh === '') ? null : $gioiTinh;
    }

    if ($email !== null) {
        $updateFields[] = "Email = ?";
        $updateValues[] = ($email === '') ? null : $email;
    }

    if ($dienThoai !== null) {
        $updateFields[] = "DienThoai = ?";
        $updateValues[] = ($dienThoai === '') ? null : $dienThoai;
    }

    if ($diaChi !== null) {
        $userRole = strtolower($currentUser['TenRole'] ?? '');
        if ($userRole === 'customer') {
            $updateFields[] = "DiaChi = ?";
            $updateValues[] = ($diaChi === '') ? null : $diaChi;
        }
    }

    if (empty($updateFields)) {
        throw new Exception('Không có thông tin nào để cập nhật');
    }

    $updateValues[] = $userId;

    $sql = "UPDATE User SET " . implode(', ', $updateFields) . " WHERE MaUser = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($updateValues);

    $stmt = $pdo->prepare("SELECT u.*, r.TenRole 
                          FROM User u 
                          INNER JOIN Role r ON u.MaRole = r.MaRole 
                          WHERE u.MaUser = ? AND u.TrangThai = 1");
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch();

    if (!$updatedUser) {
        throw new Exception('Không thể lấy thông tin người dùng sau khi cập nhật');
    }

    $_SESSION['user_gioi_tinh'] = $updatedUser['GioiTinh'] ?? null;
    $_SESSION['user_email'] = $updatedUser['Email'] ?? '';
    $_SESSION['user_phone'] = $updatedUser['DienThoai'] ?? '';
    $_SESSION['user_dia_chi'] = $updatedUser['DiaChi'] ?? '';

    $response = [
        'success' => true,
        'message' => 'Cập nhật thông tin thành công!',
        'user' => [
            'gioi_tinh' => $updatedUser['GioiTinh'] ?? null,
            'email' => $updatedUser['Email'] ?? '',
            'phone' => $updatedUser['DienThoai'] ?? '',
            'dia_chi' => $updatedUser['DiaChi'] ?? ''
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in update-profile: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
