"""
MeowTea Fresh AI Chatbot - FastAPI Entry Point
"""
import uvicorn
from fastapi import FastAPI, HTTPException, Header
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional
import uuid

from config import FASTAPI_HOST, FASTAPI_PORT, FASTAPI_RELOAD, CHATBOT_SECRET_KEY, validate_config
from agent import MeowTeaAgent

# Validate config on startup
validate_config()

app = FastAPI(
    title="MeowTea Fresh Chatbot API",
    description="AI Chatbot microservice for MeowTea Fresh ordering system",
    version="1.0.0"
)

# CORS - cho phép PHP proxy và browser gọi sang
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost", "http://127.0.0.1"],
    allow_credentials=True,
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)

# === Request / Response Models ===

class ChatMessage(BaseModel):
    role: str           # "user" | "assistant"
    content: str

class ChatRequest(BaseModel):
    message: str
    session_id: Optional[str] = None
    user_id: Optional[int] = None
    user_role: Optional[str] = None        # "Admin" | "Staff" | "Customer" | None
    history: Optional[list[ChatMessage]] = []

class ChatResponse(BaseModel):
    reply: str
    session_id: str
    actions: Optional[list[dict]] = []    # e.g. [{"type": "add_to_cart", "data": {...}}]


# === Agent pool (simple in-memory, 1 agent per session) ===
_agents: dict[str, MeowTeaAgent] = {}

def get_agent(session_id: str, user_id: Optional[int], user_role: Optional[str]) -> MeowTeaAgent:
    if session_id not in _agents:
        _agents[session_id] = MeowTeaAgent(
            session_id=session_id,
            user_id=user_id,
            user_role=user_role
        )
    return _agents[session_id]


# === Endpoints ===

@app.get("/health")
async def health():
    return {"status": "ok", "service": "MeowTea Chatbot"}


@app.post("/chat", response_model=ChatResponse)
async def chat(
    request: ChatRequest,
    x_chatbot_secret: Optional[str] = Header(default=None)
):
    # Verify secret từ PHP proxy (bỏ qua nếu chạy local dev)
    if CHATBOT_SECRET_KEY != "changeme" and x_chatbot_secret != CHATBOT_SECRET_KEY:
        raise HTTPException(status_code=403, detail="Unauthorized")

    session_id = request.session_id or str(uuid.uuid4())

    agent = get_agent(
        session_id=session_id,
        user_id=request.user_id,
        user_role=request.user_role
    )

    reply, actions = await agent.chat(
        message=request.message,
        history=[(m.role, m.content) for m in (request.history or [])]
    )

    return ChatResponse(
        reply=reply,
        session_id=session_id,
        actions=actions
    )


if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host=FASTAPI_HOST,
        port=FASTAPI_PORT,
        reload=False   # Tắt hot-reload để tránh crash pydantic/langchain conflict
    )
