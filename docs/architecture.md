# Kiến Trúc Hệ Thống - MeowTea Fresh

Tài liệu này mô tả chi tiết kiến trúc kỹ thuật của dự án MeowTea Fresh (Beverage Ordering System). Dự án được thiết kế theo mô hình **Client-Server** truyền thống dạng Multi-Page Application (MPA) sử dụng ngôn ngữ PHP thuần (không framework) ở backend, kết hợp với giao diện front-end sử dụng HTML, CSS, và JavaScript (jQuery).

---

## 1. Tổng Quan Kiến Trúc (Architecture Overview)

Kiến trúc của ứng dụng được chia thành các tầng rõ ràng mặc dù không sử dụng framework, giúp cho việc bảo trì và mở rộng dễ dàng:

1.  **Tầng Giao Diện (Presentation Layer / Front-end):** 
    - Nằm chủ yếu trong thư mục `pages/` và các thành phần dùng chung `components/`.
    - Sử dụng HTML/CSS thuần (tổ chức trong `assets/css/`) và JavaScript/jQuery (`assets/js/`) để thao tác DOM, xử lý sự kiện người dùng và gọi Ajax.
2.  **Tầng Trung Gian (API / Controller Layer):**
    - Tập trung tại thư mục `api/`.
    - Đóng vai trò như các controller điều phối nghiệp vụ (REST-like), nhận request từ phía client (qua thao tác submit form hoặc AJAX), xử lý logic, và trả về dữ liệu (thường là định dạng JSON).
3.  **Tầng Dịch Vụ và Truy Cập Dữ Liệu (Service / Data Access Layer):**
    - Chứa trong file `functions.php` (các hàm tiện ích, truy vấn DB, xử lý phiên - session).
    - Quản lý logic tính toán giá, cấu hình giỏ hàng, và truy xuất dữ liệu từ Database thông qua PDO.
4.  **Tầng Dữ Liệu (Database Layer):**
    - Hỗ trợ cả **MySQL** (cho môi trường local/XAMPP) và **PostgreSQL** (chạy trên Supabase).
    - Cấu hình kết nối nằm tĩnh trong `database/config.php`.

---

## 2. Cấu Trúc Khối Ứng Dụng (Directory Structure)

Cấu trúc thư mục được tổ chức theo module chức năng và phân tách vai trò:

```text
beverage_ordering_chatbot/
├── api/                   # (Tầng Controller/API) Điểm tiếp nhận request từ Client (xử lý logic & trả về JSON)
│   ├── auth/              # Xử lý đăng nhập, đăng ký, cập nhật hồ sơ, đổi mật khẩu.
│   ├── cart/              # Xử lý thêm, sửa, xóa, lấy giỏ hàng (lưu session/DB).
│   ├── chatbot/           # (Tính năng NoRAG) API xử lý logic trợ lý ảo chatbot tư vấn/gọi món.
│   ├── management/        # API cho quyền admin/staff (quản lý sản phẩm, danh mục, topping, khuyến mãi).
│   ├── menu/              # API tìm kiếm, lọc menu.
│   ├── order/             # API tạo đơn hàng, lấy lịch sử đơn, cập nhật trạng thái đơn hàng.
│   ├── product/           # API lấy chi tiết sản phẩm.
│   ├── promotion/         # API kiểm tra mã khuyến mãi (validate).
│   └── stores/            # API tìm kiếm cửa hàng.
├── assets/                # (Tầng Tĩnh) Các tài nguyên tĩnh (Static resources)
│   ├── css/               # File định dạng giao diện, dùng CSS thuần, không dùng framework (Bootstrap/Tailwind).
│   ├── js/                # Các file xử lý logic front-end (gọi AJAX, bắt sự kiện).
│   ├── img/               # Hình ảnh (logo, sản phẩm, banner).
│   └── md/news/           # File markdown lưu nội dung bài viết tin tức.
├── components/            # Các khối UI có thể tái sử dụng (Header, Footer, thẻ sản phẩm, carousel...).
├── database/              # Schema DB, file cấu hình kết nối DB (config.php), file script tạo dữ liệu mẫu.
├── docs/                  # Tài liệu dự án.
├── pages/                 # (Tầng View) Giao diện của các tính năng chính, mỗi thư mục là 1 trang
│   ├── auth/              # Giao diện Đăng nhập / Đăng ký.
│   ├── cart/              # Giao diện Giỏ hàng và Thanh toán (checkout).
│   ├── management/        # Giao diện cho Admin/Staff quản lý thông tin.
│   ├── menu/              # Trang xem sản phẩm.
│   ├── news/              # Trang xem tin tức (đọc từ markdown).
│   ├── profile/           # Trang thông tin cá nhân khách hàng.
│   └── stores/            # Trang hệ thống cửa hàng.
├── functions.php          # Chứa lõi logic (Truy vấn DB PDO, cấu hình hàm, xử lý session, render markdown).
└── index.php              # Trang chủ ứng dụng, đóng vai trò như landing page.
```

