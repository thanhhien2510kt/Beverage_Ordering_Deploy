<?php
/**
 * Management Page
 * Quản lý sản phẩm - chỉ dành cho Staff và Admin
 */

require_once '../../functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}


$userRole = $_SESSION['user_role_name'] ?? '';
if ($userRole !== 'Staff' && $userRole !== 'Admin') {
    header('Location: ../../index.php');
    exit;
}

$isAdmin = ($userRole === 'Admin');
$basePath = '../../';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm & Topping - MeowTea Fresh</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/management.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <section class="management-section">
        <div class="container">
            <!-- Tab Navigation -->
            <div class="management-tabs">
                <button type="button" class="tab-btn active" data-tab="products">Sản phẩm</button>
                <button type="button" class="tab-btn" data-tab="toppings">Topping</button>
            </div>

            <!-- Products Section -->
            <div id="products-section" class="management-section-content active">
                <div class="management-header">
                    <div class="management-header-left">
                        <h1 class="management-title">Quản lý sản phẩm</h1>
                        <p class="management-subtitle">Quản lý danh mục sản phẩm và thông tin chi tiết</p>
                    </div>
                    <?php if ($isAdmin): ?>
                        <?php 
                            $text = 'Thêm sản phẩm mới';
                            $type = 'primary';
                            $id = 'btn-add-product';
                            $icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>';
                            $iconPosition = 'left';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    <?php endif; ?>
                </div>

                <div class="management-content">
                    <!-- Products Accordion -->
                    <div id="products-accordion" class="products-accordion">
                        <div class="loading-spinner">Đang tải...</div>
                    </div>
                </div>
            </div>

            <!-- Toppings Section -->
            <div id="toppings-section" class="management-section-content">
                <div class="management-header">
                    <div class="management-header-left">
                        <h1 class="management-title">Quản lý Topping</h1>
                        <p class="management-subtitle">Quản lý các loại topping bổ sung cho sản phẩm</p>
                    </div>
                    <?php if ($isAdmin): ?>
                        <?php 
                            $text = 'Thêm topping mới';
                            $type = 'primary';
                            $id = 'btn-add-topping';
                            $icon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>';
                            $iconPosition = 'left';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    <?php endif; ?>
                </div>

                <div class="management-content">
                    <!-- Toppings Table -->
                    <div id="toppings-table-wrapper" class="toppings-table-wrapper">
                        <div class="loading-spinner">Đang tải...</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Product Modal (Admin only) -->
    <?php if ($isAdmin): ?>
    <div id="add-product-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm sản phẩm mới</h2>
                <button type="button" class="modal-close" id="close-add-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-product-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product-name">Tên sản phẩm <span class="required">*</span></label>
                        <input type="text" id="product-name" name="ten_sp" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="product-category">Danh mục <span class="required">*</span></label>
                        <select id="product-category" name="ma_category" class="form-input dropdown-select" required>
                            <option value="">-- Chọn danh mục --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product-price">Giá niêm yết (₫) <span class="required">*</span></label>
                        <input type="number" id="product-price" name="gia_niem_yet" class="form-input" min="0" step="1" required>
                        <small class="form-help">Giá bán thật, dùng để tính toán</small>
                    </div>
                    <div class="form-group">
                        <label for="product-reference-price">Giá tham khảo (₫)</label>
                        <input type="number" id="product-reference-price" name="gia_co_ban" class="form-input" min="0" step="1" placeholder="Để trống = giá niêm yết">
                        <small class="form-help">Giá gạch ngang ở menu, chỉ tham khảo</small>
                    </div>
                    <div class="form-group">
                        <label for="product-image">Hình ảnh</label>
                        <input type="file" id="product-image" name="hinh_anh" class="form-input" accept="image/*">
                        <small class="form-help">Để trống để sử dụng hình ảnh mặc định. Chỉ chấp nhận file ảnh (JPG, PNG, GIF, etc.)</small>
                        <div id="image-preview" style="margin-top: 10px; display: none;">
                            <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-color);">
                        </div>
                    </div>
                    <div class="form-actions">
                        <?php 
                            $text = 'Hủy';
                            $type = 'secondary';
                            $id = 'cancel-add-product';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                        <?php 
                            $text = 'Thêm sản phẩm';
                            $type = 'primary';
                            $buttonType = 'submit';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Price Modal (Admin only) -->
    <?php if ($isAdmin): ?>
    <div id="edit-price-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Điều chỉnh giá bán</h2>
                <button type="button" class="modal-close" id="close-edit-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-price-form">
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="form-group">
                        <label for="edit-product-name">Tên sản phẩm</label>
                        <input type="text" id="edit-product-name" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-product-price">Giá bán mới (₫) <span class="required">*</span></label>
                        <input type="number" id="edit-product-price" name="price" class="form-input" min="0" step="1" required>
                    </div>
                    <div class="form-actions">
                        <?php 
                            $text = 'Hủy';
                            $type = 'secondary';
                            $id = 'cancel-edit-price';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                        <?php 
                            $text = 'Cập nhật giá';
                            $type = 'primary';
                            $buttonType = 'submit';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Topping Modal (Admin only) -->
    <?php if ($isAdmin): ?>
    <div id="add-topping-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm topping mới</h2>
                <button type="button" class="modal-close" id="close-add-topping-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-topping-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="topping-name">Tên topping <span class="required">*</span></label>
                        <input type="text" id="topping-name" name="ten_topping" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="topping-price">Giá thêm (₫) <span class="required">*</span></label>
                        <input type="number" id="topping-price" name="gia_them" class="form-input" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="topping-image">Hình ảnh</label>
                        <input type="file" id="topping-image" name="hinh_anh" class="form-input" accept="image/*">
                        <small class="form-help">Để trống để sử dụng hình ảnh mặc định. Chỉ chấp nhận file ảnh (JPG, PNG, GIF, etc.)</small>
                        <div id="topping-image-preview" style="margin-top: 10px; display: none;">
                            <img id="topping-preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-color);">
                        </div>
                    </div>
                    <div class="form-actions">
                        <?php 
                            $text = 'Hủy';
                            $type = 'secondary';
                            $id = 'cancel-add-topping';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                        <?php 
                            $text = 'Thêm topping';
                            $type = 'primary';
                            $buttonType = 'submit';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Topping Price Modal (Admin only) -->
    <?php if ($isAdmin): ?>
    <div id="edit-topping-price-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Điều chỉnh giá topping</h2>
                <button type="button" class="modal-close" id="close-edit-topping-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-topping-price-form">
                    <input type="hidden" id="edit-topping-id" name="topping_id">
                    <div class="form-group">
                        <label for="edit-topping-name">Tên topping</label>
                        <input type="text" id="edit-topping-name" class="form-input" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-topping-price">Giá thêm mới (₫) <span class="required">*</span></label>
                        <input type="number" id="edit-topping-price" name="price" class="form-input" min="0" step="1" required>
                    </div>
                    <div class="form-actions">
                        <?php 
                            $text = 'Hủy';
                            $type = 'secondary';
                            $id = 'cancel-edit-topping-price';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                        <?php 
                            $text = 'Cập nhật giá';
                            $type = 'primary';
                            $buttonType = 'submit';
                            $width = 'auto';
                            include '../../components/button.php';
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php 
        $bgType = 'light-green';
        include '../../components/back-to-top.php';
    ?>

    <?php include '../../components/footer.php'; ?>

    <script src="../../assets/js/common.js"></script>
    <script src="../../assets/js/snack-bar.js"></script>
    <script src="../../assets/js/management.js"></script>
</body>
</html>
