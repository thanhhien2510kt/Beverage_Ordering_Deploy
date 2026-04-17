"""
MeowTea Fresh AI Chatbot - LangChain Agent with Fallbacks
"""
from typing import Optional
from langchain_groq import ChatGroq
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory
from langchain_core.runnables.history import RunnableWithMessageHistory
from langchain_core.messages import HumanMessage, AIMessage
import json
import os

from config import GROQ_API_KEY, GROQ_MODEL, GOOGLE_API_KEY, OPENROUTER_API_KEY, OPENROUTER_MODEL
from tools.product_tool import search_products_tool
from tools.get_product_details_tool import get_product_details_tool
from tools.cart_tool import add_to_cart_tool
from tools.order_tool import get_order_status_tool, get_recent_orders_tool
from tools.search_store_tool import search_store_tool

SYSTEM_PROMPT = """Bạn là MeowBot 🐱 — trợ lý AI của thương hiệu trà sữa MeowTea Fresh.

NGUYÊN TẮC:
- Xưng "mình", gọi khách là "bạn" 🍵🧋✨.
- Gọi món: Dùng search_products_tool. Nếu chỉ nói "Xem menu", hãy gợi ý chọn danh mục (Cà phê, Trà sữa, Yogurt).
- Đặt hàng: Kiểm tra user_context. Nếu chưa login, yêu cầu login.

{user_context}
"""

def _build_user_context(user_id: Optional[int], user_role: Optional[str]) -> str:
    if not user_id: return "Chưa đăng nhập."
    return f"Đã đăng nhập (ID: {user_id})."

class MeowTeaAgent:
    def __init__(self, session_id: str, user_id: Optional[int], user_role: Optional[str]):
        self.session_id = session_id
        self.user_id = user_id
        self.user_role = user_role
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

        # LLM —  OpenRouter (with Fallbacks), Gemini, or Groq
        if OPENROUTER_API_KEY:
            from langchain_openai import ChatOpenAI
            
            # Main model (GPT-4o)
            main_llm = ChatOpenAI(
                model_name=OPENROUTER_MODEL,
                openai_api_key=OPENROUTER_API_KEY,
                openai_api_base="https://openrouter.ai/api/v1",
                temperature=0.4,
                max_tokens=1000,
            )
            
            # Fallback 1: DeepSeek R1 (Free)
            fb1 = ChatOpenAI(
                model_name="deepseek/deepseek-r1:free",
                openai_api_key=OPENROUTER_API_KEY,
                openai_api_base="https://openrouter.ai/api/v1",
                temperature=0.4,
                max_tokens=2000,
            )
            
            # Fallback 2: Llama 3.3 70B (Free)
            fb2 = ChatOpenAI(
                model_name="meta-llama/llama-3.3-70b-instruct:free",
                openai_api_key=OPENROUTER_API_KEY,
                openai_api_base="https://openrouter.ai/api/v1",
                temperature=0.4,
                max_tokens=2000,
            )
            
            llm = main_llm.with_fallbacks([fb1, fb2])
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
                api_key=GROQ_API_KEY,
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

        # Agent executor
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
        if history and not self._history.messages:
            for role, content in history[-6:]:
                if role == "user":
                    self._history.add_user_message(content)
                elif role == "assistant":
                    self._history.add_ai_message(content)

        try:
            result = await self.executor.ainvoke({
                "input": message,
                "chat_history": self._history.messages[-6:]
            })
            reply = result.get("output", "Lỗi xử lý. Thử lại nhé!")
            
            import re
            reply = re.sub(r'\(function=[a-zA-Z0-9_]+>.*?\}[\)]?', '', reply, flags=re.DOTALL)
            reply = re.sub(r'</?function>', '', reply)
            reply = reply.strip()

            intermediate_steps = result.get("intermediate_steps", [])
            actions = []
            for action, observation in intermediate_steps:
                obs_actions = self._extract_actions_from_text(str(observation))
                actions.extend(obs_actions)
                
            reply, reply_actions = self._extract_actions_and_clean_text(reply)
            actions.extend(reply_actions)
            
            seen_actions = []
            unique_actions = []
            for a in actions:
                a_str = json.dumps(a)
                if a_str not in seen_actions:
                    seen_actions.append(a_str)
                    unique_actions.append(a)

            self._history.add_user_message(message)
            self._history.add_ai_message(reply)
            return reply, unique_actions
        except Exception as e:
            err_str = str(e)
            return f"❌ Sự cố (402/Token): {err_str}", []

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
                if char == '{': depth += 1
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
                except Exception: pass
        return actions

    def _extract_actions_and_clean_text(self, reply: str) -> tuple[str, list[dict]]:
        import re
        import json
        actions = []
        match = re.search(r'__ACTION_PAYLOAD__:\s*(\{.*)', reply, flags=re.DOTALL)
        if match:
            json_str = match.group(1)
            depth = 0
            end_idx = -1
            for i, char in enumerate(json_str):
                if char == '{': depth += 1
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
                    full_match = str(match.group(0)) if match.group(0) else ""
                    if full_match:
                        text_to_remove = full_match[:full_match.find(valid_json)] + valid_json
                        reply = reply.replace(text_to_remove, "").strip()
                except Exception: pass
        return reply, actions
