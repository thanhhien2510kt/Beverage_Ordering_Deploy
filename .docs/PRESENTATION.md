## 1. Mở đầu – Bối cảnh & mục tiêu

Chào mọi người,  
hôm nay tôi sẽ giới thiệu với anh chị về **MeowTea Fresh** – một website demo cho mô hình chuỗi đồ uống hiện đại, bao gồm đầy đủ các tính năng từ **trải nghiệm khách hàng (đặt món online)** cho tới **quản trị vận hành (quản lý sản phẩm, khuyến mãi, topping, đơn hàng…)**.

Mục tiêu của buổi này:

- **Thứ nhất**, anh chị sẽ nắm được **bức tranh tổng thể** của hệ thống.
- **Thứ hai**, chúng ta sẽ **trải nghiệm hành trình người dùng**: từ xem menu, chọn món, đặt hàng, đến thanh toán.
- **Thứ ba**, tôi sẽ điểm qua **các công cụ dành cho quản trị viên** để quản lý dữ liệu và chương trình khuyến mãi.

---

## 2. Kiến trúc & công nghệ tổng quan

Trước khi vào demo, tôi nói rất ngắn gọn về mặt kỹ thuật để mọi người có bức tranh chung:

- **Back-end**
  - Viết bằng **PHP** thuần.
  - Tầng API tách riêng trong thư mục `api/` với các nhóm:
    - `auth/` – đăng ký, đăng nhập, cập nhật hồ sơ, đổi mật khẩu.
    - `cart/` – thêm/sửa/xóa/đếm sản phẩm trong giỏ.
    - `order/` – tạo và truy vấn đơn hàng.
    - `product/`, `promotion/`, `stores/` – lấy thông tin sản phẩm, khuyến mãi, cửa hàng.
    - `management/` – các API dành riêng cho admin (sản phẩm, danh mục, topping, khuyến mãi…).

- **Front-end**
  - Các trang giao diện nằm trong `pages/`:
    - `menu`, `stores`, `news`, `about`, `promotion`, `cart`, `profile`, `management`…
  - Sử dụng:
    - **HTML + CSS thuần** (các file trong `assets/css/`).
    - **JavaScript thuần + jQuery** (các file trong `assets/js/` như `main.js`, `cart.js`, `login.js`, `stores.js`…).
    - Các **component PHP tái sử dụng**: `header.php`, `footer.php`, `product-card.php`, `news-card.php`, `button.php`, `carousel.php`.

- **Database**
  - MySQL với schema `meowtea_schema`.
  - Các bảng chính: `User`, `Role`, `Store`, `Category`, `SanPham`, `Option_Group`, `Option_Value`, `Cart`, `Orders`, `Payment_Method`, `Promotion`, `News`…
  - Đã có script `schema.sql` & `seed-data.sql` để dựng dữ liệu mẫu.

Anh chị không cần nhớ chi tiết, chỉ cần hình dung: **giao diện phía trước gọi vào API phía sau, tất cả lưu vào một database thống nhất**.

---

## 3. Trải nghiệm khách hàng – Front-office

### 3.1. Trang chủ – Cửa ngõ thương hiệu

Khi truy cập `index.php`, người dùng sẽ thấy:

- **Hero Carousel**:
  - Ảnh banner đồ uống chạy tự động, thu hút ánh nhìn ngay từ đầu.
  - Đây là nơi có thể đặt các chương trình khuyến mãi, sản phẩm mới, chiến dịch thương hiệu.

- **Giới thiệu thương hiệu**
  - Đoạn giới thiệu ngắn về **MeowTea Fresh**: cam kết nguyên liệu, trải nghiệm, không gian.
  - Mục đích: tạo thiện cảm và niềm tin ngay lần đầu truy cập.

- **Danh mục sản phẩm nổi bật**
  - Các thẻ danh mục: **Cà phê**, **Trà sữa**, **Trà trái cây**.
  - Mỗi thẻ có hình minh họa, khi click sẽ chuyển thẳng sang trang **Menu** tương ứng danh mục.
  - Nút **“ĐẶT NGAY”** dẫn người dùng vào trang menu tổng, rút ngắn đường đi từ khám phá → hành động.

