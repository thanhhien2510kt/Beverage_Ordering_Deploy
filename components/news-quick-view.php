<!-- News Quick View Modal -->
<div id="news-quick-view-modal" class="product-customize-modal">
    <div class="modal-overlay"></div>
    <div class="modal-side-panel">
        <!-- Close Button -->
        <button type="button" id="close-news-modal-btn" class="modal-close-btn" aria-label="Đóng">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
        
        <div class="modal-content">
            <div id="news-modal-loading" class="modal-loading">
                <p>Đang tải tin tức...</p>
            </div>
            
            <div id="news-modal-content" style="display: none;">
                <!-- News Image -->
                <div class="modal-news-image-wrapper" style="margin-bottom: 25px; border-radius: 20px; overflow: hidden; height: 250px;">
                    <img id="news-modal-image" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                
                <!-- News Info -->
                <div class="modal-news-info">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <span id="news-modal-date" style="font-size: 14px; color: var(--text-light); font-weight: bold;"></span>
                        <span style="width: 4px; height: 4px; border-radius: 50%; background-color: var(--border-color);"></span>
                        <span style="font-size: 14px; color: var(--primary-green); font-weight: bold;">MeowTea Fresh</span>
                    </div>
                    <h2 id="news-modal-title" style="font-size: 24px; font-weight: bold; color: var(--primary-green); margin-bottom: 20px; line-height: 1.3;"></h2>
                    
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin-bottom: 20px;">
                    
                    <!-- News Body (Rendered Markdown) -->
                    <div id="news-modal-body" class="markdown-body" style="line-height: 1.8; color: var(--text-dark); font-size: 16px;">
                        <!-- Content will be injected here -->
                    </div>
                </div>
                
                <div style="margin-top: 40px; text-align: center;">
                    <a id="news-modal-detail-link" href="#" class="btn-view-cart" style="text-decoration: none; display: inline-flex; width: auto; padding: 0 40px;">
                        Đọc toàn bộ bài viết
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.markdown-body h1, .markdown-body h2, .markdown-body h3 {
    color: var(--primary-green);
    margin-top: 25px;
    margin-bottom: 15px;
}
.markdown-body p {
    margin-bottom: 15px;
}
.markdown-body img {
    max-width: 100%;
    border-radius: 10px;
    margin: 15px 0;
}
.markdown-body ul, .markdown-body ol {
    margin-bottom: 15px;
    padding-left: 20px;
}
.markdown-body blockquote {
    border-left: 4px solid var(--primary-green);
    padding-left: 15px;
    color: var(--text-light);
    font-style: italic;
    margin: 20px 0;
}
</style>
