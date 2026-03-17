import sys, os, requests
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import GOOGLE_API_KEY

r = requests.get(
    "https://generativelanguage.googleapis.com/v1beta/models",
    params={"key": GOOGLE_API_KEY}
)
models = r.json().get("models", [])
chat = [m["name"] for m in models if "generateContent" in m.get("supportedGenerationMethods", [])]
print("Available chat models:")
for m in chat:
    print(" -", m)
