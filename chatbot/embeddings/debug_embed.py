"""
Debug script: kiểm tra Gemini Embedding API với nhiều cách thử khác nhau.
Chạy: python embeddings/debug_embed.py
"""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import requests
from config import GOOGLE_API_KEY

def test_embed(label, url, body):
    print(f"\n{'='*60}")
    print(f"TEST: {label}")
    print(f"URL: {url}")
    print(f"KEY: ...{GOOGLE_API_KEY[-8:]}")
    resp = requests.post(url, params={"key": GOOGLE_API_KEY}, json=body, timeout=10)
    print(f"Status: {resp.status_code}")
    try:
        data = resp.json()
        if resp.ok:
            emb = data.get("embedding", {}).get("values", [])
            print(f"✅ OK — vector length: {len(emb)}")
        else:
            print(f"❌ Error body: {data}")
    except Exception as e:
        print(f"❌ JSON parse error: {e}")
        print(f"Raw: {resp.text[:300]}")

# === Test 1: v1beta + text-embedding-004 (body có model)
test_embed(
    "v1beta + text-embedding-004 (with model in body)",
    "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent",
    {
        "model": "models/text-embedding-004",
        "content": {"parts": [{"text": "cà phê sữa"}]},
        "taskType": "RETRIEVAL_DOCUMENT"
    }
)

# === Test 2: v1beta + text-embedding-004 (NO model in body)
test_embed(
    "v1beta + text-embedding-004 (NO model in body)",
    "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent",
    {
        "content": {"parts": [{"text": "cà phê sữa"}]}
    }
)

# === Test 3: v1beta + embedding-001 (fallback)
test_embed(
    "v1beta + embedding-001 (fallback model)",
    "https://generativelanguage.googleapis.com/v1beta/models/embedding-001:embedContent",
    {
        "model": "models/embedding-001",
        "content": {"parts": [{"text": "cà phê sữa"}]}
    }
)

# === Test 4: List available models
print(f"\n{'='*60}")
print("TEST: List models (check what's available for this key)")
resp = requests.get(
    "https://generativelanguage.googleapis.com/v1beta/models",
    params={"key": GOOGLE_API_KEY},
    timeout=10
)
print(f"Status: {resp.status_code}")
if resp.ok:
    models = resp.json().get("models", [])
    embed_models = [m["name"] for m in models if "embed" in m["name"].lower()]
    print(f"✅ Embedding models available: {embed_models}")
else:
    print(f"❌ Error: {resp.json()}")
