"""
MeowTea Fresh AI Chatbot - LangChain Agent with Gemini 2.0 Flash
"""
from typing import Optional
from langchain_groq import ChatGroq
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory
from langchain_core.runnables.history import RunnableWithMessageHistory
from langchain_core.messages import HumanMessage, AIMessage

from config import GROQ_API_KEY, GROQ_MODEL
from tools.product_tool import search_products_tool
from tools.get_product_details_tool import get_product_details_tool
from tools.cart_tool import add_to_cart_tool
from tools.order_tool import get_order_status_tool
from tools.complaint_tool import submit_complaint_tool

SYSTEM_PROMPT = """Bạn là **MeowBot** 🐱 — trợ lý AI siêu dễ thương và thông minh của thương hiệu trà sữa **MeowTea Fresh**.

Mục tiêu chính:
1. Giao tiếp siêu ngọt ngào, xì teen, xưng "mình/MeowBot" và "bạn/cậu" (hoặc dạ/vâng). Dùng nhiều emoji 🍵🧋✨🐱.
2. **Tư vấn & Tìm kiếm**: Khi khách hỏi giá/menu, phải dùng `search_products_tool`. Tuyệt đối không bịa giá.
3. **Quy trình Đặt hàng (BẮT BUỘC TUÂN THEO)**:
    - **Bước 1: Kiểm tra Login**: Trước khi thêm bất cứ món gì vào giỏ hàng, hãy nhìn vào `user_context`. Nếu là "Chưa đăng nhập", hãy lịch sự yêu cầu khách đăng nhập/đăng ký trước khi đặt món. KHÔNG gọi tool đặt hàng nếu chưa login.
    - **Bước 2: Lấy chi tiết món**: Nếu đã login, hãy dùng `get_product_details_tool` để xem món đó có những tùy chọn nào (Size, Đá, Đường, Topping).
    - **Bước 3: Hỏi thông tin còn thiếu**: Dựa vào kết quả từ `get_product_details_tool`, hãy hỏi khách chọn Size gì? Lượng đá/đường thế nào? Có thêm topping không? 
    - **Bước 4: Gọi Cart Tool**: Chỉ gọi `add_to_cart_tool` khi khách đã xác nhận đầy đủ các thông tin trên.

4. **Tra cứu & Khiếu nại**: Hỗ trợ tra cứu đơn hàng (cần mã đơn) và tiếp nhận khiếu nại thân thiện.

Bạn hãy sử dụng các công cụ hệ thống thay vì in mã code. 

**Ví dụ mẫu:**
Khách: "Cho mình 1 trà sữa flan"
MeowBot (Nếu chưa login): "Dạ MeowBot rất sẵn lòng ạ! Nhưng bạn ơi, bạn vui lòng đăng nhập vào tài khoản để mình có thể ghi nhận đơn hàng cho bạn nhé! ✨"
MeowBot (Nếu đã login): "Dạ món Trà sữa Flan có các size M, L và các mức đường đá ạ. Bạn muốn dùng size nào và lượng đá đường ra sao nè? 🧋"

{user_context}
"""

def _build_user_context(user_id: Optional[int], user_role: Optional[str]) -> str:
    if not user_id:
        return "Khách hiện tại: Chưa đăng nhập (khách vãng lai)."
    role_map = {"Admin": "Quản trị viên", "Staff": "Nhân viên", "Customer": "Khách hàng"}
    role_str = role_map.get(user_role or "", user_role or "Không rõ")
    return f"Khách hiện tại: Đã đăng nhập | User ID: {user_id} | Vai trò: {role_str}."


class MeowTeaAgent:
    def __init__(self, session_id: str, user_id: Optional[int], user_role: Optional[str]):
        self.session_id = session_id
        self.user_id = user_id
        self.user_role = user_role
        # In-memory chat history (keep last 20 messages = 10 turns)
        self._history = ChatMessageHistory()

        # Tools
        tools = [
            search_products_tool,
            get_product_details_tool,
            add_to_cart_tool,
            get_order_status_tool,
            submit_complaint_tool,
        ]

        # LLM — Groq (Llama 3.3, free tier, no quota issues)
        llm = ChatGroq(
            model=GROQ_MODEL,
            groq_api_key=GROQ_API_KEY,
            temperature=0.4,
        )

        # Prompt
        user_context = _build_user_context(user_id, user_role)
        prompt = ChatPromptTemplate.from_messages([
            ("system", SYSTEM_PROMPT.format(user_context=user_context)),
            MessagesPlaceholder(variable_name="chat_history"),
            ("human", "{input}"),
            MessagesPlaceholder(variable_name="agent_scratchpad"),
        ])

        # Agent executor (no built-in memory — managed manually)
        agent = create_tool_calling_agent(llm, tools, prompt)
        self.executor = AgentExecutor(
            agent=agent,
            tools=tools,
            verbose=True,
            handle_parsing_errors=True,
            max_iterations=5,
        )

    async def chat(self, message: str, history: list[tuple[str, str]]) -> tuple[str, list[dict]]:
        """
        Xử lý tin nhắn, trả về (reply_text, actions_list).
        """
        # Seed history from frontend if this is a fresh agent instance
        if history and not self._history.messages:
            for role, content in history[-10:]:
                if role == "user":
                    self._history.add_user_message(content)
                elif role == "assistant":
                    self._history.add_ai_message(content)

        try:
            result = await self.executor.ainvoke({
                "input": message,
                "chat_history": self._history.messages[-20:]
            })
            reply = result.get("output", "Mình gặp lỗi khi xử lý yêu cầu. Vui lòng thử lại nhé!")

            # Ẩn các cú pháp gọi tool thô bị rò rỉ (như `(function=search_products_tool>...` hoặc `search_products_tool{...}`)
            import re
            
            # Pattern 1: (function=tool_name>{...}) hoặc <function>
            reply = re.sub(r'\(function=[a-zA-Z0-9_]+>.*?\}[\)]?', '', reply, flags=re.DOTALL)
            reply = re.sub(r'</?function>', '', reply)
            
            # Pattern 2: search_products_tool{...}
            reply = re.sub(r'[a-zA-Z0-9_]+_tool\s*\{.*?\}', '', reply, flags=re.DOTALL)
            
            reply = reply.strip()
            
            if not reply:
                reply = "Đợi mình một xíu để tìm thông tin cho bạn nhé... 🐱"

            # Persist this turn
            self._history.add_user_message(message)
            self._history.add_ai_message(reply)

            actions = self._extract_actions(reply)
            return reply, actions
        except Exception as e:
            return f"Xin lỗi bạn, mình đang gặp sự cố kỹ thuật 🙏 ({str(e)})", []

    def _extract_actions(self, reply: str) -> list[dict]:
        return []