- **Hệ thống cửa hàng**
  - Banner giới thiệu mạng lưới cửa hàng, kèm nút “Xem thêm” để tới trang **Stores**.
  - Khu vực này nhấn mạnh: đây không chỉ là website, mà là một **chuỗi cửa hàng thực tế**.

- **Best Seller**
  - Danh sách các sản phẩm bán chạy (lấy từ database theo rating, lượt rating…).
  - Mục tiêu: **định hướng lựa chọn** cho khách mới, tối ưu tỉ lệ chuyển đổi.

- **Tin tức & sự kiện**
  - Các thẻ tin từ bảng `News`, nội dung lấy từ file markdown trong `assets/md/news/`.
  - Dùng để:
    - Truyền thông chương trình khuyến mãi.
    - Chia sẻ kiến thức đồ uống, lifestyle.
    - Tăng độ tươi mới cho website.

**Thông điệp khi trình bày:**  
Trang chủ không chỉ “đẹp”, mà đóng vai trò **định tuyến hành vi**:  
→ dẫn người dùng vào **menu**, **khuyến mãi**, **cửa hàng**, **tin tức** một cách rất tự nhiên.

---

### 3.2. Trang Menu – Khám phá & chọn món (`pages/menu/index.php`)

Tiếp theo, chúng ta đi vào **trang quan trọng nhất**: trang **Menu** – nơi mọi hành động đặt hàng bắt đầu.

Các ý chính để trình bày:

- **Danh mục & lọc sản phẩm**
  - Sản phẩm được chia theo `Category` trong database: Cà phê, Trà sữa, Trà trái cây, Đá xay, Yogurt…
  - Có thể truyền tham số `?category=` để lọc trực tiếp một nhóm sản phẩm (ví dụ từ trang chủ bấm “Cà Phê”).

- **Hiển thị thẻ sản phẩm**
  - Mỗi sản phẩm hiển thị qua `product-card.php`:
    - Hình ảnh.
    - Tên đồ uống.
    - Giá cơ bản.
    - Rating (số sao).
    - Thuộc danh mục nào.

- **Tùy chọn sản phẩm (options & topping)**
  - Mỗi sản phẩm có thể cấu hình thêm:
    - **Nhóm tùy chọn** (Option_Group): ví dụ đường, đá, size.
    - **Giá trị tùy chọn** (Option_Value): đường ít/đường vừa, thêm đá / ít đá…
  - Hệ thống còn có nhóm topping riêng, lấy bằng `getToppings()`:
    - Danh sách topping (trân châu, pudding, thạch dừa…).
    - Giá cộng thêm cho mỗi topping.
  - Điều này mô phỏng rất sát **quy trình gọi đồ** tại các thương hiệu trà sữa lớn.

- **Trải nghiệm người dùng trên menu**
  - Người dùng có thể:
    - Duyệt danh sách, tìm kiếm sản phẩm.
    - Click vào từng sản phẩm để xem chi tiết và chọn cấu hình (size, topping…).
    - Thêm vào giỏ hàng (sẽ chuyển qua JS và API `cart/` ở phần sau).

Khi demo, anh chị có thể:

- Chọn một sản phẩm nổi bật.
- Chỉ rõ: đây là **giá cơ bản**, topping nào sẽ **+ thêm bao nhiêu tiền**.
- Gọi ra cấu trúc phía sau: `SanPham` + `Option_Group` + `Option_Value`.

---

### 3.3. Trang Cửa hàng – Tìm địa điểm (`pages/stores/index.php`)

Trang **Stores** hỗ trợ người dùng:

- **Xem danh sách toàn bộ cửa hàng**
  - Lấy dữ liệu từ bảng `Store` qua các hàm như `getStores()` / `getStoresWithFilters()`.
  - Hiển thị tên cửa hàng, địa chỉ, trạng thái hoạt động.

- **Tìm kiếm & lọc**
  - Có các API & hàm tìm kiếm:
    - Theo **tên cửa hàng**: `searchStoresByName()`.
    - Theo **địa chỉ** (tỉnh/thành, phường/xã): `searchStoresByLocation()`.
  - Khi trình bày, có thể minh họa:
    - Gõ từ khóa “Quận 1” hoặc “Hà Nội” → kết quả thu hẹp.

