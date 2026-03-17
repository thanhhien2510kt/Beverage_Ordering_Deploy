"""
Script đồng bộ sản phẩm từ MySQL sang Supabase pgvector.

Cách chạy:
    cd chatbot
    python embeddings/sync_products.py

Nên chạy:
  - Lần đầu setup
  - Mỗi khi thêm / sửa / xóa sản phẩm trong MySQL
  - Có thể cron job mỗi ngày 1 lần
"""
import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import pymysql
import requests
from supabase import create_client, Client
import time

from config import (
    GOOGLE_API_KEY,
    SUPABASE_URL, SUPABASE_SERVICE_KEY,
    MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB,
    EMBEDDING_MODEL,
    validate_config
)

validate_config()

# Supabase client
supabase: Client = create_client(SUPABASE_URL, SUPABASE_SERVICE_KEY)

# Gemini Embedding REST API v1beta (Google AI Studio free keys dùng v1beta)
GEMINI_EMBED_URL = f"https://generativelanguage.googleapis.com/v1beta/models/{EMBEDDING_MODEL.replace('models/', '')}:embedContent"


def get_mysql_products() -> list[dict]:
    """Lấy tất cả sản phẩm đang hoạt động từ MySQL."""
    conn = pymysql.connect(
        host=MYSQL_HOST,
        port=MYSQL_PORT,
        user=MYSQL_USER,
        password=MYSQL_PASSWORD,
        database=MYSQL_DB,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor
    )
    try:
        with conn.cursor() as cursor:
            cursor.execute("""
                SELECT
                    sp.MaSP        AS product_id,
                    sp.TenSP       AS product_name,
                    sp.GiaCoBan    AS price,
                    c.TenCategory  AS category,
                    sp.Rating      AS rating
                FROM SanPham sp
                INNER JOIN Category c ON sp.MaCategory = c.MaCategory
                WHERE sp.TrangThai = 1
                ORDER BY sp.MaSP
            """)
            return cursor.fetchall()
    finally:
        conn.close()


def build_description(product: dict) -> str:
    """Tạo text mô tả sản phẩm để embed (tiếng Việt để embedding khớp với query)."""
    rating_str = f", đánh giá {product['rating']}/5" if product.get("rating") else ""
    price = int(product.get("price") or 0)
    price_str = f"{price:,}₫".replace(",", ".")
    return (
        f"{product['product_name']} - {product['category']} "
        f"giá {price_str}{rating_str}. "
        f"Thuộc danh mục {product['category']} của MeowTea Fresh."
    )


def get_embedding(text: str) -> list[float]:
    """Tạo vector embedding qua Gemini REST API v1 trực tiếp (bypass v1beta SDK)."""
    resp = requests.post(
        GEMINI_EMBED_URL,
        params={"key": GOOGLE_API_KEY},
        json={
            "model": EMBEDDING_MODEL,
            "content": {"parts": [{"text": text}]},
            "taskType": "RETRIEVAL_DOCUMENT",
            "outputDimensionality": 768
        },
        timeout=10
    )
    resp.raise_for_status()
    return resp.json()["embedding"]["values"]


def sync_products():
    """Main sync function."""
    print("🔄 Bắt đầu đồng bộ sản phẩm từ MySQL → Supabase...")

    # Lấy sản phẩm từ MySQL
    products = get_mysql_products()
    print(f"📦 Tìm thấy {len(products)} sản phẩm trong MySQL.")

    # Xóa toàn bộ embedding cũ trước khi sync mới
    # (Có thể dùng upsert nếu muốn incremental)
    supabase.table("products_embedding").delete().neq("id", 0).execute()
    print("🗑️  Đã xóa embedding cũ.")

    success_count = 0
    error_count = 0

    for product in products:
        product_id = product["product_id"]
        description = build_description(product)

        try:
            embedding = get_embedding(description)

            supabase.table("products_embedding").insert({
                "product_id": product_id,
                "product_name": product["product_name"],
                "category": product["category"],
                "price": float(product.get("price") or 0),
                "description": description,
                "embedding": embedding,
            }).execute()

            print(f"  ✅ [{product_id}] {product['product_name']}")
            success_count += 1

            # Rate limit: Gemini Embedding API có giới hạn ~60 RPM free tier
            time.sleep(0.3)

        except Exception as e:
            print(f"  ❌ [{product_id}] {product['product_name']} - Lỗi: {e}")
            error_count += 1

    print(f"\n🎉 Đồng bộ hoàn tất!")
    print(f"   ✅ Thành công: {success_count}")
    print(f"   ❌ Lỗi: {error_count}")
    print(f"   📊 Tổng: {len(products)}")


if __name__ == "__main__":
    sync_products()
