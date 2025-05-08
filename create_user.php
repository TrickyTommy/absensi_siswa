<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_siswa";

try {
    $conn = new mysql($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully";
    
    $name = "Admin";
    $email = "admin";
    $password = password_hash("12345", PASSWORD_DEFAULT); // enkripsi password

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name','$email', '$password')";
    if ($conn->query($sql)) {
        echo "User berhasil dibuat.";
    } else {
        echo "Error: " . $conn->error;
    }
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
