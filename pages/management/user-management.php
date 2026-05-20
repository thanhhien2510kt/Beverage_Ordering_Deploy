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
        .order-detail-content .search-input {
            padding-left: 15px !important;
            border-radius: 8px; /* Giảm độ bo góc cho form input */
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

            <!-- Search Bar and Add Button -->
            <div class="orders-search-bar" style="margin-bottom: 30px; display: flex; gap: 15px; align-items: center;">
                <div class="search-input-wrapper" style="flex: 1;">
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
                <button id="addUserBtn" class="btn btn-primary" style="white-space: nowrap; display: flex; align-items: center; gap: 8px; padding: 10px 24px; border-radius: 50px; font-weight: 500;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Thêm
                </button>
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
                                <th>Hành động</th>
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

    <!-- User Modal -->
    <div id="userModal" class="order-detail-modal" role="dialog" aria-modal="true" style="display: none;">
        <div class="order-detail-overlay" id="userModalOverlay"></div>
        <div class="order-detail-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <button type="button" class="order-detail-close" id="closeUserModal" aria-label="Đóng">&times;</button>
            <h2 id="userModalTitle" style="margin-bottom: 20px;">Thêm/Sửa Người dùng</h2>
            <form id="userForm">
                <input type="hidden" id="userId" name="id" value="">
                
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label for="userHo" style="display: block; margin-bottom: 5px; font-weight: 500;">Họ</label>
                        <input type="text" id="userHo" name="ho" class="search-input" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="userTen" style="display: block; margin-bottom: 5px; font-weight: 500;">Tên</label>
                        <input type="text" id="userTen" name="ten" class="search-input" required>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="userUsername" style="display: block; margin-bottom: 5px; font-weight: 500;">Username</label>
                    <input type="text" id="userUsername" name="username" class="search-input" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="userEmail" style="display: block; margin-bottom: 5px; font-weight: 500;">Email</label>
                    <input type="email" id="userEmail" name="email" class="search-input" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label for="userPassword" style="display: block; margin-bottom: 5px; font-weight: 500;">Mật khẩu <span id="passwordHelp" style="font-size: 0.8rem; font-weight: normal; color: #666;">(Bỏ trống nếu không đổi)</span></label>
                    <input type="password" id="userPassword" name="password" class="search-input">
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label for="userPhone" style="display: block; margin-bottom: 5px; font-weight: 500;">Số điện thoại</label>
                        <input type="tel" id="userPhone" name="dienthoai" class="search-input">
                    </div>
                    <div style="flex: 1;">
                        <label for="userRole" style="display: block; margin-bottom: 5px; font-weight: 500;">Vai trò</label>
                        <select id="userRole" name="role" class="search-input" style="width: 100%; height: 42px; padding: 0 15px;" required>
                            <!-- Role options will be populated dynamically if possible, or static for now -->
                            <option value="1">Admin</option>
                            <option value="2">Quản lý cửa hàng</option>
                            <option value="3">Khách hàng</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label for="userAddress" style="display: block; margin-bottom: 5px; font-weight: 500;">Địa chỉ</label>
                    <input type="text" id="userAddress" name="diachi" class="search-input">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn" id="cancelUserBtn" style="background: #f0f0f0; color: #333;">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">Lưu thay đổi</button>
                </div>
            </form>
        </div>
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
