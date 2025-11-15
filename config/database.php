<?php
// config/database.php

// Railway Database Configuration
if (getenv('MYSQLDATABASE')) {
    // Railway environment
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT'));
} else {
    // Local development
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'earabic');
    define('DB_PORT', '3306');
}

// Fungsi koneksi database
function getConnection() {
    $port = defined('DB_PORT') ? DB_PORT : 3306;
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    
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
```

### **D. Buat file `.gitignore`**
```
.env
.DS_Store
Thumbs.db