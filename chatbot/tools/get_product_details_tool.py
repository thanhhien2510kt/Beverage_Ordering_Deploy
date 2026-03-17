"""
Tool: Lấy thông tin chi tiết sản phẩm và các tùy chọn (Size, Topping, Đá, Đường)
Gọi PHP API api/product/get.php.
"""
import httpx
from langchain_core.tools import tool
from config import PHP_BASE_URL, CHATBOT_SECRET_KEY

@tool
def get_product_details_tool(product_id: int) -> str:
    """
    Lấy thông tin chi tiết của một sản phẩm cụ thể bao gồm giá và các tùy chọn (options).
    Dùng khi khách đã chọn một món nhưng chưa rõ size, topping, hoặc các yêu cầu khác.
    
    Args:
        product_id: ID sản phẩm (MaSP)
        
    Returns:
        JSON string chứa thông tin sản phẩm và danh sách các nhóm tùy chọn (Size, Đường, Đá, Topping).
    """
    if product_id <= 0:
        return "Mã sản phẩm không hợp lệ."
        
    try:
        r = httpx.get(
            f"{PHP_BASE_URL}/api/product/get.php",
            params={"id": product_id},
            headers={"X-Chatbot-Secret": CHATBOT_SECRET_KEY},
            timeout=5.0
        )
        data = r.json()
        
        if not data.get("success"):
            return f"Không tìm thấy thông tin cho sản phẩm #{product_id}."
            
        product_info = data.get("data", {})
        product = product_info.get("product", {})
        option_groups = product_info.get("optionGroups", [])
        
        res = [f"### Chi tiết sản phẩm: {product.get('TenSP')}"]
        res.append(f"Mã SP: {product.get('MaSP')}")
        res.append(f"Giá cơ bản: {int(product.get('GiaCoBan', 0)):,}₫".replace(",", "."))
        
        if option_groups:
            res.append("\n**Các tùy chọn có sẵn (Bạn hãy hỏi khách chọn những thứ này):**")
            for group in option_groups:
                res.append(f"\n+ Nhóm: {group['TenNhom']} (Chọn {'nhiều' if group['IsMultiple'] else 'một'}):")
                for opt in group['options']:
                    extra_price = f" (+{int(opt['GiaThem']):,}₫)".replace(",", ".") if opt['GiaThem'] > 0 else ""
                    res.append(f"  - {opt['TenGiaTri']} [Mã tùy chọn: {opt['MaOptionValue']}]{extra_price}")
        else:
            res.append("\nSản phẩm này không có tùy chọn thêm.")
            
        return "\n".join(res)
        
    except Exception as e:
        return f"Lỗi khi lấy chi tiết sản phẩm: {str(e)}"
