# MeowTea Fresh

Website đặt đồ uống trực tuyến cho thương hiệu **MeowTea Fresh** — trà sữa, cà phê, trà trái cây, yogurt. Ứng dụng web PHP thuần (không framework) dùng Apache, MySQL, và REST API nội bộ.

---

## Tính Năng

- **Khách**: Xem menu, tìm kiếm & lọc sản phẩm, chọn tùy chọn (đường, đá, topping), giỏ hàng, checkout, mã khuyến mãi, xem tin tức, cửa hàng, đăng ký/đăng nhập.
- **Customer**: Hồ sơ cá nhân, đổi mật khẩu, lịch sử đơn hàng, xem chi tiết đơn.
- **Admin / Staff**: Quản lý sản phẩm, topping, khuyến mãi, đơn hàng (cập nhật trạng thái).

---

## Công Nghệ

- **Backend**: PHP 7.4+ (PDO, session)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML, CSS, JavaScript (jQuery)
- **Server**: Apache (XAMPP hoặc tương đương)

---

## Cấu Trúc Thư Mục

```
beverage-ordering-project/
├── api/                          # REST API
│   ├── auth/                     # login, logout, register, change-password, update-profile, get-users
│   ├── cart/                     # add, get, update, delete, clear, count
│   ├── management/               # products, categories, toppings, promotions, CRUD
│   ├── menu/                     # search
│   ├── order/                    # create, get, get-all, get-one-admin, get_one, update-status
│   ├── product/                  # get
│   ├── promotion/                # validate
│   └── stores/                   # search
├── assets/
│   ├── css/                      # base, main (import chung), header, footer, cart, menu, ...
│   ├── js/                       # cart, checkout, menu, profile, management, ...
│   ├── img/                      # logo, carousel, products, stores, news, avatar
│   └── md/news/                  # Nội dung markdown cho tin tức (1.md–4.md)
├── components/                   # header, footer, carousel, product-card, modal-box, snack-bar, ...
├── database/
│   ├── config.php
│   ├── schema.sql
│   └── seed-data.sql
├── docs/
│   └── PRESENTATION.md
├── pages/
│   ├── about/
│   ├── auth/                     # login, register
│   ├── career/
│   ├── cart/                     # index, checkout, order_result
│   ├── management/               # product-management, promotion-management, order-management
│   ├── menu/
│   ├── news/
│   ├── profile/                  # index, orders, order-detail-view, profile-info, profile-password
│   └── stores/
├── functions.php                 # Helper, DB, cart, session, auth
├── index.php                     # Trang chủ
├── README.md
└── SETUP.md
```

---

## Cấu Trúc Database

Database `meowtea_schema` gồm các bảng:

| Bảng | Mô tả |
|------|-------|
| `Role` | Vai trò: Admin, Staff, Customer |
| `User` | Người dùng |
| `Store` | Cửa hàng |
| `User_Store` | Liên kết User–Store (N–N) |
| `Category` | Danh mục sản phẩm |
| `SanPham` | Sản phẩm |
| `Option_Group` | Nhóm tùy chọn (đường, đá, topping) |
| `Option_Value` | Giá trị tùy chọn |
| `Product_Option_Group` | Liên kết sản phẩm–nhóm tùy chọn (N–N) |
| `Cart`, `Cart_Item`, `Cart_Item_Option` | Giỏ hàng |
| `Promotion` | Mã khuyến mãi |
| `Payment_Method` | Phương thức thanh toán |
| `Orders`, `Order_Item`, `Order_Item_Option` | Đơn hàng |
| `News` | Tin tức |

---

## Seed Data

Sau khi import `database/seed-data.sql`:

- **3 roles**: Admin, Staff, Customer  
- **12 cửa hàng** (HCM, Hà Nội, Cần Thơ, Đà Nẵng)  
- **3 users**: `admin`/`admin`, `staff`/`staff`, `cust`/`cust` (mật khẩu plain text, chỉ dùng demo)  
- **4 categories**: Cà phê truyền thống, Trà sữa, Trà trái cây, Yogurt  
- **3 option groups**: Mức đường, Mức đá, Topping  
- **16 sản phẩm** + liên kết `Product_Option_Group`  
- **4 phương thức thanh toán**, **2 khuyến mãi** (`WELCOME10`, `FIXED5K`), **4 tin tức** (markdown)

---

## Các Trang Chính

| Trang | Đường dẫn | Ghi chú |
|-------|-----------|---------|
| Trang chủ | `index.php` | Carousel, about, danh mục, best seller, tin tức |
| Menu | `pages/menu/index.php` | Sản phẩm, tìm kiếm, lọc category |
| Cửa hàng | `pages/stores/index.php` | Danh sách cửa hàng, tìm kiếm |
| Tin tức | `pages/news/index.php` | Danh sách tin, nội dung markdown |
| Giới thiệu | `pages/about/index.php` | About |
| Tuyển dụng | `pages/career/index.php` | Career |
| Giỏ hàng | `pages/cart/index.php` | Giỏ hàng & chỉnh sửa |
| Thanh toán | `pages/cart/checkout.php` | Checkout, mã KM, địa chỉ |
| Kết quả đơn | `pages/cart/order_result.php` | Trạng thái đơn sau khi đặt |
| Đăng nhập / Đăng ký | `pages/auth/login.php`, `register.php` | Auth |
| Thông tin tài khoản | `pages/profile/index.php` | Cá nhân, đổi mật khẩu |
| Đơn hàng của tôi | `pages/profile/orders.php` | Lịch sử đơn (Customer) |
| Chi tiết đơn | `pages/profile/order-detail-view.php` | AJAX/modal |
| Quản lý sản phẩm & topping | `pages/management/product-management.php` | Admin/Staff |
| Quản lý khuyến mãi | `pages/management/promotion-management.php` | Admin/Staff |
| Quản lý đơn hàng | `pages/management/order-management.php` | Admin/Staff |

---

## Lưu Ý

- `assets/css/main.css` import các CSS chung (`base`, `header`, `footer`, `carousel`, `product-card`, `news-card`, `buttons`, `pagination`, `snack-bar`, `modal-box`, `home`). Trang riêng thêm `cart.css`, `login.css`, `menu.css`, `stores.css`, `profile.css`, `management.css`, `promotion.css` tùy trang.
- `functions.php` load `database/config.php` và cung cấp helper, kết nối DB, xử lý cart/session, auth.
- Nhiều trang dùng `session_start()`; cần bật session (mặc định PHP đã bật).
- Đảm bảo `assets/img/` có đủ ảnh; tin tức dùng `assets/md/news/*.md`.

---

## Cài Đặt & Chạy

Xem **[SETUP.md](SETUP.md)** để biết yêu cầu, cấu hình database, cách đặt project và truy cập website.
