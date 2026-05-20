"""
MeowTea Fresh AI Chatbot - LangChain Agent with Gemini 2.0 Flash
"""
from typing import Optional
from langchain_groq import ChatGroq
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_openai import ChatOpenAI
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory
from langchain_core.runnables.history import RunnableWithMessageHistory
from langchain_core.messages import HumanMessage, AIMessage
import json

from config import GROQ_API_KEY, GROQ_MODEL, GOOGLE_API_KEY, BEEKNOEE_API_KEY, BEEKNOEE_BASE_URL, BEEKNOEE_MODEL
from tools.product_tool import search_products_tool
from tools.get_product_details_tool import get_product_details_tool
from tools.cart_tool import add_to_cart_tool
from tools.order_tool import get_order_status_tool, get_recent_orders_tool
from tools.search_store_tool import search_store_tool

SYSTEM_PROMPT = """Bạn là **MeowBot** 🐱 — AI của **MeowTea Fresh**.

**QUAN TRỌNG:**
1. Gọi tool **1 LẦN** cho mỗi yêu cầu.
2. KHÔNG bịa thông tin. BÊ NGUYÊN VĂN kết quả tool.
3. KHI GỌI TOOL, CHỈ TRẢ VỀ TOOL CALL, KHÔNG KÈM TEXT.
4. Luôn gọi khách là "bạn", xưng "mình".

**Hành động:**
- **Menu/Tìm món**: Dùng `search_products_tool`.
- **Đặt hàng**: Phải login. Nếu đã login: `get_product_details_tool` -> hỏi chọn -> `add_to_cart_tool`.
- **Tra đơn hàng**: Có mã -> `get_order_status_tool`. Không mã -> `get_recent_orders_tool`.
- **Tìm cửa hàng**: Cần Quận và Tỉnh -> `search_store_tool`.

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
            get_recent_orders_tool,
            search_store_tool,
        ]

        # Prompt
        user_context = _build_user_context(user_id, user_role)
        prompt = ChatPromptTemplate.from_messages([
            ("system", SYSTEM_PROMPT.format(user_context=user_context)),
            MessagesPlaceholder(variable_name="chat_history"),
            ("human", "{input}"),
            MessagesPlaceholder(variable_name="agent_scratchpad"),
        ])

        # Build executor cho mỗi LLM — fallback cần bind tools riêng để tool calling hoạt động
        def _make_executor(llm):
            agent = create_tool_calling_agent(llm, tools, prompt)
            return AgentExecutor(
                agent=agent,
                tools=tools,
                verbose=True,
                handle_parsing_errors=True,
                max_iterations=8,
                return_intermediate_steps=True,
            )

        groq_llm = ChatGroq(model=GROQ_MODEL, groq_api_key=GROQ_API_KEY, temperature=0.4)
        self._executors = [_make_executor(groq_llm)]  # primary

        if GOOGLE_API_KEY:
            gemini_llm = ChatGoogleGenerativeAI(
                model="gemini-2.0-flash-lite",
                google_api_key=GOOGLE_API_KEY,
                temperature=0.4,
                max_retries=0,
            )
            self._executors.append(_make_executor(gemini_llm))

        if BEEKNOEE_API_KEY:
            beeknoee_llm = ChatOpenAI(
                model=BEEKNOEE_MODEL,
                base_url=BEEKNOEE_BASE_URL,
                api_key=BEEKNOEE_API_KEY,
                temperature=0.4,
            )
            self._executors.append(_make_executor(beeknoee_llm))

        self.executor = self._executors[0]  # default, sẽ rotate khi fallback

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

        import re, asyncio
        _last_err = ""

        # Thử từng executor (Groq → Gemini → Beeknoee), mỗi executor retry 1 lần nếu failed_generation
        for _exec_idx, _executor in enumerate(self._executors):
            for _attempt in range(2):  # 2 attempts per executor
                try:
                    result = await _executor.ainvoke({
                        "input": message,
                        "chat_history": self._history.messages[-20:]
                    })
                    reply = result.get("output", "Mình gặp lỗi khi xử lý yêu cầu. Vui lòng thử lại nhé!")

                    # Ẩn tool call syntax rò rỉ
                    reply = re.sub(r'\(function=[a-zA-Z0-9_]+>.*?\}[\)]?', '', reply, flags=re.DOTALL)
                    reply = re.sub(r'</?function>', '', reply)
                    reply = reply.strip()

                    # Fallback: dùng tool output nếu reply không chứa data sản phẩm thực
                    _has_product_data = "Mã:" in reply or "Phân loại:" in reply or "Mình tìm được" in reply or "Top món" in reply
                    intermediate_steps = result.get("intermediate_steps", [])
                    if intermediate_steps and not _has_product_data:
                        for action, observation in intermediate_steps:
                            tool_name = getattr(action, "tool", "") or getattr(action, "name", "")
                            obs_str = str(observation)
                            if tool_name == "search_products_tool" and len(obs_str) > 100:
                                reply = obs_str
                                break

                    if not reply:
                        reply = "Đợi mình một xíu để tìm thông tin cho bạn nhé... 🐱"

                    # Trích xuất actions
                    actions = []
                    for action, observation in intermediate_steps:
                        actions.extend(self._extract_actions_from_text(str(observation)))
                    reply, reply_actions = self._extract_actions_and_clean_text(reply)
                    actions.extend(reply_actions)

                    # Xóa trùng lặp
                    seen, unique_actions = [], []
                    for a in actions:
                        a_str = json.dumps(a)
                        if a_str not in seen:
                            seen.append(a_str)
                            unique_actions.append(a)

                    # Persist this turn
                    self._history.add_user_message(message)
                    self._history.add_ai_message(reply)
                    return reply, unique_actions

                except Exception as e:
                    _last_err = str(e)
                    _is_failed_gen = "failed_generation" in _last_err or "Failed to call a function" in _last_err
                    _is_rate_limit = "429" in _last_err or "rate limit" in _last_err.lower() or "quota" in _last_err.lower() or "ResourceExhausted" in _last_err

                    if _is_failed_gen and _attempt == 0:
                        await asyncio.sleep(0.5)
                        continue  # retry cùng executor
                    break  # thử executor tiếp theo

        # Tất cả executors đều fail
        if "failed_generation" in _last_err or "Failed to call a function" in _last_err:
            return (
                "Xin lỗi bạn, mình hiểu ý bạn rồi nhưng hệ thống đang bận xíu 🐱 "
                "Bạn thử hỏi lại theo cách khác nhé! Ví dụ: \"Cho mình xem các loại cà phê\" "
                "hoặc \"Tìm trà sữa cho mình\" ✨"
            ), []
        return f"Xin lỗi bạn, mình đang gặp sự cố kỹ thuật 🙏 ({_last_err})", []

    def _extract_actions_from_text(self, text: str) -> list[dict]:
        import re
        import json
        actions = []
        match = re.search(r'__ACTION_PAYLOAD__:\s*(\{.*)', text, flags=re.DOTALL)
        if match:
            json_str = match.group(1)
            depth = 0
            end_idx = -1
            for i, char in enumerate(json_str):
                if char == '{':
                    depth += 1
                elif char == '}':
                    depth -= 1
                    if depth == 0:
                        end_idx = i
                        break
            if end_idx != -1:
                valid_json = json_str[:end_idx+1]
                try:
                    payload = json.loads(valid_json)
                    if payload.get("__action"):
                        actions.append({"type": payload["__action"], "data": payload})
                except Exception:
                    pass
        return actions

    def _extract_actions_and_clean_text(self, reply: str) -> tuple[str, list[dict]]:
        import re
        import json
        actions = []
        
        # Tìm `__ACTION_PAYLOAD__:`
        match = re.search(r'__ACTION_PAYLOAD__:\s*(\{.*)', reply, flags=re.DOTALL)
        if match:
            json_str = match.group(1)
            depth = 0
            end_idx = -1
            # Tìm dấu ngoặc đóng cuối cùng của JSON object
            for i, char in enumerate(json_str):
                if char == '{':
                    depth += 1
                elif char == '}':
                    depth -= 1
                    if depth == 0:
                        end_idx = i
                        break
            
            if end_idx != -1:
                valid_json = json_str[:end_idx+1]
                try:
                    payload = json.loads(valid_json)
                    if payload.get("__action"):
                        actions.append({"type": payload["__action"], "data": payload})
                    
                    # Xóa phần payload khỏi reply, giữ lại những text LLM sinh ra sau đó
                    full_match = str(match.group(0)) if match.group(0) else ""
                    if full_match:
                        text_to_remove = full_match[:full_match.find(valid_json)] + valid_json
                        reply = reply.replace(text_to_remove, "").strip()
                except Exception:
                    pass
                    
        return reply, actions