- **Đếm tổng số cửa hàng**
  - `countStores()` dùng cho thống kê, hiển thị tổng số cửa hàng đang hoạt động.

Thông điệp:  
**Người dùng online** không chỉ đặt đồ uống, mà còn có thể **tìm địa điểm gần nhất** để tới trải nghiệm offline.

---

### 3.4. Trang Tin tức (`pages/news/index.php`)

Mục tiêu của trang **News**:

- Giúp thương hiệu:
  - Truyền thông các campaign mới.
  - Chia sẻ kiến thức, nội dung hữu ích.
  - Đẩy mạnh SEO & traffic tự nhiên.

- Về mặt kỹ thuật:
  - Bảng `News` lưu metadata (tiêu đề, ngày tạo, trạng thái…).
  - Nội dung chi tiết được lưu dưới dạng **markdown** trong `assets/md/news/1.md`, `2.md`…
  - Hàm `readMarkdownFile()` & `getNewsExcerpt()`:
    - Đọc file markdown.
    - Tự động cắt ngắn thành **đoạn mô tả** (excerpt) hiển thị ở card.
  - Kết quả: đội content có thể chỉnh sửa file `.md` mà không phải đụng tới code.

Khi trình bày, anh chị có thể nhấn mạnh:

- Đây là cách giúp **marketing** và **dev** phối hợp hiệu quả:
  - Dev lo phần render.
  - Marketing lo file `.md`.

---

### 3.5. Trang Khuyến mãi (`pages/management/promotion-management.php`)

Trang **Promotion** là nơi:

- Người dùng:
  - Xem các chương trình khuyến mãi đang chạy: giảm giá theo mã, theo sản phẩm, theo đơn hàng.
  - Hiểu rõ điều kiện áp dụng trước khi thanh toán.

- Hệ thống:
  - Sử dụng bảng `Promotion` trong database.
  - API `api/promotion/validate.php` để:
    - Kiểm tra mã khuyến mãi.
    - Tính toán mức giảm phù hợp với giỏ hàng hiện tại.

Trong demo:

- Anh chị có thể mô phỏng nhập một mã khuyến mãi ở bước thanh toán.
- Cho thấy:
  - Mã hợp lệ → tổng tiền cập nhật.
  - Mã sai/hết hạn → thông báo lỗi rõ ràng.

---

### 3.6. Giỏ hàng & Thanh toán (`pages/cart/`)

Phần này rất quan trọng vì là **trái tim thương mại** của website.

- **Giỏ hàng (`pages/cart/index.php`)**
  - Giao diện hiển thị:
    - Danh sách các món đã thêm.
    - Số lượng, giá từng món, tổng tiền.
    - Các tùy chọn cập nhật/xóa món.
  - Logic phía sau:
    - Dùng API trong `api/cart/`:
      - `add.php`: thêm sản phẩm vào giỏ.
      - `update.php`: thay đổi số lượng, topping.
      - `delete.php`: xóa item.
      - `clear.php`: xóa toàn bộ giỏ hàng.
      - `get.php`: lấy chi tiết giỏ.
      - `count.php`: lấy tổng số món – dùng hiển thị ở icon giỏ trên header.
    - JavaScript trong `assets/js/cart.js` chịu trách nhiệm:
      - Gửi request AJAX.
      - Render lại giao diện khi có thay đổi.

- **Checkout (`pages/cart/checkout.php`)**
  - Người dùng:
    - Nhập thông tin nhận hàng / lựa chọn nhận tại cửa hàng.
    - Chọn **phương thức thanh toán** (lấy từ bảng `Payment_Method` qua `getPaymentMethods()`).
    - Áp dụng **mã khuyến mãi** nếu có.
  - Hệ thống:
    - Tạo đơn hàng qua `api/order/create.php`.
    - Lưu xuống các bảng:
      - `Orders` (thông tin đơn).
      - `Order_Item` (từng món).
      - `Order_Item_Option` (topping, cấu hình đi kèm).

