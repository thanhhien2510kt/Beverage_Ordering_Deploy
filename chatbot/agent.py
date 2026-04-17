"""
MeowTea Fresh AI Chatbot - Multi-model Fallback Loop
"""
from typing import Optional
from langchain_openai import ChatOpenAI
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory
import json
import os

from config import OPENROUTER_API_KEY

SYSTEM_PROMPT = """Bạn là MeowBot 🐱 — trợ lý AI của tiệm trà sữa MeowTea Fresh.
Xưng "mình", gọi khách là "bạn". 
- Xem menu: search_products_tool. Nếu chỉ nói "Xem menu", gợi ý Cà phê, Trà sữa, Yogurt.
- Đặt món: Kiểm tra user_context. Nếu chưa login, yêu cầu login. Nếu rồi, dùng get_product_details_tool lấy option.

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

        # Biệt đội model miễn phí dự phòng
        free_models = [
            "google/gemma-3-27b-it:free",
            "mistralai/mistral-small-24b-instruct-2501:free",
            "qwen/qwen-2.5-72b-instruct:free",
            "nousresearch/hermes-3-llama-3.1-405b:free",
            "meta-llama/llama-3.3-70b-instruct:free"
        ]

        def create_llm(model_name):
            return ChatOpenAI(
                model_name=model_name,
                openai_api_key=OPENROUTER_API_KEY,
                openai_api_base="https://openrouter.ai/api/v1",
                temperature=0.6,
                max_tokens=1500,
            )

        main_llm = create_llm(free_models[0])
        fallbacks = [create_llm(m) for m in free_models[1:]]
        
        # LangChain with_fallbacks handle 429 and 402 automatically
        self.llm = main_llm.with_fallbacks(fallbacks)

        from tools.product_tool import search_products_tool
        from tools.get_product_details_tool import get_product_details_tool
        from tools.cart_tool import add_to_cart_tool
        from tools.order_tool import get_order_status_tool, get_recent_orders_tool
        from tools.search_store_tool import search_store_tool

        self.tools = [search_products_tool, get_product_details_tool, add_to_cart_tool, 
                     get_order_status_tool, get_recent_orders_tool, search_store_tool]

        self.prompt = ChatPromptTemplate.from_messages([
            ("system", SYSTEM_PROMPT.format(user_context=_build_user_context(user_id, user_role))),
            MessagesPlaceholder(variable_name="chat_history"),
            ("human", "{input}"),
            MessagesPlaceholder(variable_name="agent_scratchpad"),
        ])

        agent = create_tool_calling_agent(self.llm, self.tools, self.prompt)
        self.executor = AgentExecutor(agent=agent, tools=self.tools, verbose=True, handle_parsing_errors=True)

    async def chat(self, message: str, history: list[tuple[str, str]]) -> tuple[str, list[dict]]:
        if history and not self._history.messages:
            for role, content in history[-6:]:
                if role == "user": self._history.add_user_message(content)
                else: self._history.add_ai_message(content)

        try:
            result = await self.executor.ainvoke({"input": message, "chat_history": self._history.messages[-6:]})
            reply = str(result.get("output", "Lỗi rồi, thử lại nhé!"))
            
            actions = []
            intermediate_steps = result.get("intermediate_steps", [])
            for action_obj, observation in intermediate_steps:
                obs_actions = self._extract_actions(str(observation))
                actions.extend(obs_actions)
                
            reply, reply_actions = self._extract_actions_from_reply(reply)
            actions.extend(reply_actions)
            
            self._history.add_user_message(message)
            self._history.add_ai_message(reply)
            return reply, actions
        except Exception as e:
            return f"Hệ thống đang rất bận, bạn thử gõ lại câu vừa rồi nhé! 🙏 ({str(e)})", []

    def _extract_actions(self, text: str) -> list[dict]:
        import re
        actions = []
        match = re.search(r'__ACTION_PAYLOAD__:\s*(\{.*)', text, flags=re.DOTALL)
        if match:
            try:
                s = match.group(1)
                d = 0
                for i, c in enumerate(s):
                    if c == '{': d += 1
                    elif c == '}':
                        d -= 1
                        if d == 0:
                            payload = json.loads(s[:i+1])
                            if payload.get("__action"):
                                actions.append({"type": payload["__action"], "data": payload})
                            break
            except: pass
        return actions

    def _extract_actions_from_reply(self, reply: str) -> tuple[str, list[dict]]:
        actions = self._extract_actions(reply)
        import re
        reply = re.sub(r'__ACTION_PAYLOAD__:\s*\{.*?\}', '', reply, flags=re.DOTALL).strip()
        return reply, actions