---

## 3. Mô Hình Mô Tả Dữ Liệu (Database Schema)

Hệ thống được thiết kế bằng Cơ sở dữ liệu quan hệ (RDBMS) với các nhóm thực thể chính:

### 3.1. Nhóm Người dùng và Phân quyền
- **Role:** Định nghĩa phân quyền hệ thống (Admin, Staff, Customer).
- **User:** Bảng lưu thông tin người dùng (Email, SĐT, Mật khẩu được hash) và có khóa ngoại trỏ tới Role.
- **Store:** Thông tin chi nhánh cửa hàng.
- **User_Store:** Bảng trung gian định nghĩa liên kết N-N giữa quản lý/nhân viên với cửa hàng phụ trách.

### 3.2. Nhóm Sản phẩm và Menu
- **Category:** Danh mục các nhóm đồ uống (Cà phê, Trà sữa, Trà trái cây...).
- **SanPham:** Chứa thông tin cấu hình sản phẩm cơ bản (Tên, Giá, Hình ảnh, Rating).
- **Option_Group & Option_Value:** Lõi của cấu hình đồ uống linh hoạt. Định nghĩa các nhóm tùy chọn (như Cỡ ly, Mức đá, Mức đường, Topping) và các giá trị đi kèm, gồm cả phí cộng thêm (`GiaThem`).
- **Product_Option_Group:** Bảng liên kết N-N xác định mặt hàng nào khả dụng với nhóm tùy biến nào.

### 3.3. Nhóm Thương mại (Giỏ hàng, Khuyến mãi & Thanh toán)
- **Cart, Cart_Item, Cart_Item_Option:** Lưu trữ thông tin giỏ hàng bền vững trong CSDL (song song với xử lý Session) hỗ trợ khả năng phân tích dữ liệu và giữ giỏ hàng cho user.
- **Promotion:** Bảng quản lý mã giảm giá, giới hạn ngân sách và thời gian kích hoạt.
- **Payment_Method:** Phân loại thanh toán (Tiền mặt, Thanh toán trực tuyến/QR).

### 3.4. Nhóm Đơn hàng (Orders)
- **Orders:** Lưu hóa đơn mẹ (tổng tiền, thông tin khách hàng, cửa hàng, trạng thái xử lý).
- **Order_Item & Order_Item_Option:** Chi tiết chính xác từng món nước và Option/Topping mà khách đã chọn trong hóa đơn phục vụ cho việc pha chế.

---

## 4. Các Luồng Nghiệp Vụ Chính (Core Workflows)

### 4.1. Luồng Xác thực và Phiên người dùng (Authentication & Session Flow)
1. **Login:** Người dùng điền credentials. Xử lý javascript (fetch/ajax) POST tới `api/auth/login.php`.
2. Endpoint này khởi tạo DB connection (`config.php`), lấy bản ghi, dùng `password_verify` so khớp hash.
3. Nếu thành công, `$_SESSION` sẽ lưu các trường cơ bản: `user_id`, `role`, `logged_in=true`. 
4. Hệ thống gọi `mergeCartWithDB()` để đồng bộ giỏ hàng cũ chưa thanh toán từ Database lên Session của trình duyệt.
5. Kiểm tra trạng thái đăng nhập được phân giải qua hàm helper `isLoggedIn()` ở file `functions.php`.

