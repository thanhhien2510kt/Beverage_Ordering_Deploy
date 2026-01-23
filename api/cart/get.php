<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartItems = [];

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartItems = $_SESSION['cart'];
}

echo json_encode([
    'success' => true,
    'items' => $cartItems,
    'count' => count($cartItems)
], JSON_UNESCAPED_UNICODE);
exit;
