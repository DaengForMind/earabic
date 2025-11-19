<?php
// pages/upload_photo.php
require_once '../config/database.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Tidak login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['photo'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$user_id = $_SESSION['user_id'];
$file = $_FILES['photo'];

// Check upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit();
}

// Validate size
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

// **RAILWAY SPECIFIC: Gunakan absolute path yang benar**
$base_dir = $_SERVER['RAILWAY_VOLUME_MOUNT_PATH'] ?? $_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/../';
$upload_dir = $base_dir . '../uploads/profiles/';

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
        echo json_encode(['success' => false, 'message' => 'Direktori tidak bisa ditulis. Permission denied.']);
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
    error_log("Move uploaded file failed. Tmp: " . $file['tmp_name'] . " -> Dest: " . $filepath);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
    exit();
}

// Update database
try {
    $conn = getConnection();
    
    // Get old photo for deletion
    $stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_data = $result->fetch_assoc();
    $stmt->close();

    // Delete old photo if exists
    if (!empty($old_data['photo'])) {
        $old_file_path = $base_dir . $old_data['photo'];
        if (file_exists($old_file_path) && is_file($old_file_path)) {
            @unlink($old_file_path);
        }
    }

    // Save new photo path (relative path untuk web)
    $photo_db_path = '../uploads/profiles/' . $filename;
    
    $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
    $stmt->bind_param("si", $photo_db_path, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'photo_url' => $photo_db_path // Return relative path
        ]);
    } else {
        // Rollback: delete uploaded file if DB failed
        @unlink($filepath);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $stmt->error]);
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
