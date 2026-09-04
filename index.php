<?php
// PayMI - Render + Postgres Version
// Homepage route
if ($_SERVER['REQUEST_URI'] == '/') {
    header('Content-Type: application/json');
    echo json_encode(["status" => "PayMi API is running ✅", "db_url" => "connected"]);
    exit();
}
    $database_url = getenv('DATABASE_URL');

    if (!$database_url) {
        die("DATABASE_URL not set. Go to Render > Environment and add it.");
    }

    $db = parse_url($database_url);
    $host = $db["host"];
    $username = $db["user"];
    $password = $db["pass"];
    $dbname = ltrim($db["path"], "/");
    $port = $db["port"];

    try {
        $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100) UNIQUE,
        balance DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo "PayMI is LIVE on Render! ✅<br>";
    echo "Database connected successfully to Postgres.";
    ?>
