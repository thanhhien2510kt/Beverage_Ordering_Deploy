# MeowTea Fresh AI Chatbot Service

**Python FastAPI + LangChain + Gemini 2.0 Flash + Supabase pgvector**

---

## Yêu cầu hệ thống

- Python 3.11+
- XAMPP đang chạy (MySQL + Apache)
- Tài khoản [Supabase](https://supabase.com) + project sẵn có
- [Google AI API Key](https://aistudio.google.com/app/apikey)

---

## Bước 1: Setup Supabase Schema

1. Vào **Supabase Dashboard** → **SQL Editor**
2. Copy nội dung file `supabase_schema.sql` và chạy
3. Kiểm tra 3 bảng được tạo: `products_embedding`, `complaints`, `chat_sessions`

---

## Bước 2: Cài đặt Python dependencies

```bash
cd chatbot
python -m venv venv

# Windows
venv\Scripts\activate

# Mac/Linux
source venv/bin/activate

pip install -r requirements.txt
```

---

## Bước 3: Cấu hình môi trường

```bash
# Copy file config mẫu
copy .env.example .env

# Điền các giá trị sau vào .env:
# GOOGLE_API_KEY    → lấy từ https://aistudio.google.com/app/apikey
# SUPABASE_URL      → Project URL trong Supabase > Settings > API
# SUPABASE_SERVICE_KEY → service_role key trong Supabase > Settings > API
# PHP_BASE_URL      → thường là http://localhost/beverage_ordering_16_03
# CHATBOT_SECRET_KEY → tự đặt chuỗi bất kỳ (cùng với PHP proxy)
```

---

## Bước 4: Đồng bộ sản phẩm vào Supabase (lần đầu)

> Đảm bảo XAMPP MySQL đang chạy và database `meowtea_schema` đã được import.

```bash
python embeddings/sync_products.py
```

Output mong đợi:
```
🔄 Bắt đầu đồng bộ sản phẩm từ MySQL → Supabase...
📦 Tìm thấy 16 sản phẩm trong MySQL.
🗑️  Đã xóa embedding cũ.
  ✅ [1] Cà phê đen
  ✅ [2] Trà sữa Thái
  ...
🎉 Đồng bộ hoàn tất!
```

---

## Bước 5: Khởi chạy FastAPI service

```bash
python main.py
```

Service sẽ chạy tại: **http://localhost:8000**

- Docs (Swagger): http://localhost:8000/docs
- Health check: http://localhost:8000/health

---

## Test nhanh với curl

```bash
curl -X POST http://localhost:8000/chat \
  -H "Content-Type: application/json" \
  -d '{"message": "Trà sữa ngon nhất của MeowTea là gì?", "session_id": "test-1"}'
```

---

## Cấu trúc thư mục

```
chatbot/
├── main.py                    # FastAPI entry point
├── agent.py                   # LangChain Agent (Gemini + tools)
├── config.py                  # Cấu hình từ .env
├── requirements.txt           # Python dependencies
├── .env.example               # Template biến môi trường
├── supabase_schema.sql        # SQL schema cần chạy trong Supabase
├── tools/
│   ├── product_tool.py        # Tìm kiếm sản phẩm (vector + fallback)
│   ├── cart_tool.py           # Thêm vào giỏ hàng
│   ├── order_tool.py          # Tra cứu đơn hàng
│   └── complaint_tool.py      # Tiếp nhận khiếu nại
└── embeddings/
    └── sync_products.py       # Script đồng bộ MySQL → Supabase
```

---

## Lưu ý quan trọng

- **Gemini free tier**: Embedding API giới hạn ~60 RPM. Script sync có delay 0.3s/item.
- **Session**: FastAPI lưu agent instance in-memory. Khi restart server, history sẽ mất (frontend lưu lại qua `sessionStorage`).
- **Security**: `CHATBOT_SECRET_KEY` phải khớp với giá trị trong `api/chatbot/proxy.php`.
