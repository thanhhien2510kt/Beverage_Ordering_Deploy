<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_destroy();

// Xóa lịch sử chat của user
setcookie('meowbot_session', '', time() - 3600, '/');

header('Location: ../../index.php');
exit;