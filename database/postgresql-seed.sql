/* * POSTGRESQL SEED DATA FOR BEVERAGE SHOP
 */

-- Clear existing data and reset ID sequences
TRUNCATE 
    role, 
    appuser, 
    store, 
    user_store, 
    category, 
    sanpham, 
    option_group, 
    option_value, 
    product_option_group, 
    cart, 
    cart_item, 
    cart_item_option, 
    promotion, 
    payment_method, 
    orders, 
    order_item, 
    order_item_option, 
    news 
RESTART IDENTITY CASCADE;

-- 1. Insert Role
INSERT INTO role (TenRole) VALUES 
('Admin'), 
('Staff'), 
('Customer');

-- 2. Insert Store (12 cửa hàng trải dài khắp Việt Nam)
INSERT INTO store (TenStore, DiaChi, DienThoai, TrangThai) VALUES
('Đồng Khởi', '91 Đồng Khởi, Bến Nghé, Quận 1, Thành Phố Hồ Chí Minh', '033492824', 1),
('Cộng Hòa', '123 Cộng Hòa, Phường 12, Tân Bình, Thành Phố Hồ Chí Minh', '033492825', 1),
('Điện Biên Phủ', '456 Điện Biên Phủ, Phường 25, Bình Thạnh, Thành Phố Hồ Chí Minh', '033492826', 1),
('Nguyễn Huệ', '789 Nguyễn Huệ, Bến Nghé, Quận 1, Thành Phố Hồ Chí Minh', '033492827', 1),
('Lê Văn Việt', '321 Lê Văn Việt, Hiệp Phú, Quận 9, Thành Phố Hồ Chí Minh', '033492828', 1),
('Cầu Giấy', '45 Cầu Giấy, Quận Cầu Giấy, Hà Nội', '033492829', 1),
('Hoàn Kiếm', '67 Phố Hàng Bông, Hoàn Kiếm, Hà Nội', '033492830', 1),
('Ba Đình', '89 Nguyễn Trãi, Nguyễn Trung Trực, Ba Đình, Hà Nội', '033492831', 1),
('Đống Đa', '234 Tây Sơn, Trung Liệt, Đống Đa, Hà Nội', '033492832', 1),
('Ninh Kiều', '123 Trần Hưng Đạo, Tân An, Ninh Kiều, Cần Thơ', '033492833', 1),
('Cái Răng', '456 Nguyễn Văn Cừ, Lê Bình, Cái Răng, Cần Thơ', '033492834', 1),
('Hải Châu', '789 Trần Phú, Hải Châu 1, Hải Châu, Đà Nẵng', '033492835', 1);

-- 3. Insert User
INSERT INTO appuser (Username, Password, Ho, Ten, GioiTinh, DienThoai, Email, TrangThai, MaRole, DiaChi) VALUES
('admin', 'admin', 'Nguyễn', 'Quản Lý', 'M', '0912345678', 'admin@shop.com', 1, 1, NULL),
('staff', 'staff', 'Trần', 'Nhân Viên', 'F', '0987654321', 'staff1@shop.com', 1, 2, NULL),
('cust', 'cust', 'Lê', 'Khách Hàng', 'M', '0911223344', 'customer@gmail.com', 1, 3, 'Số 07 đường Nguyễn Bỉnh Khiêm, phường Bến Nghé, quận 1, TP Hồ Chí Minh');

-- Phân công nhân viên vào cửa hàng
INSERT INTO user_store (MaUser, MaStore) VALUES (2, 1);

-- 4. Insert Category
INSERT INTO category (TenCategory, TrangThai) VALUES 
('Cà phê truyền thống', 1), 
('Trà sữa', 1), 
('Trà trái cây', 1),
('Yogurt', 1);

-- 5. Insert Option Group
INSERT INTO option_group (TenNhom, IsMultiple) VALUES 
('Mức đường', 0), 
('Mức đá', 0),    
('Topping', 1);   

-- 6. Insert Option Value
-- Mức đường (Group 1)
INSERT INTO option_value (TenGiaTri, GiaThem, MaOptionGroup) VALUES
('100% Đường', 0, 1),
('70% Đường', 0, 1),
('50% Đường', 0, 1),
('Không đường', 0, 1);

-- Mức đá (Group 2)
INSERT INTO option_value (TenGiaTri, GiaThem, MaOptionGroup) VALUES
('100% Đá', 0, 2),
('50% Đá', 0, 2),
('Không đá', 0, 2);

-- Topping (Group 3)
INSERT INTO option_value (TenGiaTri, GiaThem, HinhAnh, MaOptionGroup) VALUES
('Trân châu đen', 5000, 'assets/img/products/topping/topping-tranchau.png', 3),
('Thạch dừa', 5000, 'assets/img/products/topping/topping-thachdua.png', 3),
('Pudding trứng', 10000, 'assets/img/products/topping/topping-pudding.png', 3),
('Sương sáo', 5000, 'assets/img/products/topping/topping-suongsao.png', 3),
('Củ năng', 5000, 'assets/img/products/topping/toppingcunang.png', 3);

