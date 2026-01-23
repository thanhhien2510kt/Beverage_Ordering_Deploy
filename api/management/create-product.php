<?php

header('Content-Type: application/json');
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => '', 'product_id' => null];

try {

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Bạn cần đăng nhập để thực hiện thao tác này');
    }


    $userRole = $_SESSION['user_role_name'] ?? '';
    if ($userRole !== 'Admin') {
        throw new Exception('Chỉ Admin mới có quyền thêm sản phẩm mới');
    }


    $tenSP = isset($_POST['ten_sp']) ? trim($_POST['ten_sp']) : '';
    $giaNiemYet = isset($_POST['gia_niem_yet']) ? trim($_POST['gia_niem_yet']) : '';
    $giaCoBan = isset($_POST['gia_co_ban']) && $_POST['gia_co_ban'] !== '' ? trim($_POST['gia_co_ban']) : null;
    $maCategory = isset($_POST['ma_category']) ? (int)$_POST['ma_category'] : 0;


    if (empty($tenSP)) {
        throw new Exception('Vui lòng nhập tên sản phẩm');
    }

    if (empty($giaNiemYet) || !is_numeric($giaNiemYet) || $giaNiemYet < 0) {
        throw new Exception('Giá niêm yết không hợp lệ');
    }
    $giaNiemYet = (float)$giaNiemYet;
    if ($giaCoBan !== null) {
        if (!is_numeric($giaCoBan) || $giaCoBan < 0) {
            throw new Exception('Giá tham khảo không hợp lệ');
        }
        $giaCoBan = (float)$giaCoBan;
    } else {
        $giaCoBan = $giaNiemYet;
    }

    if (!$maCategory) {
        throw new Exception('Vui lòng chọn danh mục');
    }


    $pdo = getDBConnection();


    $stmt = $pdo->prepare("SELECT MaCategory FROM Category WHERE MaCategory = ? AND TrangThai = 1");
    $stmt->execute([$maCategory]);
    if (!$stmt->fetch()) {
        throw new Exception('Danh mục không tồn tại');
    }


    $hinhAnh = 'assets/img/products/product_one.png'; // Default image
    

    if (isset($_FILES['hinh_anh']) && 
        isset($_FILES['hinh_anh']['name']) && 
        !empty($_FILES['hinh_anh']['name']) &&
        $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        
        $file = $_FILES['hinh_anh'];
        $fileError = $file['error'];
        
        $uploadDir = __DIR__ . '/../../assets/img/products/';
        

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


        $newFileName = uniqid('product_', true) . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;


        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('Không thể upload file. Vui lòng kiểm tra quyền ghi file.');
        }


        if (!file_exists($uploadPath)) {
            throw new Exception('File không được lưu thành công');
        }


        $hinhAnh = 'assets/img/products/' . $newFileName;
        

        error_log("Image uploaded successfully: " . $hinhAnh);
    } else {

        if (isset($_FILES['hinh_anh'])) {
            error_log("No file uploaded or upload error. Error code: " . ($_FILES['hinh_anh']['error'] ?? 'N/A'));
        } else {
            error_log("No file field in request");
        }
    }


    $sql = "INSERT INTO SanPham (TenSP, GiaNiemYet, GiaCoBan, HinhAnh, MaCategory, TrangThai) 
            VALUES (?, ?, ?, ?, ?, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tenSP, $giaNiemYet, $giaCoBan, $hinhAnh, $maCategory]);

    $productId = $pdo->lastInsertId();

    $response = [
        'success' => true,
        'message' => 'Thêm sản phẩm mới thành công',
        'product_id' => $productId
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Database error in create product: " . $e->getMessage());
    $response['message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
