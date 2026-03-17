-- 1. Create Schema/Database (PostgreSQL handles this differently, usually run from meta-commands)
-- CREATE DATABASE meowtea_schema;
-- \c meowtea_schema;

-- 1. Bảng Role
DROP TABLE IF EXISTS role CASCADE;
CREATE TABLE role (
    MaRole SERIAL PRIMARY KEY,
    TenRole VARCHAR(50) NOT NULL -- user / member / admin
);

-- 2. Bảng User
DROP TABLE IF EXISTS appuser CASCADE;
CREATE TABLE appuser (
    MaUser SERIAL PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Ho VARCHAR(50) NOT NULL,
    Ten VARCHAR(50) NOT NULL,
    GioiTinh CHAR(1) CHECK (GioiTinh IN ('M', 'F', 'O')) DEFAULT NULL,
    DienThoai VARCHAR(20),
    Email VARCHAR(100),
    TrangThai INT DEFAULT 1,
    MaRole INT NOT NULL,
    DiaChi TEXT DEFAULT NULL,
    CONSTRAINT FK_User_Role FOREIGN KEY (MaRole) REFERENCES role (MaRole)
);

-- 3. Bảng Store
DROP TABLE IF EXISTS store CASCADE;
CREATE TABLE store (
    MaStore SERIAL PRIMARY KEY,
    TenStore VARCHAR(200) NOT NULL,
    DiaChi TEXT NOT NULL,
    DienThoai VARCHAR(20),
    TrangThai INT DEFAULT 1
);

-- 4. Bảng User_Store
DROP TABLE IF EXISTS user_store CASCADE;
CREATE TABLE user_store (
    MaUser INT NOT NULL,
    MaStore INT NOT NULL,
    PRIMARY KEY (MaUser, MaStore),
    CONSTRAINT FK_US_User FOREIGN KEY (MaUser) REFERENCES appuser (MaUser),
    CONSTRAINT FK_US_Store FOREIGN KEY (MaStore) REFERENCES store (MaStore)
);

-- 5. Bảng Category
DROP TABLE IF EXISTS category CASCADE;
CREATE TABLE category (
    MaCategory SERIAL PRIMARY KEY,
    TenCategory VARCHAR(100) NOT NULL,
    TrangThai INT DEFAULT 1
);

-- 6. Bảng SanPham
DROP TABLE IF EXISTS sanpham CASCADE;
CREATE TABLE sanpham (
    MaSP SERIAL PRIMARY KEY,
    TenSP VARCHAR(200) NOT NULL,
    GiaNiemYet DECIMAL(15, 0) NOT NULL DEFAULT 0,
    GiaCoBan DECIMAL(15, 0) NOT NULL DEFAULT 0,
    HinhAnh VARCHAR(255),
    TrangThai INT DEFAULT 1,
    Rating DECIMAL(3, 2) DEFAULT NULL,
    SoLuotRating INT DEFAULT 0,
    MaCategory INT NOT NULL,
    CONSTRAINT FK_SP_Category FOREIGN KEY (MaCategory) REFERENCES category (MaCategory),
    CONSTRAINT CHK_Rating_Range CHECK (Rating IS NULL OR (Rating >= 1.00 AND Rating <= 5.00))
);

-- 7. Bảng Option_Group
DROP TABLE IF EXISTS option_group CASCADE;
CREATE TABLE option_group (
    MaOptionGroup SERIAL PRIMARY KEY,
    TenNhom VARCHAR(100) NOT NULL,
    IsMultiple INT DEFAULT 0
);

-- 8. Bảng Option_Value
DROP TABLE IF EXISTS option_value CASCADE;
CREATE TABLE option_value (
    MaOptionValue SERIAL PRIMARY KEY,
    TenGiaTri VARCHAR(100) NOT NULL,
    GiaThem DECIMAL(15, 0) DEFAULT 0,
    HinhAnh VARCHAR(255) DEFAULT NULL,
    MaOptionGroup INT NOT NULL,
    CONSTRAINT FK_OV_Group FOREIGN KEY (MaOptionGroup) REFERENCES option_group (MaOptionGroup)
);

-- 9. Bảng Product_Option_Group
DROP TABLE IF EXISTS product_option_group CASCADE;
CREATE TABLE product_option_group (
    MaSP INT NOT NULL,
    MaOptionGroup INT NOT NULL,
    PRIMARY KEY (MaSP, MaOptionGroup),
    CONSTRAINT FK_POG_SP FOREIGN KEY (MaSP) REFERENCES sanpham (MaSP),
    CONSTRAINT FK_POG_Group FOREIGN KEY (MaOptionGroup) REFERENCES option_group (MaOptionGroup)
);

-- 10. Bảng Cart
DROP TABLE IF EXISTS cart CASCADE;
CREATE TABLE cart (
    MaCart SERIAL PRIMARY KEY,
    MaUser INT NOT NULL,
    MaStore INT NOT NULL,
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Cart_User FOREIGN KEY (MaUser) REFERENCES appuser (MaUser),
    CONSTRAINT FK_Cart_Store FOREIGN KEY (MaStore) REFERENCES store (MaStore)
);

