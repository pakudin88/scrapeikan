<?php

require_once 'config.php';

// Buat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

?>
