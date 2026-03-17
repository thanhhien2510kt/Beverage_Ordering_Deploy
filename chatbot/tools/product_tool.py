"""
Tool: Tìm kiếm sản phẩm MeowTea Fresh
Strategy:
  1. Gọi PHP API lấy danh sách sản phẩm.
  2. Lọc theo từ khóa (query) và danh mục (category) nếu có.
"""
import httpx
import unicodedata
from langchain_core.tools import tool

from config import (
    PHP_BASE_URL, CHATBOT_SECRET_KEY
)

def _normalize(text: str) -> str:
    """Bỏ tất cả dấu tiếng Việt để so sánh fuzzy: 'cà phè' → 'ca phe'."""
    nfkd = unicodedata.normalize('NFKD', text)
    return ''.join(c for c in nfkd if not unicodedata.combining(c)).lower()


def _format_product(p: dict) -> str:
    price_str = f"{int(p.get('price', 0)):,}₫".replace(",", ".")
    return f"• **{p['product_name']}** ({p.get('category', 'N/A')}) — {price_str} [ID: {p['product_id']}]"


@tool
def search_products_tool(query: str, category: str = "") -> str:
    """
    Tìm kiếm sản phẩm MeowTea Fresh theo mô tả hoặc tên.
    Dùng khi khách hỏi về sản phẩm, muốn xem menu, hoặc cần gợi ý đồ uống.

    Args:
        query: Mô tả hoặc tên sản phẩm khách muốn tìm (vd: "trà sữa ít ngọt", "cà phê đá")
        category: Danh mục tùy chọn để lọc thêm (vd: "Trà sữa", "Cà phê truyền thống")

    Returns:
        Danh sách sản phẩm phù hợp nhất
    """
    try:
        search_text = query.strip() or category.strip() or "đồ uống"

        # Step 1: Gọi PHP API — endpoint chatbot public
        try:
            r = httpx.get(
                f"{PHP_BASE_URL}/api/chatbot/products.php",
                headers={"X-Chatbot-Secret": CHATBOT_SECRET_KEY},
                timeout=5.0
            )
            mgmt_data = r.json()
            mgmt_products = mgmt_data.get("products", [])

            # So sánh sau khi bỏ dấu: 'cà phè' → 'ca phe' match 'Cà phê Cappuccino'
            norm_query = _normalize(search_text)
            products = []
            for p in mgmt_products:
                norm_name = _normalize(p.get("TenSP", ""))
                norm_cat  = _normalize(p.get("TenCategory", ""))
                # Match nếu bất kỳ 1 từ (sau bỏ dấu) trong query nằm trong tên/danh mục
                if any(kw in norm_name or kw in norm_cat
                       for kw in norm_query.split() if len(kw) >= 2):
                    products.append({
                        "product_id": p["MaSP"],
                        "product_name": p["TenSP"],
                        "category": p.get("TenCategory", ""),
                        "price": p.get("GiaCoBan", 0)
                    })
            products = products[:6]
        except Exception as e:
            return f"Lỗi khi kết nối với hệ thống sản phẩm: {str(e)}"

        if not products:
            return "Mình không tìm thấy sản phẩm phù hợp. Bạn thử: 'cà phê', 'trà sữa', 'yogurt' nhé!"

        lines = [f"Mình tìm được {len(products)} sản phẩm cho bạn:\n"]
        for p in products:
            price_str = f"{int(p.get('price', 0)):,}₫".replace(",", ".")
            lines.append(f"- [ID: {p['product_id']}] **{p['product_name']}** ({p.get('category', 'N/A')}) — Giá: {price_str}")
            
        lines.append("\n💡 Mẹo: Dùng `get_product_details_tool(product_id)` để xem chi tiết Size và Topping của từng món.")
        lines.append("\nBạn muốn thêm sản phẩm nào vào giỏ hàng không? 🛒")
        return "\n".join(lines)

    except Exception as e:
        return f"Lỗi khi tìm kiếm sản phẩm: {str(e)}"
