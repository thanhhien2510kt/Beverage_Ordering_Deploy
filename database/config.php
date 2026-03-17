<?php

// --- Database Configuration ---
// Switch between 'mysql' or 'pgsql'
define('DB_TYPE', 'pgsql');


// MySQL Config (Local/XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'meowtea_schema');
define('DB_CHARSET', 'utf8mb4');

// PostgreSQL / Supabase Config
define('PG_HOST', 'aws-1-ap-southeast-1.pooler.supabase.com');
define('PG_PORT', '6543');
define('PG_USER', 'postgres.uqdyscxjfytvesmxjyfd');
define('PG_PASS', 'Famuonnam@2510');
define('PG_NAME', 'postgres');

function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            if (DB_TYPE === 'pgsql') {
                $dsn = "pgsql:host=" . PG_HOST . ";port=" . PG_PORT . ";dbname=" . PG_NAME;
                $user = PG_USER;
                $pass = PG_PASS;
            }
            else {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $user = DB_USER;
                $pass = DB_PASS;
            }

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $pdo = new PDO($dsn, $user, $pass, $options);
        }
        catch (PDOException $e) {
            error_log("Database connection failed (" . DB_TYPE . "): " . $e->getMessage());
            throw $e; // Throw exception instead of die() for debugging
        }
    }

    return $pdo;
}

function testDBConnection()
{
    try {
        $pdo = getDBConnection();
        return $pdo !== null;
    }
    catch (Exception $e) {
        return false;
    }
}
?>
