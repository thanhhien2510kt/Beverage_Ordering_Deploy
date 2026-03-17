-- ==========================================================
-- MeowTea Fresh AI Chatbot - Supabase Schema
-- Chạy file này trong Supabase SQL Editor
-- ==========================================================

-- 1. Bật extension pgvector (nếu chưa bật)
CREATE EXTENSION IF NOT EXISTS vector;

-- ==========================================================
-- Bảng lưu embedding sản phẩm để semantic search
-- ==========================================================
DROP TABLE IF EXISTS products_embedding;
CREATE TABLE products_embedding (
    id              SERIAL PRIMARY KEY,
    product_id      INT NOT NULL,          -- maps to MySQL SanPham.MaSP
    product_name    TEXT NOT NULL,
    category        TEXT,
    price           DECIMAL(15, 0),
    description     TEXT,                  -- text dùng để embed
    embedding       vector(768),           -- Gemini text-embedding-004 = 768 dims
    synced_at       TIMESTAMPTZ DEFAULT NOW()
);

-- Index để tăng tốc similarity search
CREATE INDEX ON products_embedding
    USING ivfflat (embedding vector_cosine_ops)
    WITH (lists = 50);

-- ==========================================================
-- Function semantic search (gọi từ Python bằng supabase-py)
-- ==========================================================
CREATE OR REPLACE FUNCTION search_products_by_embedding(
    query_embedding vector(768),
    match_threshold FLOAT DEFAULT 0.5,
    match_count     INT   DEFAULT 5
)
RETURNS TABLE (
    product_id   INT,
    product_name TEXT,
    category     TEXT,
    price        DECIMAL,
    similarity   FLOAT
)
LANGUAGE sql STABLE
AS $$
    SELECT
        pe.product_id,
        pe.product_name,
        pe.category,
        pe.price,
        1 - (pe.embedding <=> query_embedding) AS similarity
    FROM products_embedding pe
    WHERE 1 - (pe.embedding <=> query_embedding) > match_threshold
    ORDER BY pe.embedding <=> query_embedding
    LIMIT match_count;
$$;

-- ==========================================================
-- Bảng lưu khiếu nại của khách hàng
-- ==========================================================
DROP TABLE IF EXISTS complaints;
CREATE TABLE complaints (
    id          SERIAL PRIMARY KEY,
    user_id     INT,
    order_id    INT,
    category    VARCHAR(100),              -- 'wrong_order', 'late_delivery', 'quality', 'other'
    content     TEXT NOT NULL,
    status      VARCHAR(50) DEFAULT 'pending',  -- 'pending', 'processing', 'resolved'
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

-- ==========================================================
-- Bảng lưu lịch sử hội thoại chatbot (optional - để debug)
-- ==========================================================
DROP TABLE IF EXISTS chat_sessions;
CREATE TABLE chat_sessions (
    id          SERIAL PRIMARY KEY,
    session_id  VARCHAR(100) NOT NULL,
    user_id     INT,
    role        VARCHAR(20) NOT NULL,      -- 'user' | 'assistant'
    content     TEXT NOT NULL,
    created_at  TIMESTAMPTZ DEFAULT NOW()
);

CREATE INDEX idx_chat_sessions_session ON chat_sessions(session_id);
