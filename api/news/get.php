<?php
header('Content-Type: application/json');
require_once '../../functions.php';

$newsId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$newsId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID tin tức']);
    exit;
}

try {
    $news = getNewsById($newsId);
    
    if (!$news) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tin tức']);
        exit;
    }

    // Đọc nội dung bài viết từ file Markdown
    $content = '';
    if (!empty($news['noidung'])) {
        $content = readMarkdownFile($news['noidung']);
    }

    // Chuẩn bị dữ liệu trả về
    $data = [
        'id' => $news['manews'],
        'title' => $news['tieude'],
        'content' => $content,
        'image' => $news['hinhanh'] ? $news['hinhanh'] : 'assets/img/news/news_one.jpg',
        'date' => date('d/m/Y', strtotime($news['ngaytao']))
    ];

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
