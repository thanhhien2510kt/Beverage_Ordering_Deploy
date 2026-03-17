import os
from dotenv import load_dotenv

load_dotenv()

# --- Gemini ---
GOOGLE_API_KEY: str = os.getenv("GOOGLE_API_KEY", "")

# --- Supabase ---
SUPABASE_URL: str = os.getenv("SUPABASE_URL", "")
SUPABASE_SERVICE_KEY: str = os.getenv("SUPABASE_SERVICE_KEY", "")

# --- PHP Backend ---
PHP_BASE_URL: str = os.getenv("PHP_BASE_URL", "http://localhost/beverage_ordering_16_03")

# --- FastAPI ---
FASTAPI_HOST: str = os.getenv("FASTAPI_HOST", "0.0.0.0")
FASTAPI_PORT: int = int(os.getenv("FASTAPI_PORT", "8000"))
FASTAPI_RELOAD: bool = os.getenv("FASTAPI_RELOAD", "true").lower() == "true"

# --- MySQL (for embedding sync) ---
MYSQL_HOST: str = os.getenv("MYSQL_HOST", "localhost")
MYSQL_PORT: int = int(os.getenv("MYSQL_PORT", "3306"))
MYSQL_USER: str = os.getenv("MYSQL_USER", "root")
MYSQL_PASSWORD: str = os.getenv("MYSQL_PASSWORD", "")
MYSQL_DB: str = os.getenv("MYSQL_DB", "meowtea_schema")

# --- Security ---
CHATBOT_SECRET_KEY: str = os.getenv("CHATBOT_SECRET_KEY", "changeme")

# --- AI Chat Model (Groq - free, no quota issues) ---
GROQ_API_KEY: str = os.getenv("GROQ_API_KEY", "")
GROQ_MODEL: str = os.getenv("GROQ_MODEL", "llama-3.3-70b-versatile")

# --- Embedding (vẫn dùng Gemini cho embedding) ---
GEMINI_MODEL: str = "gemini-2.0-flash-lite"  # fallback nếu cần
EMBEDDING_MODEL: str = "models/gemini-embedding-001"
EMBEDDING_DIMENSION: int = 768

# Validation
def validate_config():
    missing = []
    if not GOOGLE_API_KEY:
        missing.append("GOOGLE_API_KEY")
    if not SUPABASE_URL:
        missing.append("SUPABASE_URL")
    if not SUPABASE_SERVICE_KEY:
        missing.append("SUPABASE_SERVICE_KEY")
    if missing:
        raise EnvironmentError(
            f"Thiếu biến môi trường bắt buộc: {', '.join(missing)}. "
            "Vui lòng copy .env.example thành .env và điền đầy đủ."
        )
