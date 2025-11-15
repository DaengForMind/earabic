<?php
// pages/save_progress.php
require_once '../config/database.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
    $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
    $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
    $total = isset($_POST['total']) ? intval($_POST['total']) : 10;
    
    if (empty($theme) || $level < 1 || $level > 4) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
        exit();
    }
    
    $conn = getConnection();
    
    // Insert progress
    $stmt = $conn->prepare("INSERT INTO user_progress (user_id, level, theme, score, total_questions) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisii", $user_id, $level, $theme, $score, $total);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Progress berhasil disimpan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan progress']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>