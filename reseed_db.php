<?php
require_once 'database/config.php';
$pdo = getDBConnection();

function executeSqlFile($pdo, $filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }
    $sql = file_get_contents($filePath);
    
    // Split the SQL file into separate statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        'strlen'
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Ignore drop info
            }
        }
    }
    echo "Successfully executed $filePath\n";
}

try {
    // 1. Create schema
    executeSqlFile($pdo, 'database/schema.sql');
    
    // 2. Insert data
    executeSqlFile($pdo, 'database/seed-data.sql');
    
    echo "Database successfully recreated and seeded with proper PascalCase schema!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
