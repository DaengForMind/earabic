<?php
// test_user.php — hanya untuk debugging, hapus setelah selesai

require_once __DIR__ . '/config/database.php';

// Jangan panggil startSession() di sini — kita hanya cek DB
try {
    $conn = getConnection();
    $sql = "SELECT id, email, password FROM users LIMIT 5";
    $result = $conn->query($sql);

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        // jangan tampilkan password asli kalau di production; ini hanya debugging
        $rows[] = [
            'id' => $r['id'],
            'email' => $r['email'],
            'password_preview' => strlen($r['password']) . ' chars'
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'rows' => $rows], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    $conn->close();
} catch (Throwable $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
    exit;
}
