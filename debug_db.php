<?php
require_once __DIR__ . '/database/config.php';

echo "<h1>Database Connection Debug</h1>";
echo "DB_TYPE: " . DB_TYPE . "<br>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Kết nối thành công!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: " . $version;
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Kết nối thất bại!</p>";
    echo "<strong>Lỗi:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Code:</strong> " . $e->getCode() . "<br>";
    
    if (strpos($e->getMessage(), "could not find driver") !== false) {
        echo "<p><strong>Gợi ý:</strong> Bạn chưa bật extension <code>pdo_pgsql</code> trong file <code>php.ini</code> của XAMPP.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi hệ thống: " . $e->getMessage() . "</p>";
}

echo "<h2>Loaded Extensions:</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>
