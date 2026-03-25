<?php
/**
 * Public API để chatbot lấy danh sách sản phẩm (không cần auth).
 * Bảo vệ bằng CHATBOT_SECRET_KEY.
 */
header('Content-Type: application/json');
require_once '../../functions.php';

$response = ['success' => false, 'products' => [], 'message' => ''];

try {
    // Kiểm tra secret key - hỗ trợ cả uppercase/lowercase header
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $secret = $headers['x-chatbot-secret']
        ?? ($_SERVER['HTTP_X_CHATBOT_SECRET'] ?? ($_GET['secret'] ?? ''));
    $expectedSecret = 'MeowTea_Secret_2026_@abcxyz';

    if ($secret !== $expectedSecret) {
        http_response_code(403);
        throw new Exception('Unauthorized');
    }

    $pdo = getDBConnection();

    $sql = "SELECT sp.masp, sp.tensp, sp.giacoban, sp.gianiemyet, sp.hinhanh,
                   sp.rating, sp.soluotrating, sp.daban, c.tencategory
            FROM sanpham sp
            INNER JOIN category c ON sp.macategory = c.macategory
            WHERE sp.trangthai = 1
            ORDER BY sp.daban DESC NULLS LAST, sp.rating DESC NULLS LAST, sp.soluotrating DESC, c.tencategory, sp.tensp";

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
