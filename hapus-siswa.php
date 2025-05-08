<?php
include 'koneksi.php';
$id = $_GET['id'];
$conn->query("DELETE FROM siswas WHERE id=$id");
header("Location: list-siswa.php");
