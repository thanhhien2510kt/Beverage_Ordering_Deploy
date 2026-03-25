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
            FROM AppUser u 
            INNER JOIN Role r ON u.MaRole = r.MaRole 
            WHERE (u.Username = ? OR u.Email = ?) AND u.TrangThai = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Tên đăng nhập/email hoặc mật khẩu không đúng');
    }

    // Convert keys to lowercase to avoid case-sensitivity issues
    $user = array_change_key_case($user, CASE_LOWER);

    $passwordMatch = false;
    
    if (strpos($user['password'], '$2y$') === 0) {
        $passwordMatch = password_verify($password, $user['password']);
    } else {
        $passwordMatch = ($user['password'] === $password);
    }

    if (!$passwordMatch) {
        throw new Exception('Tên đăng nhập/email hoặc mật khẩu không đúng');
    }

    $fullName = trim($user['ho'] . ' ' . $user['ten']);
    $_SESSION['user_id'] = $user['mauser'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_ho'] = $user['ho'];
    $_SESSION['user_ten'] = $user['ten'];
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_gioi_tinh'] = $user['gioitinh'] ?? null;
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['dienthoai'];
    $_SESSION['user_dia_chi'] = $user['diachi'] ?? '';
    $_SESSION['user_role'] = $user['marole'];
    $_SESSION['user_role_name'] = $user['tenrole'];
    $_SESSION['logged_in'] = true;

    $_SESSION['user'] = [
        'MaUser' => $user['mauser'],
        'Username' => $user['username'],
        'Ho' => $user['ho'],
        'Ten' => $user['ten'],
        'Email' => $user['email'],
        'DienThoai' => $user['dienthoai'],
        'DiaChi' => $user['diachi'] ?? '',
        'MaRole' => $user['marole'],
        'TenRole' => $user['tenrole']
    ];

    require_once '../../functions.php';
    $storeId = isset($_SESSION['selected_store']) ? (int)$_SESSION['selected_store'] : 1;
    
    $cartMerged = mergeCartWithDB($user['mauser'], $storeId);
    if (!$cartMerged) {
        error_log("Warning: Failed to merge cart from database for user " . $user['mauser']);
    }

    $response = [
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user' => [
            'id' => $user['mauser'],
            'username' => $user['username'],
            'ho' => $user['ho'],
            'ten' => $user['ten'],
            'name' => $fullName,
            'gioi_tinh' => $user['gioitinh'] ?? null,
            'email' => $user['email'],
            'phone' => $user['dienthoai'],
            'dia_chi' => $user['diachi'] ?? '',
            'role' => $user['tenrole']
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