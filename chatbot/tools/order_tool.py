"""
Tool: Tra cứu trạng thái đơn hàng
Gọi PHP API api/order/get_one.php để lấy thông tin đơn hàng.
"""
import httpx
from langchain_core.tools import tool
from config import PHP_BASE_URL

STATUS_MAP = {
    "Pending": "⏳ Chờ xác nhận",
    "Payment_Received": "💳 Đã thanh toán",
    "Processing": "🔄 Đang chuẩn bị",
    "Shipping": "🚚 Đang giao hàng",
    "Completed": "✅ Đã giao thành công",
    "Cancelled": "❌ Đã hủy",
}


@tool
def get_order_status_tool(order_id: int, user_id: int = 0) -> str:
    """
    Tra cứu trạng thái và thông tin đơn hàng của khách.
    CHỈ dùng khi khách cung cấp MÃ ĐƠN HÀNG cụ thể (số nguyên dương).
    KHÔNG tự suy đoán hay gọi tool này nếu khách chưa nói mã đơn.
    Nếu khách chưa cung cấp mã đơn, hãy hỏi lại: "Bạn cho mình biết mã đơn hàng nhé?"

    Args:
        order_id: Mã đơn hàng (số nguyên dương, ví dụ: 5 cho đơn #000000005).
                  PHẢI > 0. Không được truyền 0 hoặc giá trị mặc định.
        user_id: ID người dùng (để verify quyền xem đơn). Để 0 nếu không có.

    Returns:
        Thông tin chi tiết trạng thái đơn hàng
    """
    if order_id <= 0:
        return "Bạn vui lòng cung cấp mã đơn hàng cụ thể để mình tra cứu nhé! 🔍"
    try:
        r = httpx.get(
            f"{PHP_BASE_URL}/api/order/get_one.php",
            params={"order_id": order_id},
            timeout=5.0
        )
        data = r.json()

        if not data.get("success"):
            return f"Mình không tìm thấy đơn hàng #{order_id}. Bạn kiểm tra lại mã đơn nhé! 🔍"

        order = data.get("order", {})

        # Verify user ownership nếu có user_id
        if user_id and int(order.get("MaUser", 0)) != user_id:
            return "Mình không thể xem đơn hàng này vì nó không thuộc về tài khoản của bạn. 🔒"

        status_raw = order.get("TrangThai", "Unknown")
        status_display = STATUS_MAP.get(status_raw, status_raw)
        order_code = f"#{str(order_id).zfill(9)}"

        total = int(order.get("TongTien", 0))
        total_str = f"{total:,}₫".replace(",", ".")
        address = order.get("DiaChiGiao", "N/A")
        created = order.get("NgayTao", "N/A")

        return (
            f"📦 **Đơn hàng {order_code}**\n"
            f"• Trạng thái: {status_display}\n"
            f"• Tổng tiền: {total_str}\n"
            f"• Địa chỉ giao: {address}\n"
            f"• Ngày đặt: {created}\n\n"
            "Bạn có câu hỏi gì thêm về đơn hàng không? 😊"
        )

    except Exception as e:
        return f"Mình gặp lỗi khi tra cứu đơn hàng: {str(e)}. Vui lòng thử lại sau nhé!"