- **Kết quả đơn hàng (`pages/cart/order_result.php`)**
  - Hiển thị:
    - Đơn hàng thành công hay thất bại.
    - Mã đơn, tổng tiền, chi tiết sản phẩm.
  - Có thể dùng để:
    - Chuyển hướng người dùng về trang chủ.
    - Gợi ý sản phẩm khác (up-sell / cross-sell).

Trong phần trình bày, anh chị nên:

- Demo full flow:
  1. Chọn 1–2 món ở menu.
  2. Vào giỏ → chỉnh số lượng.
  3. Sang checkout → nhập thông tin + chọn phương thức thanh toán.
  4. Hoàn tất đơn → xem trang kết quả.

---

### 3.7. Đăng nhập, đăng ký & Hồ sơ người dùng

#### 3.7.1. Đăng nhập / Đăng ký (`pages/auth/login.php`, `register.php`)

- **Đăng ký**
  - Tạo tài khoản mới:
    - Thông tin cơ bản: họ tên, email, số điện thoại, mật khẩu.
    - Mặc định gán **role = Customer**.
  - API `api/auth/register.php` xử lý:
    - Validate input.
    - Hash mật khẩu bằng `hashPassword()` (bcrypt).
    - Lưu vào bảng `User`.

- **Đăng nhập**
  - Người dùng nhập email + mật khẩu.
  - API `api/auth/login.php`:
    - Lấy thông tin user từ DB.
    - Dùng `verifyPassword()` để kiểm tra:
      - Hỗ trợ cả mật khẩu bcrypt và plain-text (cho mục đích demo/seed).
    - Nếu đúng:
      - Lưu session: `user_id`, `username`, `role`, v.v…
      - Đánh dấu `logged_in = true`.

- **Đăng xuất**
  - Gọi tới `api/auth/logout.php` hoặc dùng hàm `logout()`:
    - Xóa session, hủy phiên đăng nhập.

Khi trình bày, anh chị có thể nhấn mạnh:

- Đây là nền tảng cho mọi **tính năng cá nhân hóa**:
  - Xem lịch sử đơn.
  - Cập nhật thông tin cá nhân.
  - Phân quyền admin/staff.

#### 3.7.2. Hồ sơ cá nhân (`pages/profile/index.php`)

Trang **Profile** cho phép:

- **Hiển thị thông tin người dùng hiện tại**
  - Lấy qua `getCurrentUser()` từ session:
    - Họ, tên, email, số điện thoại, giới tính, vai trò.
  - Sinh avatar chữ cái đầu bằng `getAvatarInitial()` hoặc `getAvatarInitialFromName()`.

- **Cập nhật thông tin**
  - Thay đổi:
    - Họ tên, email, số điện thoại.
    - Giới tính (để đổi avatar hình ảnh).
  - API `api/auth/update-profile.php` nhận request và lưu lại DB.

- **Đổi mật khẩu**
  - Dùng `api/auth/change-password.php`:
    - Yêu cầu mật khẩu cũ.
    - Hash mật khẩu mới bằng `hashPassword()`.
    - Cập nhật trong bảng `User`.

Phần này cho thấy:  
Website không chỉ là một **trang đặt đồ uống**, mà còn là một **tài khoản cá nhân** gắn với hành vi mua hàng của từng khách.

---

## 4. Trải nghiệm quản trị – Back-office (`pages/management/`)

Đối với đội vận hành, các trang **Management** là trung tâm điều khiển:

- **Quản lý sản phẩm (Product Management)**
  - `api/management/products.php`, `create-product.php`, `delete-product.php`, `update-price.php`:
    - Thêm mới sản phẩm.
    - Cập nhật giá, trạng thái (ẩn/hiện).
    - Xóa sản phẩm không còn kinh doanh.

- **Quản lý danh mục (Category Management)**
  - `api/management/categories.php`:
    - Thêm/sửa/xóa danh mục: Cà phê, Trà sữa, Đá xay…
    - Điều chỉnh thứ tự hiển thị.

- **Quản lý topping & option group**
  - `api/management/create-topping.php`, `delete-topping.php`, `update-topping-price.php`:
    - Thêm topping mới với giá cộng thêm.
    - Chỉnh sửa giá topping hiện có.
    - Gắn topping vào sản phẩm thông qua `Option_Group`/`Option_Value`.

