<?php
require_once 'database/config.php';
$pdo = getDBConnection();

$tables = ['SanPham', 'Category', 'Store', 'News', 'AppUser', 'Role', 'Cart', 'Cart_Item', 'Cart_Item_Option'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
        $colCount = $stmt->columnCount();
        echo "Table: $table\n";
        for ($i = 0; $i < $colCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            echo "  - " . $meta['name'] . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "Table: $table - Error: " . $e->getMessage() . "\n\n";
    }
}
