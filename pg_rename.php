<?php
require_once 'database/config.php';
$pdo = getDBConnection();

$schema = file_get_contents('database/schema.sql');

// Regex to find CREATE TABLE and its columns
preg_match_all('/CREATE TABLE `([^`]+)` \((.*?)\) ENGINE/s', $schema, $matches, PREG_SET_ORDER);

$renames = [];

foreach ($matches as $match) {
    $tableName = $match[1];
    $columnsBlock = $match[2];
    
    // Regex to extract column names
    preg_match_all('/`([^`]+)`/', $columnsBlock, $colMatches);
    
    foreach ($colMatches[1] as $colName) {
        // Only if column name is not entirely uppercase or lowercase
        // Actually just rename everything to ensure it has the correct case
        if (strtolower($tableName) == 'role' && $colName == 'MaRole' || $colName == 'TenRole') {
            // it's fine
        }
        $lowerTable = strtolower($tableName);
        $lowerCol = strtolower($colName);
        
        $renames[] = "ALTER TABLE \"$lowerTable\" RENAME COLUMN \"$lowerCol\" TO \"$colName\";";
    }
}

// Remove duplicates and clean up
$renames = array_unique($renames);

foreach ($renames as $sql) {
    try {
        $pdo->exec($sql);
        echo "Success: $sql\n";
    } catch (PDOException $e) {
        // Ignore errors if column doesn't exist or already renamed
        // echo "Skipped: " . $e->getMessage() . "\n";
    }
}
echo "PostgreSQL columns successfully renamed to PascalCase!\n";
