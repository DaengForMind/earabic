<?php
// pages/upload_photo.php
require_once '../config/database.php';
require_once '../config/functions.php';
startSession();

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
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung']);
    exit();
}

// **PATH YANG BENAR UNTUK STRUKTUR ANDA**
// Dari pages/upload_photo.php ke uploads/profiles/
$upload_dir = __DIR__ . '/../uploads/profiles/';

error_log("Upload directory: " . $upload_dir);

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
    error_log("Directory not writable: " . $upload_dir);
    echo json_encode(['success' => false, 'message' => 'Direktori upload tidak bisa ditulis. Periksa permission.']);
    exit();
}

error_log("Directory is writable: " . $upload_dir);

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

error_log("File will be saved to: " . $filepath);

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    error_log("move_uploaded_file FAILED");
    error_log("Temp file: " . $file['tmp_name']);
    error_log("Destination: " . $filepath);
    
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file']);
    exit();
}

error_log("File successfully moved to: " . $filepath);

// Verify file was saved
if (!file_exists($filepath)) {
    error_log("ERROR: File doesn't exist after move_uploaded_file!");
    echo json_encode(['success' => false, 'message' => 'File tidak tersimpan setelah upload']);
    exit();
}

$file_size = filesize($filepath);
error_log("File verified exists, size: " . $file_size);

// Update database
try {
    $conn = getConnection();
    
    // **PATH UNTUK DATABASE - Relative dari root web**
    $photo_db_path = 'uploads/profiles/' . $filename;
    
    error_log("Database path: " . $photo_db_path);

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
            if (unlink($old_file_path)) {
                error_log("Deleted old photo: " . $old_file_path);
            } else {
                error_log("Failed to delete old photo: " . $old_file_path);
            }
        }
    }

    // Save to database
    $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
    $stmt->bind_param("si", $photo_db_path, $user_id);
    
    if ($stmt->execute()) {
        error_log("Database updated successfully");
        echo json_encode([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'photo_url' => $photo_db_path
        ]);
    } else {
        // Rollback: delete uploaded file if DB failed
        @unlink($filepath);
        error_log("DB update failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Rollback file
    @unlink($filepath);
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
}
?>
