<?php
/**
 * Management Create Topping API
 * Create new topping (Admin only)
 */

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'topping_id' => null];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Chỉ Admin mới có quyền thêm topping mới');
    }


    $tenTopping = isset($_POST['ten_topping']) ? trim($_POST['ten_topping']) : '';
    $giaThem = isset($_POST['gia_them']) ? trim($_POST['gia_them']) : '';


    if (empty($tenTopping)) {
        throw new Exception('Vui lòng nhập tên topping');
    }

    if (empty($giaThem) || !is_numeric($giaThem) || $giaThem < 0) {
        throw new Exception('Giá thêm không hợp lệ');
    }


    $pdo = getDBConnection();


    $stmt = $pdo->prepare("SELECT MaOptionGroup FROM Option_Group WHERE MaOptionGroup = 3");
    $stmt->execute();
    $optionGroup = $stmt->fetch();
    
    if (!$optionGroup) {
        throw new Exception('Nhóm topping không tồn tại');
    }

    $maOptionGroup = 3;


    $hinhAnh = 'assets/img/products/topping/topping-tranchau.png'; // Default image
    

    if (isset($_FILES['hinh_anh']) && 
        isset($_FILES['hinh_anh']['name']) && 
        !empty($_FILES['hinh_anh']['name']) &&
        $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        
        $file = $_FILES['hinh_anh'];
        $fileError = $file['error'];
        
        $uploadDir = __DIR__ . '/../../assets/img/products/topping/';
        

        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Không thể tạo thư mục upload: ' . $uploadDir);
            }
        }

        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];


        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (empty($fileExtension) || !in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)');
        }


        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($fileSize > $maxFileSize) {
            throw new Exception('Kích thước file không được vượt quá 5MB');
        }


        $imageInfo = @getimagesize($fileTmpName);
        if ($imageInfo === false) {
            throw new Exception('File không phải là hình ảnh hợp lệ');
        }


        $newFileName = 'topping-' . uniqid('', true) . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;


        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('Không thể upload file. Vui lòng kiểm tra quyền ghi file.');
        }


        if (!file_exists($uploadPath)) {
            throw new Exception('File không được lưu thành công');
        }


        $hinhAnh = 'assets/img/products/topping/' . $newFileName;
        

        error_log("Image uploaded successfully: " . $hinhAnh);
    }


    $sql = "INSERT INTO Option_Value (TenGiaTri, GiaThem, HinhAnh, MaOptionGroup) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tenTopping, $giaThem, $hinhAnh, $maOptionGroup]);

    $toppingId = $pdo->lastInsertId();

    $response = [
        'success' => true,
        'message' => 'Thêm topping mới thành công',
        'topping_id' => $toppingId
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in create topping: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
