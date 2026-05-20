<?php

require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

$userRole = $_SESSION['user_role_name'] ?? '';
$isAdmin = ($userRole === 'Admin');

if (!$isAdmin) {
    header('Location: ../../index.php');
    exit;
}

$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
    <link rel="stylesheet" href="../../assets/css/management.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .users-table th, .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .users-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: var(--text-dark);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-active {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        .status-inactive {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        .role-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            background-color: var(--light-green);
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="management-layout" style="display: flex; min-height: calc(100vh - 100px); background-color: var(--light-green); align-items: stretch;">
        <?php include '../../components/admin-sidebar.php'; ?>
        <section class="management-section" style="flex: 1; padding: 30px; box-sizing: border-box; overflow-y: auto;">
            <div class="container" style="max-width: 1200px; margin: 0 auto; width: 100%;">
            <div class="management-header">
                <div class="management-header-left">
                    <h1 class="management-title">Quản lý người dùng</h1>
                    <p class="management-subtitle">Xem danh sách và thông tin người dùng trong hệ thống</p>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="orders-search-bar" style="margin-bottom: 30px;">
                <div class="search-input-wrapper">
                    <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="manageUserSearchInput" 
                        class="search-input" 
                        placeholder="Tìm kiếm theo tên, email, số điện thoại..."
                    >
                </div>
            </div>

            <!-- Users Container -->
            <div class="orders-container" style="background: var(--white); border-radius: 30px; padding: 30px; box-shadow: var(--shadow);">
                <div id="manageUsersLoading" class="orders-loading" style="display: none; text-align: center; padding: 40px;">
                    <p>Đang tải người dùng...</p>
                </div>
                <div id="manageUsersEmpty" class="orders-empty" style="display: none; text-align: center; padding: 40px;">
                    <p>Không có người dùng nào</p>
                </div>
                
                <div class="table-responsive">
                    <table class="users-table" id="usersTable" style="display: none;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Username / Email</th>
                                <th>Số điện thoại</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="manageUsersList">
                            <!-- Dữ liệu được render bằng JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </section>
    </div>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <?php include '../../components/snack-bar.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/manage-users.js"></script>
</body>
</html>
