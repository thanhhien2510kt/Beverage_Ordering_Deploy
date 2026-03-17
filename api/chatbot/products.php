<?php
/**
 * Public API để chatbot lấy danh sách sản phẩm (không cần auth).
 * Bảo vệ bằng CHATBOT_SECRET_KEY.
 */
header('Content-Type: application/json');
require_once '../../functions.php';

$response = ['success' => false, 'products' => [], 'message' => ''];

try {
    // Kiểm tra secret key
    $headers = getallheaders();
    $secret = $headers['X-Chatbot-Secret'] ?? ($_GET['secret'] ?? '');
    $expectedSecret = 'MeowTea_Secret_2026_@abcxyz';

    if ($secret !== $expectedSecret) {
        http_response_code(403);
        throw new Exception('Unauthorized');
    }

    $pdo = getDBConnection();

    $sql = "SELECT sp.MaSP, sp.TenSP, sp.GiaCoBan, sp.GiaNiemYet, sp.HinhAnh,
                   c.TenCategory
            FROM SanPham sp
            INNER JOIN Category c ON sp.MaCategory = c.MaCategory
            WHERE sp.TrangThai = 1
            ORDER BY c.TenCategory, sp.TenSP";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'products' => $products,
        'message' => 'OK'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    error_log("Chatbot products API error: " . $e->getMessage());
    $response['message'] = 'Database error';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