### 4.2. Luồng Giỏ hàng và Thanh toán
- Giỏ hàng được quản lý theo dạng **Hybrid**: Cả qua trình duyệt lưu Session mảng (`$_SESSION['cart']`) nhằm tối ưu độ trễ cho máy khách và CSDL qua bảng (`Cart`, `Cart_Item`) cho mục tiêu bền vững dữ liệu. Hỗ trợ sync với hàm `saveCartToDB` và `loadCartFromDB`.
- **Flow thanh toán:**
  1. Người dùng xác nhận ở `pages/cart/checkout.php`.
  2. Mã khuyến mãi (nếu có) được xác minh qua `api/promotion/validate.php`.
  3. POST gửi đến `api/order/create.php`, hệ thống thực thi **Database Transaction (COMMIT/ROLLBACK)** để thêm bản ghi vào `Orders` -> `Order_Item` -> `Order_Item_Option`. Đảm bảo tính toàn vẹn (ACID) của hóa đơn. Giỏ hàng cũ sau đó bị xóa.

### 4.3. Luồng Quản lý Đồ uống động (Dynamic Product Options)
Trải nghiệm gọi đồ uống (ví dụ, Trà Sữa + 50% Đá + Trân Châu) được thiết kế qua Data-driven design:
- Sản phẩm được tra cứu từ DB. `api/product/` hoặc logic trang menu truy vấn bảng `Product_Option_Group` theo `MaSP` nhằm kéo lên danh sách topping/cỡ để hiển thị ra UI. Do đó cho phép mở rộng nhanh chóng hàng loạt topping mới mà không phải sửa cứng (hardcode) trong logic ứng dụng.

### 4.4. Tính Năng Đoạn Hội Thoại (Chatbot NoRAG)
Dựa theo nhánh `Beverage_Ordering_NoRAG`, hệ thống được trang bị tính năng Chatbot tư vấn, logic đặt ở đường dẫn `api/chatbot/`. Module này sẽ xử lý đầu vào của khách để tự động trả lời, có khả năng tra cứu vào Database bằng các API có sẵn hoặc prompt để giới thiệu món và giải quyết nhu cầu đặt món mà không phụ thuộc hạ tầng Vector DB RAG bổ sung.

---

## 5. Đánh Giá Kiến Trúc Tính Bảo Mật & Hiệu Năng

- **Bảo mật (Security):** 
  - Backend sử dụng hàm `php password_hash()` với thuật toán Bcrypt được coi là chuẩn cho xác thực và an toàn nhất với PHP.
  - Các truy vấn CSDL đều sử dụng `PDO Prepare Statement` (`$pdo->prepare()`) triệt tiêu hoàn toàn khả năng SQL Injection.
  - Sử dụng tham số thoát đặc tả HTML tĩnh qua hàm `e(html_specialchars)` ở view nhằm hạn chế XSS (Cross Site Scripting).
  
- **Hiệu năng & Khả năng mở rộng (Performance & Scalability):**
  - Vì ứng dụng không có framework nên ít overhead, hiệu năng rất cao đối với lượng request từ nhỏ đến vừa, xử lý render template rất nhanh qua `require_once`.
  - Sự cho phép thay đổi CSDL sang PostgreSQL cho thấy khả năng linh hoạt chạy Backend dưới dạng serverless DB (Supabase) sẵn sàng cho môi trường đám mây phân tán.
  - Tách bạch rõ mô hình file (API trả JSON độc lập và trang HTML) giúp sau này dễ dàng tái cấu trúc (refactoring) để nâng cấp lên các kiến trúc Microservices hoặc Headless Frontend (React/Vue/Next.js) nếu cần đổi mới Client.
