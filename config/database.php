<?php
// config/database.php

// Jika Railway menyediakan MYSQL_PUBLIC_URL → gunakan itu
$publicUrl = getenv('MYSQL_PUBLIC_URL');

if ($publicUrl) {
    $parts = parse_url($publicUrl);

    define('DB_HOST', $parts['host']);
    define('DB_PORT', $parts['port']);
    define('DB_USER', $parts['user']);
    define('DB_PASS', $parts['pass']);
    define('DB_NAME', ltrim($parts['path'], '/'));
} else {
    // Local development
    define('DB_HOST', 'localhost');
    define('DB_PORT', 3306);
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'earabic');
}

// Koneksi database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Start session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Cek login
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Ambil user login
function getCurrentUser() {
    if (!isLoggedIn()) return null;

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
}

// Redirect
function redirect($page) {
    header("Location: $page");
    exit();
}
