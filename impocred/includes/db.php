<?php
/**
 * Database connection for Impocred Credit Management System
 * Uses PDO with error handling and UTF-8 support
 */

// Database configuration
$host = 'localhost';
$dbname = 'impocred';
$username = 'root';
$password = '';

try {
    // Create PDO connection with options
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Set timezone
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch (PDOException $e) {
    // Log error (in production, log to file instead of displaying)
    error_log("Database connection failed: " . $e->getMessage());
    
    // Display user-friendly error message
    die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
}

/**
 * Function to test database connection
 * @return bool
 */
function testConnection() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Function to safely execute prepared statements
 * @param string $sql
 * @param array $params
 * @return PDOStatement|false
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Function to get last insert ID
 * @return string
 */
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}
?>
