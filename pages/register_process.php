<?php
// pages/register_process.php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    
    // Validasi input
    if (empty($nama) || empty($telepon) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit();
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
        exit();
    }
    
    // Ambil initial dari nama (huruf pertama)
    $avatar_initial = strtoupper(substr($nama, 0, 1));
    
    $conn = getConnection();
    
    // Cek apakah telepon atau email sudah terdaftar
    $stmt = $conn->prepare("SELECT id FROM users WHERE telepon = ? OR email = ?");
    $stmt->bind_param("ss", $telepon, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Telepon atau email sudah terdaftar']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Insert user baru
    $stmt = $conn->prepare("INSERT INTO users (nama, telepon, email, avatar_initial) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $telepon, $email, $avatar_initial);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registrasi berhasil']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>