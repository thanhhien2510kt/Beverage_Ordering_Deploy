<?php
header('Content-Type: application/json');
require_once '../../database/config.php';
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
if (session_status() === PHP_SESSION_NONE) { session_start(); }
}

$response = ['success' => false, 'message' => ''];
try {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ho = isset($_POST['ho']) ? trim($_POST['ho']) : '';
    $ten = isset($_POST['ten']) ? trim($_POST['ten']) : '';
    $gioiTinh = null;
    $dienThoai = isset($_POST['dien_thoai']) ? trim($_POST['dien_thoai']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;

    if (empty($username)) {
        throw new Exception('Vui lòng nhập tên đăng nhập');
    }

    if (strlen($username) < 3 || strlen($username) > 100) {
        throw new Exception('Tên đăng nhập phải có từ 3 đến 100 ký tự');
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception('Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới');
    }

    if (empty($password)) {
        throw new Exception('Vui lòng nhập mật khẩu');
    }

    if (strlen($password) < 6) {
        throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
    }

    if (empty($ho)) {
        throw new Exception('Vui lòng nhập họ');
    }

    if (strlen($ho) > 50) {
        throw new Exception('Họ không được vượt quá 50 ký tự');
    }

    if (empty($ten)) {
        throw new Exception('Vui lòng nhập tên');
    }

    if (strlen($ten) > 50) {
        throw new Exception('Tên không được vượt quá 50 ký tự');
    }

    if ($dienThoai !== null && !empty($dienThoai)) {
        $phoneDigits = preg_replace('/\D/', '', $dienThoai);
        
        if (strlen($phoneDigits) !== 10) {
            throw new Exception('Số điện thoại phải có đúng 10 chữ số');
        }
        
        if (!preg_match('/^0\d{9}$/', $phoneDigits)) {
            throw new Exception('Số điện thoại phải bắt đầu bằng số 0 và có 10 chữ số');
        }
    }

    if ($email !== null && !empty($email)) {
        if (strlen($email) > 100) {
            throw new Exception('Email không được vượt quá 100 ký tự');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
    }

    $pdo = getDBConnection();

    $stmt = $pdo->prepare("SELECT MaUser FROM AppUser WHERE Username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        throw new Exception('Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác');
    }

    if ($email !== null && !empty($email)) {
        $stmt = $pdo->prepare("SELECT MaUser FROM AppUser WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email đã được sử dụng. Vui lòng sử dụng email khác');
        }
    }

    if ($dienThoai !== null && !empty($dienThoai)) {
        $stmt = $pdo->prepare("SELECT MaUser FROM AppUser WHERE DienThoai = ?");
        $stmt->execute([$dienThoai]);
        if ($stmt->fetch()) {
            throw new Exception('Số điện thoại đã được sử dụng. Vui lòng sử dụng số khác');
        }
    }

    $stmt = $pdo->prepare("SELECT MaRole FROM Role WHERE TenRole = 'Customer' LIMIT 1");
    $stmt->execute();
    $role = $stmt->fetch();

    if (!$role) {
        throw new Exception('Không tìm thấy role Customer. Vui lòng liên hệ quản trị viên');
    }

    $maRole = $role['marole'];

    $hashedPassword = hashPassword($password);

    $sql = "INSERT INTO AppUser (Username, Password, Ho, Ten, GioiTinh, DienThoai, Email, TrangThai, MaRole) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 3)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $username,
        $hashedPassword,
        $ho,
        $ten,
        $gioiTinh,
        $dienThoai,
        $email,
        $maRole
    ]);

    $newUserId = $pdo->lastInsertId();
    $fullName = trim($ho . ' ' . $ten);

    $response = [
        'success' => true,
        'message' => 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.',
        'user' => [
            'id' => $newUserId,
            'username' => $username,
            'ho' => $ho,
            'ten' => $ten,
            'name' => $fullName,
            'gioi_tinh' => $gioiTinh,
            'email' => $email,
            'phone' => $dienThoai,
            'role' => 'Customer'
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in register: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
