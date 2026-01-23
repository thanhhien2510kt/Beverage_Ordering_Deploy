<?php
header('Content-Type: application/json');
require_once '../../database/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];
try {
    $usernameOrEmail = isset($_POST['username_or_email']) ? trim($_POST['username_or_email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($usernameOrEmail)) {
        throw new Exception('Vui lòng nhập tên đăng nhập hoặc email');
    }

    if (empty($password)) {
        throw new Exception('Vui lòng nhập mật khẩu');
    }

    $pdo = getDBConnection();

    $sql = "SELECT u.*, r.TenRole 
            FROM User u 
            INNER JOIN Role r ON u.MaRole = r.MaRole 
            WHERE (u.Username = ? OR u.Email = ?) AND u.TrangThai = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Tên đăng nhập/email hoặc mật khẩu không đúng');
    }

    $passwordMatch = false;
    
    if (strpos($user['Password'], '$2y$') === 0) {
        $passwordMatch = password_verify($password, $user['Password']);
    } else {
        $passwordMatch = ($user['Password'] === $password);
    }

    if (!$passwordMatch) {
        throw new Exception('Tên đăng nhập/email hoặc mật khẩu không đúng');
    }

    $fullName = trim($user['Ho'] . ' ' . $user['Ten']);
    $_SESSION['user_id'] = $user['MaUser'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['user_ho'] = $user['Ho'];
    $_SESSION['user_ten'] = $user['Ten'];
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_gioi_tinh'] = $user['GioiTinh'] ?? null;
    $_SESSION['user_email'] = $user['Email'];
    $_SESSION['user_phone'] = $user['DienThoai'];
    $_SESSION['user_dia_chi'] = $user['DiaChi'] ?? '';
    $_SESSION['user_role'] = $user['MaRole'];
    $_SESSION['user_role_name'] = $user['TenRole'];
    $_SESSION['logged_in'] = true;

    $_SESSION['user'] = [
        'MaUser' => $user['MaUser'],
        'Username' => $user['Username'],
        'Ho' => $user['Ho'],
        'Ten' => $user['Ten'],
        'Email' => $user['Email'],
        'DienThoai' => $user['DienThoai'],
        'DiaChi' => $user['DiaChi'] ?? '',
        'MaRole' => $user['MaRole'],
        'TenRole' => $user['TenRole']
    ];

    require_once '../../functions.php';
    $storeId = isset($_SESSION['selected_store']) ? (int)$_SESSION['selected_store'] : 1;
    
    $cartMerged = mergeCartWithDB($user['MaUser'], $storeId);
    if (!$cartMerged) {
        error_log("Warning: Failed to merge cart from database for user " . $user['MaUser']);
    }

    $response = [
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user' => [
            'id' => $user['MaUser'],
            'username' => $user['Username'],
            'ho' => $user['Ho'],
            'ten' => $user['Ten'],
            'name' => $fullName,
            'gioi_tinh' => $user['GioiTinh'] ?? null,
            'email' => $user['Email'],
            'phone' => $user['DienThoai'],
            'dia_chi' => $user['DiaChi'] ?? '',
            'role' => $user['TenRole']
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in login: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;