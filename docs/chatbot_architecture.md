# Kiến Trúc và Công Nghệ Chatbot AI (MeowTea Fresh)

Khác với các module truyền thống quản lý nghiệp vụ bằng PHP, luồng Chatbot của MeowTea Fresh được tách rời thành một kiến trúc **Microservice** sử dụng Python. 

Tính năng `MeowBot` cho phép khách hàng tra cứu đồ uống, tìm kiếm chi nhánh, giải quyết khiếu nại và đặc biệt là **có khả năng đặt món (thêm vào giỏ hàng) tự động** bằng ngôn ngữ tự nhiên. 

Dưới đây là thiết kế chi tiết về luồng hoạt động, cấu trúc công nghệ và kỹ thuật tích hợp.

---

## 1. Stack Công Nghệ Lõi (Tech Stack)

### 1.1 Backend AI Microservice
- **Ngôn ngữ:** Python 3.11+.
- **Web Framework:** [FastAPI](https://fastapi.tiangolo.com/) đóng vai trò web server tạo các RESTful endpoint hiệu năng cao. Chạy qua Uvicorn (`localhost:8000`).
- **AI/LLM Orchestration:** [LangChain](https://python.langchain.com/) - Môi trường quản lý Agent, memory, và kích hoạt công cụ (Tool calling).
- **LLM Model:** Backend tích hợp hai nền tảng tùy chỉnh (Gemini 2.0 Flash hoặc **Groq Llama 3.3** để đạt tốc độ suy luận - Inference siêu tốc, thực tế theo mã nguồn sử dụng `ChatGroq`).

### 1.2 Embeddings và Lưu Trữ (Vector Database)
- **Supabase (PostgreSQL + pgvector):** Lưu trữ embedding của sản phẩm để có thể Semantic Search (Tìm kiếm ngữ nghĩa).
- Các bảng được thiết lập trong Supabase: 
  - `products_embedding`: Lưu biểu diễn vector của danh sách menu đồ uống.
  - `chat_sessions`: Lưu trạng thái hội thoại.
  - `complaints`: Tiếp nhận khiếu nại khách hàng.

### 1.3 PHP Proxy & Frontend Integration
- Khối ứng dụng PHP ở LocalHost (XAMPP) giữ vai trò như Identity & Access Management proxy: Xác thực người dùng (User Context) qua Session, trước khi đẩy Request sang FastAPI qua cURL (`api/chatbot/proxy.php`).
- Giao diện Client dùng JavaScript để hiển thị hộp thoại, đồng thời tiếp nhận các sự kiện **Action UI** mà AI xuất ra để giao tiếp với giỏ hàng.

---

## 2. Kiến Trúc Luồng Hoạt Động (Workflow)

Quá trình trò chuyện của một khách hàng với Chatbot diễn ra theo luồng sau: 

### Bước 1: Client gửi tin nhắn 
- Người dùng gõ "Cho mình 1 cốc trà sữa trân châu".
- Frontend gửi POST request tới file trung gian (Proxy) `api/chatbot/proxy.php`.

### Bước 2: PHP Proxy đóng gói Context
- Tránh tình trạng lộ cổng 8000 và bảo vệ thông tin, `proxy.php` sẽ:
  - Đọc `$_SESSION` để chèn thêm `user_id` và `user_role` (Ví dụ: Khách hàng số 5, nhân viên, khách vãng lai).
  - Khởi tạo UUID cho phiên chat, tạo chuỗi API Key Authentication nội bộ (`CHATBOT_SECRET_KEY`).
  - Chuyển tiếp Request sang FastAPI (`POST http://localhost:8000/chat`).

### Bước 3: LangChain & Agent Xử lý (FastAPI Server)
- **Init Agent:** File `agent.py` tiếp nhận context (Khách đã login hay chưa).
- **Phân Tích Ngữ Nghĩa:** Model **Groq/Gemini** phân tích câu "Cho mình 1 trà sữa trân châu".
- **Tool Calling (Function Calling):** 
  - Thông qua LangChain Tool Calling Agent, AI nhận định cần tìm sản phẩm.
  - Model tự động kích hoạt hàm `search_products_tool("trà sữa trân châu")`.
  - Nếu kết quả tìm thấy sản phẩm có ID=4, AI kiểm tra nếu khách đã đăng nhập, nó gọi tiếp một Tool khác là `get_product_details_tool(4)` để xem tùy chọn Đá/Đường.
  - AI sẽ chào khách: "Trà sữa trân châu của bạn đây. Bạn muốn uống size M hay L, và mức đá đường thế nào nhỉ?"

### Bước 4: Parse Actions và Trả Về (Response Formatting)
- Bên cạnh việc sinh ra ngôn ngữ tự nhiên, các Agent Tools được cấu hình để in ra chuỗi JSON đặc biệt `__ACTION_PAYLOAD__: {"__action": "add_to_cart", ...}`.
- Hàm `_extract_actions_and_clean_text()` phân tách chuỗi này, giấu nó khỏi câu trả lời của AI.
- FastAPI trả về đối tượng JSON gồm:
  - `reply`: Câu chat của AI ("Mình đã thêm trà sữa...").
  - `actions`: Mảng object chứa hành động. Vd: `[{"type": "add_to_cart", "data": {...}}]`.

### Bước 5: Cập Nhật Giao Diện (Client Side Rendering)
- Trình duyệt hiển thị text của AI. 
- JavaScript quét trường `actions`. Khi thấy `add_to_cart`, nó tự động kích hoạt logic Frontend: Trigger popup Giỏ Hàng hoặc trực tiếp gửi POST lên `api/cart/add.php`.

---

## 3. Hệ Sinh Thái Các Công Cụ (Tools) Của Chatbot

Chatbot MeowBot là một Agent độc lập, nhưng được cấp quyền tương tác với Database thông qua một kho vũ khí (tools) sau:

1. **`search_products_tool(query)`:** Kích hoạt chức năng truy vấn Vector Database trên Supabase hoặc tìm kiếm LIKE ở SQL để lấy danh sách tương quan.
2. **`get_product_details_tool(product_id)`:** Tra cứu chính xác Cấu hình Option (Topping, Size, Đường) của mặt hàng đã chọn.
3. **`add_to_cart_tool(product_id, quantity, options)`:** Sinh ra chuẩn Payload `__ACTION_PAYLOAD__` để đẩy lệnh xuống Frontend yêu cầu cộng sản phẩm vào giỏ. (Luôn ép buộc phải kiểm tra User đã Login mới gọi hàm này).
4. **`search_store_tool(district, city)`:** Tìm kiếm chi nhánh địa chỉ. Cơ chế Prompt của tác tử yêu cầu bắt buộc phải moi đủ thông tin cả Quận và Tỉnh từ khách thì hàm này mới được gọi.
5. **`get_order_status_tool(order_code)`:** Đối soát và tra cứu trạng thái giao hàng của hóa đơn dựa vào mã. 
6. **`complaint_tool(content)`:** Insert nội dung trực tiếp vào Supabase Complaints để Admin Review.

---

## 4. Kiểm Soát Hallucination (Ảo giác AI)

Giải pháp kiến trúc này áp dụng các kỹ thuật khắc nghiệt trong Prompt (`SYSTEM_PROMPT`) để kìm hãm lỗi "ảo giác" của LLMs:

- **Giới hạn số lần gọi:** Ép buộc chỉ gọi mỗi tool 1 lần nhằm tránh model rơi vào vòng lặp lặp đi lặp lại vô tận (Infinite loop).
- **Bê nguyên văn kết quả:** Khi tool nội bộ trả về Markdown format với giá cả, LLM bị cấm chỉ định việc tóm tắt hoặc tự ý thêm thông tin như "Nước uống này có mùi vị thanh mát".
- **Gắn Logic Ràng Buộc trong Prompt:** Việc "Thêm vô giỏ hàng" bị ép logic cứng theo User Context: Nếu `_build_user_context` báo là chưa đăng nhập, LLM sẽ từ chối gọi `add_to_cart_tool` và giao tiếp yêu cầu khách Đăng nhập ngay lập tức.
- **In-memory Window Context:** Module AI chỉ lưu trữ History trong một `ChatMessageHistory` ngắn giới hạn 20 message cuối cùng (10 lượt chat) trong RAM. Phòng ngừa tình trạng ngộ độc bộ nhớ ngữ cảnh và rò rỉ token với các khung chat dài.

## 5. Kết Luận

Kiến trúc AI của MeowTea là sự kết hợp nhuần nhuyễn giữa **Generative AI** (sinh ngôn ngữ) và **Deterministic Logic** (nghiệp vụ tĩnh).  
Trong khi LLM lo nhiệm vụ "hiểu" người dùng nói gì thì các Python Tools đảm nhận chức năng "làm" chính xác điều người dùng mong muốn, và PHP đóng vai trò làm cầu nối an toàn, bảo vệ dữ liệu nội bộ.
