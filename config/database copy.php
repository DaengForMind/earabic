<?php
// config/database.php

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'earabic');

// Fungsi koneksi database
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Fungsi untuk memulai session jika belum dimulai
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan data user yang sedang login
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
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

// Fungsi untuk redirect
function redirect($page) {
    header("Location: $page");
    exit();
}
?>