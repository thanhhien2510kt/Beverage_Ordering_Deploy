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
import json

from config import GROQ_API_KEY, GROQ_MODEL, GOOGLE_API_KEY, OPENROUTER_API_KEY, OPENROUTER_MODEL
from tools.product_tool import search_products_tool
from tools.get_product_details_tool import get_product_details_tool
from tools.cart_tool import add_to_cart_tool
from tools.order_tool import get_order_status_tool, get_recent_orders_tool
from tools.search_store_tool import search_store_tool

SYSTEM_PROMPT = """You are MeowBot 🐱. 
If you need to use a tool, you MUST call it immediately without any introductory text, greetings, or emojis.
Only after receiving the tool output, you should provide a friendly response in Vietnamese with emojis.

Guidelines:
- Search products: search_products_tool
- Get item details: get_product_details_tool
- Orders/Cart: use respective tools.
- Address: search_store_tool (needs District and City).

Customer Context:
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

        # LLM — Choosen prioritization: Groq
        if GROQ_API_KEY:
            llm = ChatGroq(
                model=GROQ_MODEL,
                api_key=GROQ_API_KEY,
                temperature=0.4,
            )
        elif OPENROUTER_API_KEY:
            from langchain_openai import ChatOpenAI
            llm = ChatOpenAI(
                model_name=OPENROUTER_MODEL,
                openai_api_key=OPENROUTER_API_KEY,
                openai_api_base="https://openrouter.ai/api/v1",
                temperature=0.4,
            )
        elif GOOGLE_API_KEY:
            from langchain_google_genai import ChatGoogleGenerativeAI
            llm = ChatGoogleGenerativeAI(
                model="gemini-2.0-flash",
                google_api_key=GOOGLE_API_KEY,
                temperature=0.4,
            )
        else:
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
            max_iterations=8,
            return_intermediate_steps=True,
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

            # Ẩn tool call syntax rò rỉ
            import re
            reply = re.sub(r'\(function=[a-zA-Z0-9_]+>.*?\}[\)]?', '', reply, flags=re.DOTALL)
            reply = re.sub(r'</?function>', '', reply)
            reply = reply.strip()

            # Fallback: dùng tool output nếu reply không chứa data sản phẩm thực
            # (LLM hay tóm tắt, nhưng product list thật sẽ luôn có "Mã:" hoặc "Phân loại:")
            _has_product_data = "Mã:" in reply or "Phân loại:" in reply or "Mình tìm được" in reply
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

            # Trích xuất payload từ CẢ tool outputs GỐC và LLM reply
            actions = []
            for action, observation in intermediate_steps:
                obs_actions = self._extract_actions_from_text(str(observation))
                actions.extend(obs_actions)
                
            reply, reply_actions = self._extract_actions_and_clean_text(reply)
            actions.extend(reply_actions)
            
            # Xóa trùng lặp (nếu LLM vô tình lặp lại payload)
            seen_actions = []
            unique_actions = []
            for a in actions:
                a_str = json.dumps(a)
                if a_str not in seen_actions:
                    seen_actions.append(a_str)
                    unique_actions.append(a)

            # Persist this turn
            self._history.add_user_message(message)
            self._history.add_ai_message(reply)
            
            return reply, unique_actions
        except Exception as e:
            err_str = str(e)
            # DEBUG: Hiển thị lỗi thật để đoán bệnh
            return f"❌ Lỗi thật sự: {err_str}", []

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
