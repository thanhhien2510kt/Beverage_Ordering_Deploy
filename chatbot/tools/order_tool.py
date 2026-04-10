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
def get_recent_orders_tool(user_id: int) -> str:
    """
    Tra cứu danh sách 5 đơn hàng NHỎ NHẤT (GẦN NHẤT) mà khách đã đặt gần đây.
    DÙNG KHI: 
    - Khách nói "tôi muốn đặt lại độ uống giống hôm qua/lần trước".
    - Khách muốn tra cứu đơn nhưng KHÔNG NHỚ MÃ ĐƠN HÀNG.
    
    Lưu ý: Yêu cầu khách phải đăng nhập (user_id > 0). Lấy danh sách để AI trình bày lại cho khách, hỏi khách xem khách muốn thao tác với đơn nào.
    
    Args:
        user_id: ID khách hàng (Lấy từ context "User ID: X"). Bắt buộc > 0.
    """
    if user_id <= 0:
        return "Để xem lịch sử đơn hàng, bạn vui lòng đăng nhập vào tài khoản MeowTea trước nha! 🐾"
    try:
        r = httpx.get(
            f"{PHP_BASE_URL}/api/chatbot/orders.php",
            params={"action": "recent", "user_id": user_id},
            headers={"X-Chatbot-Secret": "MeowTea_Secret_2026_@abcxyz"},
            timeout=5.0
        )
        data = r.json()
        if not data.get("success"):
            return f"Lỗi khi lấy lịch sử đơn: {data.get('message')}"
            
        orders = data.get("orders", [])
        if not orders:
            return "Bạn chưa có đơn hàng nào trong thời gian gần đây ạ! 🥺"
            
        lines = ["Dưới đây là 5 đơn hàng gần nhất của bạn:\n"]
        for o in orders:
            order_id = o.get('MaOrder') or o.get('maorder')
            date_str = o.get('NgayTao') or o.get('ngaytao')
            total = int(o.get('TongTien', o.get('tongtien', 0)))
            
            raw_status = o.get('TrangThai') or o.get('trangthai', '')
            status = STATUS_MAP.get(raw_status, raw_status)
            
            lines.append(f"📦 **Mã đơn: {order_id}**")
            lines.append(f"   • Ngày: {date_str} | Trạng thái: {status} | Tổng: {total:,}₫".replace(",", "."))
            
        lines.append("\nBạn dùng mã đơn cụ thể nếu muốn xem chi tiết các món bên trong nhé! ✨")
        return "\n".join(lines)
    except Exception as e:
        return f"Lỗi: {str(e)}"

@tool
def get_order_status_tool(order_id: int, user_id: int = 0) -> str:
    """
    Tra cứu trạng thái và CHI TIẾT CÁC MÓN bên trong 1 đơn hàng cụ thể.
    CHỈ dùng khi khách CUNG CẤP MÃ ĐƠN HÀNG cụ thể (số nguyên dương) HOẶC sau khi bạn đã lấy được danh sách đơn gần nhất và khách chọn 1 đơn.
    KHÔNG tự suy đoán mã đơn. Nếu khách chưa có mã đơn, hãy dùng `get_recent_orders_tool` để tìm giúp khách.

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
            f"{PHP_BASE_URL}/api/chatbot/orders.php",
            params={"action": "detail", "order_id": order_id},
            headers={"X-Chatbot-Secret": "MeowTea_Secret_2026_@abcxyz"},
            timeout=5.0
        )
        data = r.json()

        if not data.get("success"):
            return f"Mình không tìm thấy đơn hàng #{order_id}. Bạn kiểm tra lại mã đơn nhé! 🔍"

        order = data.get("order", {})

        # Verify user ownership nếu có user_id
        db_user_id = int(order.get("MaUser") or order.get("mauser") or 0)
        if user_id and db_user_id != user_id:
            return "Mình không thể xem đơn hàng này vì nó không thuộc về tài khoản của bạn. 🔒"

        status_raw = order.get("TrangThai") or order.get("trangthai") or "Unknown"
        status_display = STATUS_MAP.get(status_raw, status_raw)
        order_code = f"#{str(order_id).zfill(9)}"

        total = int(order.get("TongTien") or order.get("tongtien") or 0)
        total_str = f"{total:,}₫".replace(",", ".")
        address = order.get("DiaChiGiao") or order.get("diachigiao") or "Tại cửa hàng"
        created = order.get("NgayTao") or order.get("ngaytao") or "N/A"
        
        items_text = ""
        if order.get("items"):
            items_text = "\n**Các món trong đơn:**\n"
            for item in order["items"]:
                sl = item.get("SoLuong") or item.get("soluong") or 1
                ten = item.get("TenSP") or item.get("tensp") or "Món"
                items_text += f"- {sl}x {ten}\n"

        return (
            f"📦 **Đơn hàng {order_code}**\n"
            f"• Trạng thái: {status_display}\n"
            f"• Tổng tiền: {total_str}\n"
            f"• Địa chỉ giao: {address}\n"
            f"• Ngày đặt: {created}\n"
            f"{items_text}\n"
            "Bạn có câu hỏi gì thêm về đơn hàng không? 😊"
        )

    except Exception as e:
        return f"Mình gặp lỗi khi tra cứu đơn hàng: {str(e)}. Vui lòng thử lại sau nhé!"
