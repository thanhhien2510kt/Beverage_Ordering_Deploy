"""
Test tool calls locally — chạy: python test_tools.py
Dùng PHP_BASE_URL=https://meowteafresh.space nên không cần server local.
"""
import asyncio
import sys
import os

# Fix Windows terminal encoding
if sys.platform == "win32":
    sys.stdout.reconfigure(encoding="utf-8")
    os.environ.setdefault("PYTHONIOENCODING", "utf-8")

from dotenv import load_dotenv

load_dotenv()

from config import GROQ_API_KEY, GROQ_MODEL, PHP_BASE_URL
from agent import MeowTeaAgent

# ── màu terminal ──────────────────────────────────────────────────
GREEN  = "\033[92m"
YELLOW = "\033[93m"
RED    = "\033[91m"
CYAN   = "\033[96m"
RESET  = "\033[0m"

def ok(msg):  print(f"{GREEN}✓ {msg}{RESET}")
def err(msg): print(f"{RED}✗ {msg}{RESET}")
def info(msg):print(f"{CYAN}→ {msg}{RESET}")

# ── test cases ────────────────────────────────────────────────────
TESTS = [
    {
        "name": "Tìm cà phê",
        "message": "Cho mình xem các loại cà phê",
        "expect_keywords": ["Phân loại", "Mã", "phê"],
    },
    {
        "name": "Tìm trà sữa",
        "message": "có trà sữa nào không",
        "expect_keywords": ["Phân loại", "Mã"],
    },
    {
        "name": "Hỏi món bán chạy",
        "message": "món nào bán chạy nhất",
        "expect_keywords": ["Đã bán", "Mã"],
    },
    {
        "name": "Chào hỏi (không cần tool)",
        "message": "xin chào",
        "expect_keywords": ["bạn"],
    },
]

async def run_test(test: dict) -> bool:
    agent = MeowTeaAgent(session_id="test-session", user_id=None, user_role=None)
    name = test["name"]
    msg  = test["message"]

    info(f'[{name}] "{msg}"')
    try:
        reply, actions = await agent.chat(message=msg, history=[])
    except Exception as e:
        err(f"[{name}] Exception: {e}")
        return False

    # kiểm tra không phải fallback error
    if "hệ thống đang bận" in reply or "gặp sự cố" in reply:
        err(f"[{name}] Fallback error triggered:\n  {reply[:120]}")
        return False

    # kiểm tra có keyword kỳ vọng không
    missing = [kw for kw in test["expect_keywords"] if kw.lower() not in reply.lower()]
    if missing:
        err(f"[{name}] Thiếu keyword {missing} trong reply:\n  {reply[:200]}")
        return False

    ok(f"[{name}] PASS — reply ({len(reply)} chars), actions: {len(actions)}")
    print(f"  {YELLOW}{reply[:180].strip()}...{RESET}" if len(reply) > 180 else f"  {YELLOW}{reply.strip()}{RESET}")
    return True


async def main():
    print(f"\n{CYAN}=== MeowBot Tool Call Test ==={RESET}")
    print(f"Model : {GROQ_MODEL}")
    print(f"Backend: {PHP_BASE_URL}\n")

    if "FILL_YOUR_KEY_HERE" in GROQ_API_KEY or not GROQ_API_KEY:
        err("GROQ_API_KEY chưa được điền trong chatbot/.env")
        sys.exit(1)

    if "localhost" in PHP_BASE_URL:
        print(f"{YELLOW}⚠ PHP_BASE_URL trỏ localhost ({PHP_BASE_URL}){RESET}")
        print(f"{YELLOW}  Tool calls sẽ fail — đổi thành https://meowteafresh.space trong .env{RESET}\n")

    results = []
    for i, t in enumerate(TESTS):
        passed = await run_test(t)
        results.append(passed)
        print()
        if i < len(TESTS) - 1:
            print(f"  {CYAN}⏳ chờ 3s (tránh rate limit)...{RESET}\n")
            await asyncio.sleep(3)

    passed = sum(results)
    total  = len(results)
    bar = "█" * passed + "░" * (total - passed)
    color = GREEN if passed == total else (YELLOW if passed > 0 else RED)
    print(f"{color}Result: {passed}/{total} [{bar}]{RESET}\n")
    sys.exit(0 if passed == total else 1)


if __name__ == "__main__":
    asyncio.run(main())
