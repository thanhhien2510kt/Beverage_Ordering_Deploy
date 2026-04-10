<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../functions.php';

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    echo json_encode(['history' => []]);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT Role, Content FROM Chat_Message WHERE SessionID = ? ORDER BY MaChat ASC LIMIT 50");
    $stmt->execute([$sessionId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $history = [];
    foreach ($messages as $msg) {
        // Handle both case-sensitive (MySQL) and lowercase (PostgreSQL) keys
        $role = $msg['Role'] ?? $msg['role'] ?? '';
        $content = $msg['Content'] ?? $msg['content'] ?? '';
        
        if ($role && $content) {
            $history[] = [
                'role' => strtolower($role),
                'content' => $content
            ];
        }
    }
    
    echo json_encode(['history' => $history]);
} catch (Exception $e) {
    echo json_encode(['history' => [], 'error' => $e->getMessage()]);
}
