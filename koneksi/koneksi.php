<?php
// Konfigurasi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "produk_hp";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Jika koneksi berhasil, tidak perlu melakukan apa-apa
?>
