<?php
require_once 'koneksi.php';

// Add kelas column to siswas table
$sql = "ALTER TABLE siswas ADD COLUMN kelas VARCHAR(20) AFTER nama";
if ($conn->query($sql)) {
    echo "Added kelas column successfully";
} else {
    echo "Error adding kelas column: " . $conn->error;
}
?>
