"""
Tool: Thêm sản phẩm vào giỏ hàng
Gọi ngược PHP API api/cart/add.php thông qua HTTP request.

Lưu ý: PHP cart dùng session, nên cần session token từ browser.
Trong flow chatbot, browser JS sẽ tự gọi cart API sau khi AI confirm.
Tool này trả về instruction cho frontend thực hiện action.
"""
import json
import httpx
from langchain_core.tools import tool
from config import PHP_BASE_URL


@tool
def add_to_cart_tool(
    product_id: int,
    quantity: int = 1,
    options: str = "[]",
    product_name: str = ""
) -> str:
    """
    Thêm sản phẩm vào giỏ hàng của khách.
    Dùng khi khách nói muốn mua, đặt, hoặc thêm sản phẩm vào giỏ.

    Args:
        product_id: ID sản phẩm (MaSP từ kết quả tìm kiếm)
        quantity: Số lượng muốn mua (mặc định 1)
        options: JSON string danh sách tùy chọn (đường, đá, topping). 
                 Ví dụ: '[{"option_value_id": 1, "price": 0}]'
        product_name: Tên sản phẩm (để hiển thị xác nhận)

    Returns:
        Xác nhận và instruction cho frontend thực hiện thêm vào giỏ
    """
    # Validate options JSON
    try:
        options_list = json.loads(options) if isinstance(options, str) else options
        if not isinstance(options_list, list):
            options_list = []
    except json.JSONDecodeError:
        options_list = []

    # Lấy thông tin giá sản phẩm từ PHP API
    try:
        r = httpx.get(
            f"{PHP_BASE_URL}/api/product/get.php",
            params={"id": product_id},
            timeout=5.0
        )
        data = r.json()
        # API trả về { success: true, data: { product: {...} } }
        product = (data.get("data") or {}).get("product") if data.get("success") else None
        if product:
            base_price = float(product.get("GiaCoBan", 0))
            product_name = product_name or product.get("TenSP", f"Sản phẩm #{product_id}")
        else:
            base_price = 0
            product_name = product_name or f"Sản phẩm #{product_id}"
    except Exception:
        base_price = 0
        product_name = product_name or f"Sản phẩm #{product_id}"

    # Tính tổng giá
    options_price = sum(float(opt.get("price", 0)) for opt in options_list)
    total_price = (base_price + options_price) * quantity

    # Trả về action payload cho frontend
    action_payload = {
        "__action": "add_to_cart",
        "product_id": product_id,
        "quantity": quantity,
        "base_price": base_price,
        "total_price": total_price,
        "options": json.dumps(options_list)
    }

    price_str = f"{int(total_price):,}₫".replace(",", ".")

    return (
        f"✅ Mình sẽ thêm **{quantity}x {product_name}** vào giỏ hàng cho bạn!\n"
        f"💰 Tổng: **{price_str}**\n"
        f"__ACTION_PAYLOAD__: {json.dumps(action_payload, ensure_ascii=False)}"
    )
