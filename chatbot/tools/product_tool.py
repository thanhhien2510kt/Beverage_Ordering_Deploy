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

def _decode_unicode_escapes(s: str) -> str:
    """Decode literal \\u00e0-style escape sequences that Groq may pass as ASCII."""
    import re
    return re.sub(r'\\u([0-9a-fA-F]{4})', lambda m: chr(int(m.group(1), 16)), s)


def _normalize(text: str) -> str:
    """Bỏ tất cả dấu tiếng Việt để so sánh fuzzy: 'cà phè' → 'ca phe'."""
    nfkd = unicodedata.normalize('NFKD', text)
    return ''.join(c for c in nfkd if not unicodedata.combining(c)).lower()


def _format_product(p: dict) -> str:
    price_str = f"{int(p.get('price', 0)):,}₫".replace(",", ".")
    return f"• **{p['product_name']}** ({p.get('category', 'N/A')}) — {price_str} [ID: {p['product_id']}]"


@tool
def search_products_tool(query: str, category: str = "", is_specific_search: bool = False) -> str:
    """
    Tìm kiếm sản phẩm trong menu. Hỗ trợ tìm kiếm mờ không dấu.
    - Dùng khi khách muốn xem menu, tìm món chung chung (cà phê, trà, trà sữa) hoặc tìm món cụ thể.
    - query: Mô tả món khách muốn (VD: "cà phê", "nước uống", "trà sữa dâu tây"). Nếu khách hỏi toàn bộ menu thì truyền "".
    - is_specific_search: Bắt buộc truyền là True nếu khách hàng đang hỏi đích danh MỘT MÓN CỤ THỂ (VD: "Trà sữa Thái Đỏ", "Có bán cà phê muối không?"). Truyền là False nếu khách rủ rê, nhờ gợi ý (VD: "gợi ý đồ uống mát", "món nào ngon").
    - Danh sách trả về đã tự động được sắp xếp giảm dần theo lượt bán (Đã bán/DaBan DESC) để gợi ý các món được yêu thích nhất.
    Nếu khách hỏi "món nào bán chạy nhất", HÃY LẤY NGAY CÁC MÓN ĐẦU TIÊN mà tool trả về và tự tin giới thiệu với khách vì đó là số liệu bán chạy chính thức từ quán, kèm theo số lượng Đã bán!

    Args:
        query: Mô tả hoặc tên sản phẩm (vd: "trà sữa bán chạy nhất", "cà phê đá")
        category: Danh mục tùy chọn để lọc thêm
        Danh sách sản phẩm (đã được sắp xếp xếp hạng bán chạy nhất từ trên xuống dưới)
    """
    try:
        # Decode literal \u00e0-style escapes Groq may pass instead of real Unicode
        query = _decode_unicode_escapes(query)
        category = _decode_unicode_escapes(category)
        search_text = query.strip() or category.strip() or "đồ uống"

        # Step 1: Gọi PHP API — endpoint chatbot public
        try:
            r = httpx.get(
                f"{PHP_BASE_URL}/api/chatbot/products.php",
                headers={"X-Chatbot-Secret": CHATBOT_SECRET_KEY},
                timeout=8.0
            )
            print(f"[DEBUG] HTTP {r.status_code} | body[:300]: {r.text[:300]}")
            if r.status_code != 200 or not r.text.strip():
                return f"Hệ thống sản phẩm đang bận (HTTP {r.status_code}). Vui lòng thử lại nhé! 🙏"
            mgmt_data = r.json()
            mgmt_products = mgmt_data.get("products", [])
        except Exception as e:
            return f"Lỗi khi kết nối với hệ thống sản phẩm: {str(e)}"

        # Map category → emoji
        cat_icon = {
            "c ph truyn thng": "☕",
            "tr s": "🧋", 
            "tr tri cy": "🍓",
            "yogurt": "🥛",
        }
        import unicodedata as _ud
        def _cat_icon(cat):
            k = ''.join(c for c in _ud.normalize('NFKD', cat.lower()) if not _ud.combining(c))
            for key, icon in cat_icon.items():
                if key in k:
                    return icon
            return "🍹"

        # So sánh sau khi bỏ dấu: 'cà phè' → 'ca phe' match 'Cà phê Cappuccino'
        norm_query = _normalize(query.strip())
        norm_cat_param = _normalize(category.strip())
        
        # Xử lý đồng nghĩa: khách gọi 'sữa chua' nhưng db lưu 'yogurt'
        if "sua chua" in norm_query and "yogurt" not in norm_query:
            norm_query += " yogurt"
            
        if not norm_query and norm_cat_param:
            norm_query = norm_cat_param
            
        if not norm_query:
            norm_query = "do uong"
        
        # Tự động trả về danh mục nếu khách hỏi menu chung chung
        menu_keywords = ["menu", "thuc don", "danh muc"]
        # Chỉ trả về danh mục nếu từ khóa quá ngắn hoặc đích danh hỏi menu
        if norm_query in ["xem", "tat ca"] or any(hw in norm_query.split() for hw in menu_keywords):
            categories = list(set(p.get("tencategory") or p.get("TenCategory", "") for p in mgmt_products))
            categories = [c for c in categories if c]
            if categories:
                lines = ["Dạ quán có menu cực kì hấp dẫn luôn ạ! 🎉 \nHiện tại quán có các danh mục thức uống sau:\n"]
                for c in sorted(categories):
                    icon = _cat_icon(c)
                    lines.append(f" {icon} **{c}**")
                lines.append("\nBạn đang quan tâm đến danh mục nào nhỉ? Bật mí cho mình để mình liệt kê các món ngon nhất nha! 😽")
                return "\n".join(lines)

        # Lọc bớt từ khóa nhiễu
        ignore_kws = {"menu", "thuc", "don", "danh", "muc", "cho", "xem", "co", "khong", "nao", "ban", "chay", "nhat", "cac", "mon", "top", "nhung", "ngon", "gia", "bao", "nhieu", "tien", "la", "gi", "cay"}
        query_words = set(kw for kw in norm_query.split() if len(kw) >= 2 and kw not in ignore_kws)
        cleaned_query = " ".join(kw for kw in norm_query.split() if len(kw) >= 2 and kw not in ignore_kws)

        scored_products = []
        for p in mgmt_products:
            # Liên kết với CSDL trả về trường chữ thường
            norm_name = _normalize(p.get("tensp") or p.get("TenSP", ""))
            norm_cat  = _normalize(p.get("tencategory") or p.get("TenCategory", ""))
            name_words = set(norm_name.split())
            cat_words = set(norm_cat.split())

            score = 0
            
            # 0. Ưu tiên tuyệt đối nều AI xác định được danh mục (Category Parameter)
            if norm_cat_param and norm_cat_param in norm_cat:
                score += 5000

            # 1. Exact phrase match
            if cleaned_query and cleaned_query in norm_name:
                score += 1000
            elif cleaned_query and cleaned_query in norm_cat:
                score += 500

            # 2. Word match
            for qw in query_words:
                if qw in name_words:
                    score += 20
                elif any(qw in nw for nw in name_words):
                    score += 10
                elif qw in cat_words:
                    score += 8
                elif any(qw in cw for cw in cat_words):
                    score += 4

            if score > 0:
                scored_products.append((score, p))

        # Sort by score descending. Python's sort is stable, so equal scores preserve the original DaBan DESC order!
        scored_products.sort(key=lambda x: x[0], reverse=True)
        
        # Lấy danh sách Exact Matches (những món có chuỗi tên khớp tuyệt đối cụm tìm kiếm)
        exact_matches = [(s, p) for s, p in scored_products if s >= 1000]
        
        # Chiến thuật: Nếu khách tìm đích danh 1-2 món (vd "trà sữa dâu tây"), ta CHỈ hiển thị đúng món đó để tránh loãng.
        # Nhưng nếu khách gõ chữ chung chung như "trà sữa" (có tới 5 món Exact Match), ta vẫn liệt kê tất cả (tối đa 6).
        if exact_matches and len(exact_matches) <= 2:
            source_list = exact_matches
        elif exact_matches:
            source_list = exact_matches[:6]
        else:
            source_list = scored_products[:6]

        products = []
        for score, p in source_list:
            products.append({
                "product_id": p.get("masp") or p.get("MaSP"),
                "product_name": p.get("tensp") or p.get("TenSP", ""),
                "category": p.get("tencategory") or p.get("TenCategory", ""),
                "price": p.get("gianiemyet") or p.get("GiaNiemYet") or p.get("giacoban") or p.get("GiaCoBan", 0),
                "rating": p.get("rating"),
                "soluotrating": p.get("soluotrating", 0),
                "daban": p.get("daban", 0)
            })

        if not products:
            return "Mình không tìm thấy sản phẩm phù hợp. Bạn thử các danh mục như: 'Cà phê', 'Trà sữa', 'Yogurt' nhé!"

        lines = []
        if exact_matches:
            if len(products) == 1:
                lines.append("🔍 Dưới đây là thông tin món bạn đang tìm:\n")
            else:
                lines.append(f"🔍 Mình tìm được **{len(products)} món** phù hợp nhất cho bạn:\n")
        else:
            if is_specific_search:
                lines.append(f"🥺 Dạ MeowTea Fresh hiện chưa có món '{query}'. Nhưng tụi mình có các món tương tự bán rất chạy, bạn tham khảo thử nha:\n")
            else:
                lines.append("✨ Dưới đây là các món đỉnh nhất phù hợp với yêu cầu của bạn, bạn tham khảo nha:\n")

        for p in products:
            price_str = f"{int(p.get('price', 0)):,}đ".replace(",", ".")
            icon = _cat_icon(p.get('category', ''))
            rating_val = p.get('rating')
            reviews = p.get('soluotrating', 0)
            daban = p.get('daban', 0)
            
            rating_str = f"⭐ {rating_val} ({reviews} đánh giá)" if rating_val else "Chưa có đánh giá"
            daban_str = f"🔥 Đã bán: {daban}" if daban > 0 else "🔥 Đã bán: 0"
            
            lines.append(
                f"{icon} **{p['product_name']}**\n"
                f"   • Phân loại: {p.get('category', 'N/A')}\n"
                f"   • {daban_str} | Giá: **{price_str}** | {rating_str} | Mã: `{p['product_id']}`"
            )

        lines.append("\n---\n💬 Bạn muốn đặt món nào hông? Mình sẵn sàng tư vấn thêm ạ! 🐾")
        return "\n".join(lines)

    except Exception as e:
        return f"Lỗi khi tìm kiếm sản phẩm: {str(e)}"

