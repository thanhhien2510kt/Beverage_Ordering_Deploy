# Hướng Dẫn Cài Đặt và Chạy — MeowTea Fresh

## Yêu Cầu

- **XAMPP** (Apache + MySQL + PHP) hoặc môi trường PHP tương đương
- **PHP 7.4** trở lên (khuyến nghị 8.x)
- **MySQL 5.7** / **MariaDB 10.2** trở lên
- Các extension PHP: `pdo`, `pdo_mysql`, `mbstring`, `json`, `session`

---

## Các Bước Setup

### 1. Cấu Hình Database

1. Mở **XAMPP Control Panel**, khởi động **Apache** và **MySQL**.
2. Mở **phpMyAdmin**: <http://localhost/phpmyadmin>
3. **Import schema**:
   - Chọn tab **Import**
   - Chọn file `database/schema.sql`
   - Bấm **Go** → tạo database `meowtea_schema` và toàn bộ bảng.
4. **Import seed data**:
   - Chọn tab **Import**
   - Chọn file `database/seed-data.sql`
   - Bấm **Go**

### 2. Cấu Hình Kết Nối Database

Sửa các hằng ở đầu file `database/config.php` nếu khác môi trường của bạn:

| Hằng | Mặc định |
|------|----------|
| `DB_HOST` | `localhost` |
| `DB_USER` | `root` |
| `DB_PASS` | `` (trống) |
| `DB_NAME` | `meowtea_schema` |
| `DB_CHARSET` | `utf8mb4` |

File còn cung cấp: `getDBConnection()`, `testDBConnection()`.

### 3. Đặt Project và Chạy

1. **Đặt toàn bộ thư mục dự án** vào `htdocs` của XAMPP:
   - Ví dụ Windows: `C:\xampp\htdocs\beverage-ordering-project\`
   - Có thể đổi tên thư mục (ví dụ `meowtea`). Cấu trúc chi tiết xem [README.md](README.md).

2. **Truy cập website**:
   - <http://localhost/beverage-ordering-project/>
   - Hoặc <http://localhost/meowtea/> (nếu đổi tên thư mục)
   - Nếu đặt ngay trong `htdocs`: <http://localhost/>

3. **Trang chủ** do `index.php` tại thư mục gốc phụ trách (carousel, about, danh mục, best seller, tin tức).

---

## Lưu Ý

- **MySQL**: Đảm bảo MySQL đã chạy và database `meowtea_schema` đã được tạo từ `database/schema.sql`. Lỗi kết nối thường do `database/config.php` sai hoặc MySQL chưa bật.
- **Ảnh**: Thư mục `assets/img/` phải có đủ ảnh (logo, carousel, products, stores, news, avatar …). Thiếu ảnh sẽ gây broken image.
- **Session**: Ứng dụng dùng `session_start()`. Cần bật session (mặc định PHP đã bật).
- **Mật khẩu seed**: `admin`/`admin`, `staff`/`staff`, `cust`/`cust` là **plain text**, chỉ dùng demo. Môi trường production nên dùng `password_hash`/`password_verify` (đã có `hashPassword`/`verifyPassword` trong `functions.php`).

---

Giới thiệu dự án, tính năng, cấu trúc và danh sách trang: xem **[README.md](README.md)**.
