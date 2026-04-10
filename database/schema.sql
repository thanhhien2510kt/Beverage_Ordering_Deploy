DROP DATABASE IF EXISTS `meowtea_schema`;
CREATE DATABASE `meowtea_schema` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `meowtea_schema`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Bảng ROLE
DROP TABLE IF EXISTS `Role`;
CREATE TABLE `Role` (
    `MaRole` INT AUTO_INCREMENT PRIMARY KEY,
    `TenRole` VARCHAR(50) NOT NULL -- user / member / admin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Bảng USER
DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
    `MaUser` INT AUTO_INCREMENT PRIMARY KEY,
    `Username` VARCHAR(100) NOT NULL UNIQUE,
    `Password` VARCHAR(255) NOT NULL,
    `Ho` VARCHAR(50) NOT NULL,
    `Ten` VARCHAR(50) NOT NULL,
    `GioiTinh` ENUM('M', 'F', 'O') DEFAULT NULL,
    `DienThoai` VARCHAR(20),
    `Email` VARCHAR(100),
    `TrangThai` TINYINT(1) DEFAULT 1,
    `MaRole` INT NOT NULL,
    `DiaChi` TEXT DEFAULT NULL, -- FIXED: Added comma here
    CONSTRAINT `FK_User_Role` FOREIGN KEY (`MaRole`) REFERENCES `Role` (`MaRole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Bảng STORE
DROP TABLE IF EXISTS `Store`;
CREATE TABLE `Store` (
    `MaStore` INT AUTO_INCREMENT PRIMARY KEY,
    `TenStore` VARCHAR(200) NOT NULL,
    `DiaChi` TEXT NOT NULL,
    `DienThoai` VARCHAR(20),
    `TrangThai` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Bảng USER_STORE (N-N)
DROP TABLE IF EXISTS `User_Store`;
CREATE TABLE `User_Store` (
    `MaUser` INT NOT NULL,
    `MaStore` INT NOT NULL,
    PRIMARY KEY (`MaUser`, `MaStore`),
    CONSTRAINT `FK_US_User` FOREIGN KEY (`MaUser`) REFERENCES `User` (`MaUser`),
    CONSTRAINT `FK_US_Store` FOREIGN KEY (`MaStore`) REFERENCES `Store` (`MaStore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Bảng CATEGORY
DROP TABLE IF EXISTS `Category`;
CREATE TABLE `Category` (
    `MaCategory` INT AUTO_INCREMENT PRIMARY KEY,
    `TenCategory` VARCHAR(100) NOT NULL,
    `TrangThai` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Bảng SANPHAM
DROP TABLE IF EXISTS `SanPham`;
CREATE TABLE `SanPham` (
    `MaSP` INT AUTO_INCREMENT PRIMARY KEY,
    `TenSP` VARCHAR(200) NOT NULL,
    `GiaNiemYet` DECIMAL(15, 0) NOT NULL DEFAULT 0,
    `GiaCoBan` DECIMAL(15, 0) NOT NULL DEFAULT 0,
    `HinhAnh` VARCHAR(255),
    `TrangThai` TINYINT(1) DEFAULT 1,
    `Rating` DECIMAL(3, 2) DEFAULT NULL,
    `SoLuotRating` INT DEFAULT 0,
    `MaCategory` INT NOT NULL,
    CONSTRAINT `FK_SP_Category` FOREIGN KEY (`MaCategory`) REFERENCES `Category` (`MaCategory`),
    CONSTRAINT `CHK_Rating_Range` CHECK (`Rating` IS NULL OR (`Rating` >= 1.00 AND `Rating` <= 5.00))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Bảng OPTION_GROUP
DROP TABLE IF EXISTS `Option_Group`;
CREATE TABLE `Option_Group` (
    `MaOptionGroup` INT AUTO_INCREMENT PRIMARY KEY,
    `TenNhom` VARCHAR(100) NOT NULL,
    `IsMultiple` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Bảng OPTION_VALUE
DROP TABLE IF EXISTS `Option_Value`;
CREATE TABLE `Option_Value` (
    `MaOptionValue` INT AUTO_INCREMENT PRIMARY KEY,
    `TenGiaTri` VARCHAR(100) NOT NULL,
    `GiaThem` DECIMAL(15, 0) DEFAULT 0,
    `HinhAnh` VARCHAR(255) DEFAULT NULL,
    `MaOptionGroup` INT NOT NULL,
    CONSTRAINT `FK_OV_Group` FOREIGN KEY (`MaOptionGroup`) REFERENCES `Option_Group` (`MaOptionGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Bảng PRODUCT_OPTION_GROUP (N-N)
DROP TABLE IF EXISTS `Product_Option_Group`;
CREATE TABLE `Product_Option_Group` (
    `MaSP` INT NOT NULL,
    `MaOptionGroup` INT NOT NULL,
    PRIMARY KEY (`MaSP`, `MaOptionGroup`),
    CONSTRAINT `FK_POG_SP` FOREIGN KEY (`MaSP`) REFERENCES `SanPham` (`MaSP`),
    CONSTRAINT `FK_POG_Group` FOREIGN KEY (`MaOptionGroup`) REFERENCES `Option_Group` (`MaOptionGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Bảng CART
DROP TABLE IF EXISTS `Cart`;
CREATE TABLE `Cart` (
    `MaCart` INT AUTO_INCREMENT PRIMARY KEY,
    `MaUser` INT NOT NULL,
    `MaStore` INT NOT NULL,
    `NgayTao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `FK_Cart_User` FOREIGN KEY (`MaUser`) REFERENCES `User` (`MaUser`),
    CONSTRAINT `FK_Cart_Store` FOREIGN KEY (`MaStore`) REFERENCES `Store` (`MaStore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Bảng CART_ITEM
DROP TABLE IF EXISTS `Cart_Item`;
CREATE TABLE `Cart_Item` (
    `MaCartItem` INT AUTO_INCREMENT PRIMARY KEY,
    `MaCart` INT NOT NULL,
    `MaSP` INT NOT NULL,
    `SoLuong` INT DEFAULT 1,
    `GiaNiemYet` DECIMAL(15, 0) NOT NULL,
    `GhiChu` TEXT DEFAULT NULL,
    CONSTRAINT `FK_CI_Cart` FOREIGN KEY (`MaCart`) REFERENCES `Cart` (`MaCart`) ON DELETE CASCADE,
    CONSTRAINT `FK_CI_SP` FOREIGN KEY (`MaSP`) REFERENCES `SanPham` (`MaSP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Bảng CART_ITEM_OPTION
DROP TABLE IF EXISTS `Cart_Item_Option`;
CREATE TABLE `Cart_Item_Option` (
    `MaCartItem` INT NOT NULL,
    `MaOptionValue` INT NOT NULL,
    `GiaThem` DECIMAL(15, 0) DEFAULT 0,
    PRIMARY KEY (`MaCartItem`, `MaOptionValue`),
    CONSTRAINT `FK_CIO_Item` FOREIGN KEY (`MaCartItem`) REFERENCES `Cart_Item` (`MaCartItem`) ON DELETE CASCADE,
    CONSTRAINT `FK_CIO_Value` FOREIGN KEY (`MaOptionValue`) REFERENCES `Option_Value` (`MaOptionValue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Bảng PROMOTION
DROP TABLE IF EXISTS `Promotion`;
CREATE TABLE `Promotion` (
    `MaPromotion` INT AUTO_INCREMENT PRIMARY KEY,
    `Code` VARCHAR(50) NOT NULL UNIQUE,
    `LoaiGiamGia` VARCHAR(50), -- Percentage / Fixed
    `GiaTri` DECIMAL(15, 0) NOT NULL,
    `GiaTriToiDa` DECIMAL(15, 0) DEFAULT NULL,
    `NgayBatDau` DATETIME,
    `NgayKetThuc` DATETIME,
    `TrangThai` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Bảng PAYMENT_METHOD
DROP TABLE IF EXISTS `Payment_Method`;
CREATE TABLE `Payment_Method` (
    `MaPayment` INT AUTO_INCREMENT PRIMARY KEY,
    `TenPayment` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Bảng ORDERS
DROP TABLE IF EXISTS `Orders`;
CREATE TABLE `Orders` (
    `MaOrder` INT AUTO_INCREMENT PRIMARY KEY,
    `MaUser` INT NOT NULL,
    `MaStore` INT NOT NULL,
    `MaPayment` INT DEFAULT NULL,
    `DiaChiGiao` TEXT NOT NULL,
    `NguoiNhan` VARCHAR(200) DEFAULT NULL,
    `DienThoaiGiao` VARCHAR(20) DEFAULT NULL,
    `PhiVanChuyen` DECIMAL(15, 0) DEFAULT 0,
    `MaPromotion` INT DEFAULT NULL,
    `GiamGia` DECIMAL(15, 0) DEFAULT 0,
    `TongTien` DECIMAL(15, 0) NOT NULL,
    `TrangThai` VARCHAR(50) DEFAULT 'Pending',
    `NgayTao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ThoiDiemNhanDon` DATETIME NULL DEFAULT NULL,
    `ThoiDiemGiaoHang` DATETIME NULL DEFAULT NULL,
    `ThoiDiemNhanHang` DATETIME NULL DEFAULT NULL,
    `ThoiDiemHuyDon` DATETIME NULL DEFAULT NULL,
    CONSTRAINT `FK_Order_User` FOREIGN KEY (`MaUser`) REFERENCES `User` (`MaUser`),
    CONSTRAINT `FK_Order_Store` FOREIGN KEY (`MaStore`) REFERENCES `Store` (`MaStore`),
    CONSTRAINT `FK_Order_Promotion` FOREIGN KEY (`MaPromotion`) REFERENCES `Promotion` (`MaPromotion`),
    CONSTRAINT `FK_Order_Payment` FOREIGN KEY (`MaPayment`) REFERENCES `Payment_Method` (`MaPayment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Bảng ORDER_ITEM
DROP TABLE IF EXISTS `Order_Item`;
CREATE TABLE `Order_Item` (
    `MaOrderItem` INT AUTO_INCREMENT PRIMARY KEY,
    `MaOrder` INT NOT NULL,
    `MaSP` INT NOT NULL,
    `SoLuong` INT DEFAULT 1,
    `GiaNiemYet` DECIMAL(15, 0) NOT NULL,
    CONSTRAINT `FK_OI_Order` FOREIGN KEY (`MaOrder`) REFERENCES `Orders` (`MaOrder`) ON DELETE CASCADE,
    CONSTRAINT `FK_OI_SP` FOREIGN KEY (`MaSP`) REFERENCES `SanPham` (`MaSP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Bảng ORDER_ITEM_OPTION
DROP TABLE IF EXISTS `Order_Item_Option`;
CREATE TABLE `Order_Item_Option` (
    `MaOrderItem` INT NOT NULL,
    `MaOptionValue` INT NOT NULL,
    `GiaThem` DECIMAL(15, 0) DEFAULT 0,
    PRIMARY KEY (`MaOrderItem`, `MaOptionValue`),
    CONSTRAINT `FK_OIO_Item` FOREIGN KEY (`MaOrderItem`) REFERENCES `Order_Item` (`MaOrderItem`) ON DELETE CASCADE,
    CONSTRAINT `FK_OIO_Value` FOREIGN KEY (`MaOptionValue`) REFERENCES `Option_Value` (`MaOptionValue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Bảng NEWS
DROP TABLE IF EXISTS `News`;
CREATE TABLE `News` (
    `MaNews` INT AUTO_INCREMENT PRIMARY KEY,
    `TieuDe` VARCHAR(255) NOT NULL,
    `NoiDung` VARCHAR(255) NOT NULL,
    `HinhAnh` VARCHAR(255),
    `TrangThai` TINYINT(1) DEFAULT 1,
    `NgayTao` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. Bảng CHAT_MESSAGE
DROP TABLE IF EXISTS `Chat_Message`;
CREATE TABLE `Chat_Message` (
    `MaChat` INT AUTO_INCREMENT PRIMARY KEY,
    `SessionID` VARCHAR(100) NOT NULL,
    `Role` VARCHAR(20) NOT NULL,
    `Content` TEXT NOT NULL,
    `NgayTao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_chat_session` (`SessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;