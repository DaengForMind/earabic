<?php
// pages/update_profile.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

header('Content-Type: application/json');

// Simple authentication check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$instansi = $_POST['instansi'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$motivasi = $_POST['motivasi'] ?? '';

try {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET instansi = ?, tanggal_lahir = ?, motivasi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $instansi, $tanggal_lahir, $motivasi, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
