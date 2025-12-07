<?php
// db.php (Standardized for MySQLi)

// 1. Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Central Config
require_once __DIR__ . '/config.php';

// --- 3. MySQLi Connection Setup ---
// Assume DB credentials (like DB_HOST, DB_USER, etc.) are defined in config.php.
// Using explicit credentials here for demonstration, replace with constants if available.

// Use object-oriented mysqli for better error handling and consistency
// Create mysqli connection and also expose legacy $conn variable
$mysqli = new mysqli('localhost', 'root', '', 'spa');

if ($mysqli->connect_error) {
    error_log('MySQLi Connection failed: ' . $mysqli->connect_error);
    die('<h1>Database Error: System currently unavailable.</h1>');
}

$mysqli->set_charset("utf8mb4");

// Backwards compatibility: many scripts expect $conn (mysqli) and some expect $pdo
$conn = $mysqli;

// Create PDO connection for code that uses $pdo
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=spa;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log('PDO connection failed: ' . $e->getMessage());
    // Keep going; parts of the app that require PDO will fail gracefully.
}
?>