<?php
// pages/update_profile.php
require_once '../config/database.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $instansi = isset($_POST['instansi']) ? trim($_POST['instansi']) : '';
    $tanggal_lahir = isset($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : null;
    $motivasi = isset($_POST['motivasi']) ? trim($_POST['motivasi']) : '';
    
    $conn = getConnection();
    
    // Update profile
    $stmt = $conn->prepare("UPDATE users SET instansi = ?, tanggal_lahir = ?, motivasi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $instansi, $tanggal_lahir, $motivasi, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal update profile']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>