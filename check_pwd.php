<?php
require_once 'database/config.php';
$pdo = getDBConnection();
// Thử với tên cột MySQL thực tế (case-insensitive thường lấy theo DB schema)
$stmt = $pdo->query("SELECT MaUser, Username, Password FROM AppUser LIMIT 3");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "EMPTY - no users found"; exit; }
foreach($rows as $r) {
    $keys = array_keys($r);
    echo implode(',', $keys) . "\n"; // in column names
    echo $r[$keys[1]] . ' | ' . substr($r[$keys[2]], 0, 25) . "\n";
}
