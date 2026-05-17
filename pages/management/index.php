<?php
require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['user_role_name'] ?? '';
if (hasPermission('manage_orders')) {
    header('Location: order-management.php');
} else {
    header('Location: ../../index.php');
}
exit;