- **Quản lý khuyến mãi (Promotion Management)**
  - `api/management/create-promotion.php`, `update-promotion.php`, `delete-promotion.php`, `promotions.php`:
    - Tạo campaign giảm giá theo:
      - Mã code.
      - Thời gian bắt đầu/kết thúc.
      - Điều kiện đơn hàng / sản phẩm áp dụng.
    - Cập nhật hoặc tắt chương trình khi đã hết hiệu lực.

- **Quản lý đơn hàng (Order Management)**
  - Thông qua `api/order/get.php`:
    - Xem danh sách đơn đã tạo.
    - Lọc theo trạng thái, thời gian, cửa hàng.
  - Giúp đội vận hành theo dõi và đối soát.

Trong phần trình bày, anh chị nên nhấn mạnh:

- **Một hệ thống bán hàng online chỉ thực sự “sống” khi đội vận hành có thể tự tay quản lý dữ liệu**, không phải nhờ dev mỗi lần đổi menu.

---

## 5. Luồng demo đề xuất trong buổi thuyết trình

Anh chị có thể sử dụng kịch bản demo sau, bám theo flow người dùng:

1. **Giới thiệu nhanh trang chủ**
   - Lướt qua hero, danh mục, best seller, tin tức.
   - Chỉ ra những “điểm chạm” dẫn sâu vào chức năng.

2. **Đi vào Menu**
   - Chọn một danh mục, ví dụ Trà sữa.
   - Mở chi tiết 1 sản phẩm, chọn topping, size.
   - Thêm vào giỏ hàng.

3. **Xem giỏ hàng & cập nhật**
   - Vào `pages/cart/index.php`.
   - Tăng/giảm số lượng, xóa bớt, quan sát cập nhật tổng tiền.
   - Cho thấy số lượng giỏ hàng trên header thay đổi theo.

4. **Thanh toán & khuyến mãi**
   - Chuyển sang `checkout.php`.
   - Nhập thông tin người nhận, chọn phương thức thanh toán.
   - Thử nhập 1 mã khuyến mãi (hợp lệ và không hợp lệ).
   - Hoàn tất đơn, đến trang kết quả.

5. **Đăng nhập & hồ sơ**
   - Nếu chưa login: đăng ký tài khoản mới, sau đó đăng nhập.
   - Vào trang `profile`:
     - Thay đổi thông tin, quan sát avatar chữ cái đầu.
     - Thử đổi mật khẩu.

6. **Chuyển sang góc nhìn admin**
   - Đăng nhập bằng tài khoản admin (từ seed data).
   - Truy cập `management`:
     - Thêm một sản phẩm mới hoặc cập nhật giá sản phẩm.
     - Thêm một topping mới, gán giá cộng thêm.
     - Tạo một chương trình khuyến mãi mới.
   - Quay lại trang menu/checkout:
     - Cho thấy sản phẩm/topping/khuyến mãi mới đã xuất hiện thực tế.

---

## 6. Kết luận

Để kết lại, anh chị có thể nhấn mạnh 3 ý:

- **Thứ nhất**, MeowTea Fresh là một **mô hình thu nhỏ** nhưng tương đối đầy đủ cho một website đặt đồ uống online: từ menu, khuyến mãi, giỏ hàng, thanh toán, cho tới quản lý cửa hàng & tin tức.
- **Thứ hai**, hệ thống được xây dựng với **kiến trúc rõ ràng**: tách API – giao diện – database, hỗ trợ mở rộng và nâng cấp dễ dàng.
- **Thứ ba**, đây là một **nền tảng tốt để học và phát triển thêm**:
  - Có thể tích hợp cổng thanh toán thật.
  - Mở rộng phân quyền chi tiết hơn.
  - Thêm dashboard báo cáo doanh thu, hành vi khách hàng.

Cảm ơn anh chị đã lắng nghe.  
Bây giờ chúng ta có thể chuyển sang phần **hỏi đáp** hoặc demo sâu hơn vào phần mà anh chị quan tâm nhất.
