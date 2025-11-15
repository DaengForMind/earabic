<?php
// pages/login_process.php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    
    // Validasi input
    if (empty($telepon) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Telepon dan email harus diisi']);
        exit();
    }
    
    $conn = getConnection();
    
    // Cek user berdasarkan telepon dan email
    $stmt = $conn->prepare("SELECT id, nama FROM users WHERE telepon = ? AND email = ?");
    $stmt->bind_param("ss", $telepon, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session
        startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama'];
        
        echo json_encode(['success' => true, 'message' => 'Login berhasil']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Telepon atau email tidak ditemukan']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>