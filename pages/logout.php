<?php
// pages/logout.php
require_once '../config/database.php';
startSession();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman utama
redirect('../index.php');
?>