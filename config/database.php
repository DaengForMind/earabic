<?php
// config/database.php

$publicUrl = getenv('MYSQL_PUBLIC_URL');

if (!$publicUrl) {
    error_log("ENV MYSQL_PUBLIC_URL NOT FOUND");
    die("Database configuration missing.");
}

$parts = parse_url($publicUrl);

define('DB_HOST', $parts['host']);
define('DB_PORT', $parts['port']);
define('DB_USER', $parts['user']);
define('DB_PASS', $parts['pass']);
define('DB_NAME', ltrim($parts['path'], '/'));

// Session
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// DB
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Throwable $e) {
        error_log("DB ERROR: " . $e->getMessage());
        die("Database connection failed.");
    }
}
