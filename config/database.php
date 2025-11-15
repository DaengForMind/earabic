<?php
// config/database.php
// Improved Railway-compatible DB config and safe session start

// Prefer public URL if available (useful when connecting from outside Railway)
$publicUrl = getenv('MYSQL_PUBLIC_URL');

if ($publicUrl) {
    $parts = parse_url($publicUrl);
    if ($parts !== false) {
        define('DB_HOST', $parts['host'] ?? getenv('MYSQLHOST'));
        define('DB_PORT', isset($parts['port']) ? (string)$parts['port'] : (getenv('MYSQLPORT') ?: '3306'));
        define('DB_USER', $parts['user'] ?? getenv('MYSQLUSER'));
        define('DB_PASS', $parts['pass'] ?? getenv('MYSQLPASSWORD'));
        // path may begin with "/" so trim it
        define('DB_NAME', isset($parts['path']) ? ltrim($parts['path'], '/') : (getenv('MYSQLDATABASE') ?: 'railway'));
    } else {
        // fallback to individual env vars
        define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
        define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
        define('DB_USER', getenv('MYSQLUSER') ?: 'root');
        define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
        define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
    }
} elseif (getenv('MYSQLDATABASE')) {
    // Railway internal envs
    define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} else {
    // Local development
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'earabic');
    define('DB_PORT', '3306');
}

// Start session immediately (must happen before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable mysqli exceptions for clearer errors (optional, helpful for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * getConnection — returns mysqli connection or throws exception
 * caller can catch or let error propagate to logs
 */
function getConnection() {
    $port = defined('DB_PORT') ? (int) DB_PORT : 3306;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);

    if ($conn->connect_error) {
        // Use error_log instead of echo (no output before headers)
        error_log("DB connect error: " . $conn->connect_error);
        throw new Exception("Database connection failed.");
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    try {
        $conn = getConnection();
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();
        $conn->close();

        return $user;
    } catch (Throwable $e) {
        error_log("getCurrentUser error: " . $e->getMessage());
        return null;
    }
}

function redirect($page) {
    header("Location: $page");
    exit();
}
