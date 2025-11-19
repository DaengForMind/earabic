<?php
// pages/upload_photo.php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Simple authentication check
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Simple redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diupload']);
    exit();
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['photo'];

// Check upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        1 => 'File terlalu besar (server limit)',
        2 => 'File terlalu besar (form limit)',
        3 => 'File hanya terupload sebagian',
        4 => 'Tidak ada file yang dipilih',
        6 => 'Folder temporary tidak ada',
        7 => 'Gagal menulis file',
        8 => 'Upload dihentikan oleh extension'
    ];
    
    $message = $error_messages[$file['error']] ?? 'Unknown error: ' . $file['error'];
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Validate size (5MB max)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
    exit();
}

// Validate type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF']);
    exit();
}

// **PATH YANG BENAR**
$upload_dir = __DIR__ . '/../uploads/profiles/';

// Buat directory jika belum ada
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        error_log("Failed to create directory: " . $upload_dir);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat direktori upload']);
        exit();
    }
}

// Cek jika directory writable
if (!is_writable($upload_dir)) {
    // Coba fix permission
    chmod($upload_dir, 0755);
    if (!is_writable($upload_dir)) {
        error_log("Directory not writable: " . $upload_dir);
        echo json_encode(['success' => false, 'message' => 'Direktori upload tidak bisa ditulis. Periksa permission.']);
        exit();
    }
}

// Generate filename
$extension = match($mime) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png', 
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    default => 'jpg'
};

$filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    error_log("move_uploaded_file failed: " . $file['tmp_name'] . " to " . $filepath);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
    exit();
}

// Verify file was saved
if (!file_exists($filepath)) {
    echo json_encode(['success' => false, 'message' => 'File tidak tersimpan setelah upload']);
    exit();
}

// Update database
try {
    $conn = getConnection();
    
    // Path untuk database
    $photo_db_path = 'uploads/profiles/' . $filename;

    // Get old photo for deletion
    $stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_data = $result->fetch_assoc();
    $old_photo = $old_data['photo'] ?? null;
    $stmt->close();

    // Delete old photo if exists
    if (!empty($old_photo)) {
        $old_file_path = __DIR__ . '/../' . $old_photo;
        if (file_exists($old_file_path) && is_file($old_file_path)) {
            @unlink($old_file_path);
        }
    }

    // Save to database
    $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
    $stmt->bind_param("si", $photo_db_path, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'photo_url' => $photo_db_path
        ]);
    } else {
        // Rollback: delete uploaded file if DB failed
        @unlink($filepath);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Rollback file
    @unlink($filepath);
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error database']);
}
?>
