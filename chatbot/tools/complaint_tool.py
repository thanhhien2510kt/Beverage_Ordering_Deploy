"""
Tool: Tiếp nhận và ghi nhận khiếu nại của khách hàng
Gọi PHP API /api/chatbot/complaint.php.
"""
import httpx
from langchain_core.tools import tool
from config import PHP_BASE_URL, CHATBOT_SECRET_KEY

COMPLAINT_CATEGORIES = {
    "sai_don": "wrong_order",
    "giao_hang": "late_delivery",
    "chat_luong": "quality",
    "thanh_toan": "payment",
    "khac": "other",
}


@tool
def submit_complaint_tool(
    content: str,
    user_id: int = 0,
    order_id: int = 0,
    category: str = "other"
) -> str:
    """
    Tiếp nhận khiếu nại, phản ánh của khách hàng về đơn hàng hoặc sản phẩm.
    Dùng khi khách phàn nàn, báo lỗi, hoặc muốn khiếu nại.

    Args:
        content: Nội dung khiếu nại của khách (càng chi tiết càng tốt)
        user_id: ID khách hàng (0 nếu chưa đăng nhập)
        order_id: Mã đơn hàng liên quan (0 nếu không liên quan đến đơn cụ thể)
        category: Loại khiếu nại: 'wrong_order' (sai đơn), 'late_delivery' (giao chậm), 
                  'quality' (chất lượng), 'payment' (thanh toán), 'other' (khác)

    Returns:
        Xác nhận đã tiếp nhận khiếu nại kèm mã ticket
    """
    try:
        # Normalize category
        cat = COMPLAINT_CATEGORIES.get(category, category)
        if cat not in ["wrong_order", "late_delivery", "quality", "payment", "other"]:
            cat = "other"

        # Call PHP API
        payload = {
            "content": content,
            "category": cat,
            "user_id": user_id,
            "order_id": order_id
        }

        r = httpx.post(
            f"{PHP_BASE_URL}/api/chatbot/complaint.php",
            json=payload,
            headers={"X-Chatbot-Secret": CHATBOT_SECRET_KEY},
            timeout=5.0
        )
        data = r.json()

        if data.get("success"):
            complaint_id = data.get("complaint_id", "N/A")
            return (
                f"🙏 Mình đã tiếp nhận khiếu nại của bạn thành công!\n"
                f"• **Mã ticket**: #{complaint_id}\n"
                f"• **Nội dung**: {content[:100]}{'...' if len(content) > 100 else ''}\n"
                f"• **Trạng thái**: Đang chờ xử lý ⏳\n\n"
                "Đội ngũ MeowTea Fresh sẽ liên hệ with bạn trong vòng 24 giờ. "
                "Xin lỗi bạn vì sự bất tiện này! 😔"
            )
        else:
            raise Exception(data.get("message", "Không nhận được phản hồi từ hệ thống"))

    except Exception as e:
        return (
            f"Mình gặp lỗi khi ghi nhận khiếu nại: {str(e)}\n"
            "Bạn vui lòng liên hệ hotline MeowTea Fresh hoặc thử lại sau nhé!"
        )
