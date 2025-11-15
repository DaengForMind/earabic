<?php
// config/database.php

// Ambil URL publik resmi Railway
$publicUrl = getenv('MYSQL_PUBLIC_URL');

// Parse URL
$parts = parse_url($publicUrl);

define('DB_HOST', $parts['host']);
define('DB_PORT', $parts['port']);
define('DB_USER', $parts['user']);
define('DB_PASS', $parts['pass']);
define('DB_NAME', ltrim($parts['path'], '/'));

// Start Session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Koneksi DB
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        error_log("DB ERROR: " . $conn->connect_error);
        die("Database connection failed.");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Login check
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    startSession();

    if (!isset($_SESSION['user_id'])) return null;

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

function redirect($page) {
    header("Location: $page");
    exit();
}
