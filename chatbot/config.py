import os
from dotenv import load_dotenv

load_dotenv()

# --- Gemini ---
GOOGLE_API_KEY: str = os.getenv("GOOGLE_API_KEY", "")

# --- PHP Backend ---
PHP_BASE_URL: str = os.getenv("PHP_BASE_URL", "http://localhost/beverage_ordering_16_03")

# --- FastAPI ---
FASTAPI_HOST: str = os.getenv("FASTAPI_HOST", "0.0.0.0")
FASTAPI_PORT: int = int(os.getenv("FASTAPI_PORT", "8000"))
FASTAPI_RELOAD: bool = os.getenv("FASTAPI_RELOAD", "true").lower() == "true"

# --- Security ---
CHATBOT_SECRET_KEY: str = os.getenv("CHATBOT_SECRET_KEY", "changeme")

# --- AI Chat Model ---
# Ưu tiên dùng OPENROUTER_API_KEY -> GEMINI_API_KEY -> GROQ_API_KEY
OPENROUTER_API_KEY: str = os.getenv("OPENROUTER_API_KEY", "")
OPENROUTER_MODEL: str = os.getenv("OPENROUTER_MODEL", "meta-llama/llama-3.3-70b-instruct:free")

GOOGLE_API_KEY: str = os.getenv("GEMINI_API_KEY", os.getenv("GOOGLE_API_KEY", ""))

GROQ_API_KEY: str = os.getenv("GROQ_API_KEY", "")
GROQ_MODEL: str = os.getenv("GROQ_MODEL", "llama-3.3-70b-versatile")

# Validation
def validate_config():
    missing = []
    if not GOOGLE_API_KEY:
        missing.append("GOOGLE_API_KEY hoặc GEMINI_API_KEY (Gemini là primary model)")
    if not GROQ_API_KEY:
        print("[WARNING] Thiếu GROQ_API_KEY — sẽ không có fallback khi Gemini quá tải.")
    if missing:
        raise EnvironmentError(
            f"Thiếu biến môi trường bắt buộc: {', '.join(missing)}. "
            "Vui lòng thiết lập GOOGLE_API_KEY trong Render environment."
        )
