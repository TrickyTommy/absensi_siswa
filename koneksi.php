<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "absensi_siswa"; // Update this line

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
