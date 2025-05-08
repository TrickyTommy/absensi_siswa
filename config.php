<?php
$host = "localhost";
$user = "root";
$pass = ""; // default XAMPP
$db   = "absensi_siswa"; // ganti dengan nama DB kamu, misalnya: smk_db

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
