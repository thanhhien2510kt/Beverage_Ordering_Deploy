"""
MeowTea Fresh AI Chatbot - Gemini + Groq Stable Version
"""
print(">>> MeowBot is starting... Initializing libraries...")

import os
import json
from typing import Optional
from langchain_google_genai import ChatGoogleGenerativeAI
from langchain_groq import ChatGroq
from langchain.agents import AgentExecutor, create_tool_calling_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory

# Import tools
from tools.product_tool import search_products_tool
from tools.get_product_details_tool import get_product_details_tool
from tools.cart_tool import add_to_cart_tool
from tools.order_tool import get_order_status_tool, get_recent_orders_tool
from tools.search_store_tool import search_store_tool

# Import config
from config import GOOGLE_API_KEY, GROQ_API_KEY, GROQ_MODEL

print(">>> Libraries loaded. Setting up Agent...")

SYSTEM_PROMPT = """Bạn là MeowBot 🐱 [Alpha] — trợ lý AI của tiệm trà sữa MeowTea Fresh.
QUY TẮC:
- Xưng "mình", gọi khách là "bạn" 🍵✨.
- Chỉ khi khách muốn "Mua/Đặt" mới dùng get_product_details_tool.
- Nếu khách chỉ hỏi món, dùng search_products_tool để giới thiệu.

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

        # Primary: Google Gemini 2.0 Flash (15 RPM, 1500 req/ngày — rất ổn định)
        # Fallback: Groq llama-3.3-70b (30 RPM, 1000 req/ngày)
        p_llm = ChatGoogleGenerativeAI(
            model="gemini-2.0-flash",
            google_api_key=GOOGLE_API_KEY,
            temperature=0.4,
            convert_system_message_to_human=False,
        )
        f_llm = ChatGroq(model=GROQ_MODEL, api_key=GROQ_API_KEY, temperature=0.4)
        self.llm = p_llm.with_fallbacks([f_llm])

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
            reply = str(result.get("output", "Mình đây! 🐱"))
            
            actions = []
            it_steps = result.get("intermediate_steps", [])
            for _, observation in it_steps:
                actions.extend(self._extract_payload(str(observation)))
                
            reply, r_actions = self._extract_payload_from_text(reply)
            actions.extend(r_actions)
            
            self._history.add_user_message(message)
            self._history.add_ai_message(reply)
            return reply, actions
        except Exception as e:
            return f"Lỗi: {str(e)}", []

    def _extract_payload(self, text: str) -> list[dict]:
        import re
        actions = []
        match = re.search(r'__ACTION_PAYLOAD__:\s*(\{.*)', text, flags=re.DOTALL)
        if match:
            s_json = match.group(1); d = 0
            for i, c in enumerate(s_json):
                if c == '{': d += 1
                elif c == '}':
                    d -= 1
                    if d == 0:
                        try:
                            p = json.loads(s_json[:i+1])
                            if p.get("__action"): actions.append({"type": p["__action"], "data": p})
                        except: pass
                        break
        return actions

    def _extract_payload_from_text(self, reply: str) -> tuple[str, list[dict]]:
        import re
        actions = self._extract_payload(reply)
        reply = re.sub(r'__ACTION_PAYLOAD__:\s*\{.*?\}', '', reply, flags=re.DOTALL).strip()
        return reply, actions
