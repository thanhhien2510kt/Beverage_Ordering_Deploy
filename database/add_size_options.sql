-- Bước 1: Thêm nhóm tùy chọn 'Size' vào bảng option_group 
-- (ismultiple = 0 vì mỗi sản phẩm chỉ được chọn 1 size duy nhất)
INSERT INTO option_group (tennhom, ismultiple) 
VALUES ('Size', 0);

-- Bước 2: Thêm các giá trị Size S (0đ), Size M (+5.000đ) và Size L (+10.000đ) vào bảng option_value
-- Tự động lấy maoptiongroup của nhóm 'Size' vừa tạo để map vào
INSERT INTO option_value (tengiatri, giathem, maoptiongroup)
VALUES 
    ('Size S', 0, (SELECT maoptiongroup FROM option_group WHERE tennhom = 'Size' LIMIT 1)),
    ('Size M', 5000, (SELECT maoptiongroup FROM option_group WHERE tennhom = 'Size' LIMIT 1)),
    ('Size L', 10000, (SELECT maoptiongroup FROM option_group WHERE tennhom = 'Size' LIMIT 1));

-- Bước 3: Áp dụng nhóm 'Size' này cho TẤT CẢ các sản phẩm hiện có
-- Lấy toàn bộ masp từ bảng sanpham và map với maoptiongroup của nhóm 'Size'
INSERT INTO product_option_group (masp, maoptiongroup)
SELECT masp, (SELECT maoptiongroup FROM option_group WHERE tennhom = 'Size' LIMIT 1) 
FROM sanpham;
