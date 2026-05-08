<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['user_role_name'] ?? '';
if ($userRole === 'Staff' || $userRole === 'Admin') {
    header('Location: order-management.php');
} else {
    header('Location: ../../index.php');
}
exit;
