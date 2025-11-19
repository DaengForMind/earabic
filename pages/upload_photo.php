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

// Basic upload error
if ($file['error'] !== UPLOAD_ERR_OK) {
    error_log("Upload error code: " . $file['error']);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat upload (code '.$file['error'].')']);
    exit();
}

// Validate size
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
    exit();
}

// Validate MIME using finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime, $allowed_types)) {
    error_log("Invalid mime: $mime");
    echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF']);
    exit();
}

// Prepare directories using absolute paths
$upload_dir_abs = realpath(__DIR__ . '/../uploads') . '/profiles/';
if ($upload_dir_abs === false) {
    // try to create uploads/profiles
    $base = __DIR__ . '/../uploads/profiles/';
    if (!mkdir($base, 0755, true) && !is_dir($base)) {
        error_log("Failed create upload dir: $base");
        echo json_encode(['success' => false, 'message' => 'Gagal membuat direktori upload. Periksa permission server.']);
        exit();
    }
    $upload_dir_abs = realpath($base) . '/';
}

// Ensure writable
if (!is_writable($upload_dir_abs)) {
    error_log("Upload dir not writable: $upload_dir_abs");
    echo json_encode(['success' => false, 'message' => 'Direktori upload tidak bisa ditulis. Periksa permission.']);
    exit();
}

// Generate unique filename and move
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($extension === '') {
    // derive from mime as fallback
    $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    $extension = $map[$mime] ?? 'jpg';
}
$filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
$filepath_abs = $upload_dir_abs . $filename;

// Move file
if (!move_uploaded_file($file['tmp_name'], $filepath_abs)) {
    error_log("move_uploaded_file failed. tmp_name: {$file['tmp_name']}, dest: $filepath_abs");
    echo json_encode(['success' => false, 'message' => 'Gagal upload file (server).']);
    exit();
}

// Delete old photo (if exists) using absolute path
$conn = getConnection();
$stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$old_photo = $user['photo'] ?? null;
$stmt->close();

if ($old_photo) {
    $old_abs = realpath(__DIR__ . '/../' . $old_photo);
    if ($old_abs && strpos($old_abs, realpath(__DIR__ . '/../uploads')) === 0 && file_exists($old_abs)) {
        @unlink($old_abs);
    }
}

// Save new photo path (web-accessible relative path)
$photo_db_path = 'uploads/profiles/' . $filename;
$stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
$stmt->bind_param("si", $photo_db_path, $user_id);

if ($stmt->execute()) {
    // Return a URL the frontend can use; keep same pattern as existing code
    echo json_encode([
        'success' => true,
        'message' => 'Foto berhasil diupload',
        'photo_url' => '../' . $photo_db_path
    ]);
} else {
    // remove the uploaded file because DB save failed
    @unlink($filepath_abs);
    error_log("DB update failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
}

$stmt->close();
$conn->close();
?>
