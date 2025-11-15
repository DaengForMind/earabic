<?php
// config/database.php

// -------------------------------
// 1. LOAD ENV RAILWAY
// -------------------------------
$publicUrl = getenv('MYSQL_PUBLIC_URL');

// Railway Public URL jika tersedia (akses dari luar container)
if ($publicUrl) {
    $parts = parse_url($publicUrl);

    define('DB_HOST', $parts['host']);
    define('DB_PORT', $parts['port']);
    define('DB_USER', $parts['user']);
    define('DB_PASS', $parts['pass']);
    define('DB_NAME', ltrim($parts['path'], '/'));
}

// Jika tidak ada PUBLIC_URL, pakai env default Railway internal
elseif (getenv('MYSQLDATABASE')) {
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_PORT', getenv('MYSQLPORT') ?: 3306);
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
}

// Mode lokal
else {
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'earabic');
}


// -------------------------------
// 2. START SESSION (AMAN)
// -------------------------------
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}


// -------------------------------
// 3. KONEKSI DATABASE
// -------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Throwable $e) {
        error_log("DB CONNECTION ERROR: " . $e->getMessage());
        die("Tidak dapat terhubung ke database.");
    }
}


// -------------------------------
// 4. AUTH HELPERS
// -------------------------------
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    startSession();

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    return $user;
}


// -------------------------------
// 5. REDIRECT
// -------------------------------
function redirect($page) {
    header("Location: $page");
    exit();
}
