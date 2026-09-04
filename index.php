<?php
header('Content-Type: application/json');

$databaseUrl = $_ENV['DATABASE_URL'] ?? '';

if (empty($databaseUrl)) {
    echo json_encode(["error" => "DATABASE_URL not set"]);
    exit;
}

// Convert postgres:// to pdo format
$db = parse_url($databaseUrl);
$dsn = "pgsql:host=" . $db["host"] . ";port=" . $db["port"] . ";dbname=" . ltrim($db["path"], '/');

try {
    $pdo = new PDO($dsn, $db["user"], $db["pass"]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// HOME ROUTE
if ($path === '/') {
    echo json_encode(["status" => "PayMi API is running ✅", "db_url" => "connected"]);
    exit;
}

// MIGRATE ROUTE - CREATE TABLES
if ($path === '/migrate') {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            balance DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
            id SERIAL PRIMARY KEY,
            user_id INT REFERENCES users(id),
            amount DECIMAL(10,2) NOT NULL,
            type VARCHAR(50) NOT NULL,
            reference VARCHAR(255) UNIQUE,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        echo json_encode(["status" => "Tables created successfully ✅"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
    exit;
}