-- 11. Bảng Cart_Item
DROP TABLE IF EXISTS cart_item CASCADE;
CREATE TABLE cart_item (
    MaCartItem SERIAL PRIMARY KEY,
    MaCart INT NOT NULL,
    MaSP INT NOT NULL,
    SoLuong INT DEFAULT 1,
    GiaNiemYet DECIMAL(15, 0) NOT NULL,
    GhiChu TEXT DEFAULT NULL,
    CONSTRAINT FK_CI_Cart FOREIGN KEY (MaCart) REFERENCES cart (MaCart) ON DELETE CASCADE,
    CONSTRAINT FK_CI_SP FOREIGN KEY (MaSP) REFERENCES sanpham (MaSP)
);

-- 12. Bảng Cart_Item_Option
DROP TABLE IF EXISTS cart_item_option CASCADE;
CREATE TABLE cart_item_option (
    MaCartItem INT NOT NULL,
    MaOptionValue INT NOT NULL,
    GiaThem DECIMAL(15, 0) DEFAULT 0,
    PRIMARY KEY (MaCartItem, MaOptionValue),
    CONSTRAINT FK_CIO_Item FOREIGN KEY (MaCartItem) REFERENCES cart_item (MaCartItem) ON DELETE CASCADE,
    CONSTRAINT FK_CIO_Value FOREIGN KEY (MaOptionValue) REFERENCES option_value (MaOptionValue)
);

-- 13. Bảng Promotion
DROP TABLE IF EXISTS promotion CASCADE;
CREATE TABLE promotion (
    MaPromotion SERIAL PRIMARY KEY,
    Code VARCHAR(50) NOT NULL UNIQUE,
    LoaiGiamGia VARCHAR(50), 
    GiaTri DECIMAL(15, 0) NOT NULL,
    GiaTriToiDa DECIMAL(15, 0) DEFAULT NULL,
    NgayBatDau TIMESTAMP,
    NgayKetThuc TIMESTAMP,
    TrangThai INT DEFAULT 1
);

-- 14. Bảng Payment_Method
DROP TABLE IF EXISTS payment_method CASCADE;
CREATE TABLE payment_method (
    MaPayment SERIAL PRIMARY KEY,
    TenPayment VARCHAR(100) NOT NULL
);

-- 15. Bảng Orders
DROP TABLE IF EXISTS orders CASCADE;
CREATE TABLE orders (
    MaOrder SERIAL PRIMARY KEY,
    MaUser INT NOT NULL,
    MaStore INT NOT NULL,
    MaPayment INT DEFAULT NULL,
    DiaChiGiao TEXT NOT NULL,
    NguoiNhan VARCHAR(200) DEFAULT NULL,
    DienThoaiGiao VARCHAR(20) DEFAULT NULL,
    PhiVanChuyen DECIMAL(15, 0) DEFAULT 0,
    MaPromotion INT DEFAULT NULL,
    GiamGia DECIMAL(15, 0) DEFAULT 0,
    TongTien DECIMAL(15, 0) NOT NULL,
    TrangThai VARCHAR(50) DEFAULT 'Pending',
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ThoiDiemNhanDon TIMESTAMP NULL DEFAULT NULL,
    ThoiDiemGiaoHang TIMESTAMP NULL DEFAULT NULL,
    ThoiDiemNhanHang TIMESTAMP NULL DEFAULT NULL,
    ThoiDiemHuyDon TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT FK_Order_User FOREIGN KEY (MaUser) REFERENCES appuser (MaUser),
    CONSTRAINT FK_Order_Store FOREIGN KEY (MaStore) REFERENCES store (MaStore),
    CONSTRAINT FK_Order_Promotion FOREIGN KEY (MaPromotion) REFERENCES promotion (MaPromotion),
    CONSTRAINT FK_Order_Payment FOREIGN KEY (MaPayment) REFERENCES payment_method (MaPayment)
);

-- 16. Bảng Order_Item
DROP TABLE IF EXISTS order_item CASCADE;
CREATE TABLE order_item (
    MaOrderItem SERIAL PRIMARY KEY,
    MaOrder INT NOT NULL,
    MaSP INT NOT NULL,
    SoLuong INT DEFAULT 1,
    GiaNiemYet DECIMAL(15, 0) NOT NULL,
    CONSTRAINT FK_OI_Order FOREIGN KEY (MaOrder) REFERENCES orders (MaOrder) ON DELETE CASCADE,
    CONSTRAINT FK_OI_SP FOREIGN KEY (MaSP) REFERENCES sanpham (MaSP)
);

-- 17. Bảng Order_Item_Option
DROP TABLE IF EXISTS order_item_option CASCADE;
CREATE TABLE order_item_option (
    MaOrderItem INT NOT NULL,
    MaOptionValue INT NOT NULL,
    GiaThem DECIMAL(15, 0) DEFAULT 0,
    PRIMARY KEY (MaOrderItem, MaOptionValue),
    CONSTRAINT FK_OIO_Item FOREIGN KEY (MaOrderItem) REFERENCES order_item (MaOrderItem) ON DELETE CASCADE,
    CONSTRAINT FK_OIO_Value FOREIGN KEY (MaOptionValue) REFERENCES option_value (MaOptionValue)
);

-- 18. Bảng News
DROP TABLE IF EXISTS news CASCADE;
CREATE TABLE news (
    MaNews SERIAL PRIMARY KEY,
    TieuDe VARCHAR(255) NOT NULL,
    NoiDung VARCHAR(255) NOT NULL,
    HinhAnh VARCHAR(255),
    TrangThai INT DEFAULT 1,
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
