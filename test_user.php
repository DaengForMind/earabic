<?php
require_once 'config/database.php';

$conn = getConnection();

echo "Connected to: " . DB_HOST . ":" . DB_PORT . "<br>";

$res = $conn->query("SELECT COUNT(*) AS total FROM users");
$row = $res->fetch_assoc();
echo "User count: " . $row['total'];