-- 7. Insert SanPham
INSERT INTO sanpham (TenSP, GiaNiemYet, GiaCoBan, HinhAnh, Rating, SoLuotRating, MaCategory) VALUES
('Cà phê Cappuccino', 38000, 45000, 'assets/img/products/caphe/caphe-cappucchino.png', 4.65, 245, 1),
('Cà phê đen truyền thống', 27000, 30000, 'assets/img/products/caphe/caphe-dentruyenthong.png', 4.50, 328, 1),
('Cà phê muối', 39000, 42000, 'assets/img/products/caphe/caphe-muoi.png', 4.75, 189, 1),
('Cà phê sữa đá', 29000, 34000, 'assets/img/products/caphe/caphe-suada.png', 4.80, 456, 1),
('Trà sữa Dâu Tây', 45000, 50000, 'assets/img/products/trasua/trasua-dautay.png', 4.85, 512, 2),
('Trà sữa Flan', 49000, 52000, 'assets/img/products/trasua/trasua-flan.png', 4.90, 645, 2),
('Trà sữa Matcha', 48000, 50000, 'assets/img/products/trasua/trasua-mathca.png', 4.70, 432, 2),
('Trà sữa Socola', 38000, 48000, 'assets/img/products/trasua/trasua-socola.png', 4.65, 298, 2),
('Trà sữa Thái Xanh', 45000, 49000, 'assets/img/products/trasua/trasua-thaixanh.png', 4.75, 356, 2),
('Trà sữa Việt Quất', 45000, 50000, 'assets/img/products/trasua/trasua-vietquat.png', 4.80, 421, 2),
('Trà Đào', 39000, 45000, 'assets/img/products/tratraicay/tratc-dao.png', 4.60, 234, 3),
('Trà Khóm', 39000, 45000, 'assets/img/products/tratraicay/tratc-khom.png', 4.55, 198, 3),
('Trà Sen Vàng', 39000, 46000, 'assets/img/products/tratraicay/tratc-senvang.png', 4.70, 267, 3),
('Trà Vải', 42000, 45000, 'assets/img/products/tratraicay/tratc-vai.png', 4.65, 189, 3),
('Yogurt Truyền Thống', 38000, 40000, 'assets/img/products/yogurt/truyenthong.png', 4.75, 312, 4),
('Yogurt Dâu Tây', 40000, 45000, 'assets/img/products/yogurt/dautay.png', 4.80, 278, 4);

-- 8. Link SanPham với Option Group (Product_Option_Group)
INSERT INTO product_option_group (MaSP, MaOptionGroup) VALUES 
(1, 1), (1, 2), (2, 1), (2, 2), (3, 1), (3, 2), (4, 1), (4, 2),
(5, 1), (5, 2), (5, 3), (6, 1), (6, 2), (6, 3), (7, 1), (7, 2), (7, 3),
(8, 1), (8, 2), (8, 3), (9, 1), (9, 2), (9, 3), (10, 1), (10, 2), (10, 3),
(11, 1), (11, 2), (12, 1), (12, 2), (13, 1), (13, 2), (14, 1), (14, 2),
(15, 1), (15, 2), (15, 3), (16, 1), (16, 2), (16, 3);

-- 9. Insert Payment Method
INSERT INTO payment_method (TenPayment) VALUES ('Tiền mặt'), ('Chuyển khoản'), ('Momo'), ('VNPay');

-- 10. Insert Promotion
INSERT INTO promotion (Code, LoaiGiamGia, GiaTri, GiaTriToiDa, NgayBatDau, NgayKetThuc, TrangThai) VALUES
('WELCOME10', 'Percentage', 10, 20000, NULL, NULL, 1),
('FIXED5K', 'Fixed', 5000, NULL, NULL, NULL, 1);

-- 11. Insert News
INSERT INTO news (TieuDe, NoiDung, HinhAnh, TrangThai, NgayTao) VALUES
('Những lợi ích tuyệt vời của nước ép trái cây đối với sức khỏe', 'assets/md/news/1.md', 'assets/img/news/news_one.jpg', 1, '2024-12-24 10:00:00'),
('Cà Phê Cappuccino Dừa lần đầu tiên có mặt tại MeowTea Fresh', 'assets/md/news/2.md', 'assets/img/news/news_two.jpg', 1, '2024-12-05 10:00:00'),
('MeowTea Fresh ra mắt dòng sản phẩm Matcha - dấu ấn độc đáo', 'assets/md/news/3.md', 'assets/img/news/news_three.png', 1, '2024-12-09 10:00:00'),
('App Thành Viên MeowTea Fresh chính thức ra mắt trên Android & iOS', 'assets/md/news/4.md', 'assets/img/news/news_banner.jpg', 1, '2024-12-15 10:00:00');
