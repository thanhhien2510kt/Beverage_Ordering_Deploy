"""
Tool: Tiếp nhận và ghi nhận khiếu nại của khách hàng
Lưu trực tiếp vào Supabase bảng `complaints`.
"""
from langchain_core.tools import tool
from supabase import create_client, Client
from config import SUPABASE_URL, SUPABASE_SERVICE_KEY

supabase: Client = create_client(SUPABASE_URL, SUPABASE_SERVICE_KEY)

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

        # Insert into Supabase
        payload = {
            "content": content,
            "category": cat,
            "status": "pending",
        }
        if user_id:
            payload["user_id"] = user_id
        if order_id:
            payload["order_id"] = order_id

        result = supabase.table("complaints").insert(payload).execute()

        if result.data:
            complaint_id = result.data[0].get("id", "N/A")
            return (
                f"🙏 Mình đã tiếp nhận khiếu nại của bạn thành công!\n"
                f"• **Mã ticket**: #{complaint_id}\n"
                f"• **Nội dung**: {content[:100]}{'...' if len(content) > 100 else ''}\n"
                f"• **Trạng thái**: Đang chờ xử lý ⏳\n\n"
                "Đội ngũ MeowTea Fresh sẽ liên hệ với bạn trong vòng 24 giờ. "
                "Xin lỗi bạn vì sự bất tiện này! 😔"
            )
        else:
            raise Exception("Không nhận được response từ Supabase")

    except Exception as e:
        return (
            f"Mình gặp lỗi khi ghi nhận khiếu nại: {str(e)}\n"
            "Bạn vui lòng liên hệ hotline MeowTea Fresh hoặc thử lại sau nhé!"
        )
