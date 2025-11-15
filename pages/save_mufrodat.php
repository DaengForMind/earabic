<?php
// pages/save_mufrodat.php
require_once '../config/database.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $word = isset($_POST['word']) ? trim($_POST['word']) : '';
    $translation = isset($_POST['translation']) ? trim($_POST['translation']) : '';
    $audio_url = isset($_POST['audio_url']) ? trim($_POST['audio_url']) : '';
    
    if (empty($word) || empty($translation)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit();
    }
    
    $conn = getConnection();
    
    // Cek apakah mufrodat sudah ada
    $stmt = $conn->prepare("SELECT id FROM mufrodat WHERE user_id = ? AND arabic_word = ?");
    $stmt->bind_param("is", $user_id, $word);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Sudah ada, tidak perlu insert lagi
        echo json_encode(['success' => true, 'message' => 'Mufrodat sudah ada']);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();
    
    // Insert mufrodat baru dengan audio_url
    $stmt = $conn->prepare("INSERT INTO mufrodat (user_id, arabic_word, translation, audio_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $word, $translation, $audio_url);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Mufrodat berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan mufrodat']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>