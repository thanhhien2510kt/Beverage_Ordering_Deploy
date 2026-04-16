<?php
$canAddToCart = true;
if (isset($_SESSION['user_role_name'])) {
    $userRoleLower = strtolower($_SESSION['user_role_name']);
    if ($userRoleLower === 'admin' || $userRoleLower === 'staff') {
        $canAddToCart = false;
    }
}
?>
    <!-- Product Customization Side Menu -->
    <div id="product-customize-modal" class="product-customize-modal">
        <div class="modal-overlay"></div>
        <div class="modal-side-panel">
            <!-- Close Button -->
            <button type="button" id="close-modal-btn" class="modal-close-btn" aria-label="Đóng">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            
            <div class="modal-content">
                <div id="modal-loading" class="modal-loading">
                    <p>Đang tải...</p>
                </div>
                
                <div id="modal-product-content" style="display: none;">
                    <!-- Product Image -->
                    <div class="modal-product-image-wrapper">
                        <img id="modal-product-image" src="" alt="" class="modal-product-image">
                    </div>
                    
                    <!-- Product Info -->
                    <div class="modal-product-info">
                        <h2 id="modal-product-name"></h2>
                        <div class="modal-product-price">
                            <span id="modal-current-price" class="modal-current-price"></span>
                            <span id="modal-old-price" class="modal-old-price"></span>
                        </div>
                        
                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" id="modal-decrease-qty">-</button>
                            <input type="hidden" id="modal-quantity" value="1" min="1" max="10">
                            <span id="modal-quantity-display" class="quantity-input" aria-live="polite">1</span>
                            <button type="button" class="quantity-btn" id="modal-increase-qty">+</button>
                        </div>
                        
                        <!-- Options Form -->
                        <form id="modal-product-form">
                            <input type="hidden" id="modal-product-id" name="product_id">
                            <input type="hidden" id="modal-base-price" name="base_price">
                            <input type="hidden" id="modal-reference-price" name="reference_price">
                            
                            <div id="modal-option-groups" style="margin-top: 20px;"></div>
                            
                            <!-- Note Section -->
                            <div class="note-section">
                                <label for="modal-product-note" class="note-label">Thêm ghi chú</label>
                                <textarea id="modal-product-note" name="note" class="note-textarea" placeholder="Nhập nội dung ghi chú cho quán (nếu có)" maxlength="52"></textarea>
                                <div class="char-count"><span id="modal-char-count">0</span>/52 ký tự</div>
                            </div>
                            
                            <!-- Total Price Display -->
                            <div class="total-price-display">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 20px; font-weight: bold; color: var(--primary-green);">Tổng tiền:</span>
                                    <span id="modal-total-price" style="font-size: 28px; font-weight: bold; color: var(--primary-green);"></span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <?php if ($canAddToCart): ?>
                                <div class="product-actions" style="position: sticky; bottom: 0; background-color: var(--white); padding: 16px 20px; border-top: 1px solid var(--border-color); box-sizing: border-box; display: flex; gap: 10px; margin-top: auto;">
                                    <button type="button" id="modal-add-to-cart-btn" class="btn-add-cart">Thêm vào giỏ</button>
                                    <a href="<?php echo isset($basePath) ? $basePath : ''; ?>pages/cart/index.php" class="btn-view-cart" style="text-decoration: none; width: auto; flex: 1; display: flex; align-items: center; justify-content: center;">Xem giỏ hàng</a>
                                </div>
                            <?php else: ?>
                            <div class="product-actions" style="position: fixed; bottom: 0; background-color: var(--white); padding: 20px; border-top: 1px solid var(--border-color); text-align: center;">
                                <p style="color: var(--text-light); font-size: 14px;">Tài khoản Admin/Staff không thể thêm sản phẩm</p>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
