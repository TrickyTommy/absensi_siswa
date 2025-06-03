<?php
require_once 'koneksi.php';

// Get date parameter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query data
$query = "SELECT a.*, s.nama, s.nis 
          FROM absensis a
          LEFT JOIN siswas s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = ?
          ORDER BY a.jam_masuk";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();

// Create content
$content = "LAPORAN ABSENSI SISWA\n";
$content .= "Tanggal: " . date('d-m-Y', strtotime($tanggal)) . "\n\n";
$content .= str_pad("No", 5) . str_pad("NIS", 15) . str_pad("Nama", 30) . 
           str_pad("Jam Masuk", 15) . str_pad("Status", 15) . "Keterangan\n";
$content .= str_repeat("=", 100) . "\n";

$no = 1;
while($row = $result->fetch_assoc()) {
    $content .= str_pad($no++, 5) . 
                str_pad($row['nis'], 15) . 
                str_pad($row['nama'], 30) . 
                str_pad($row['jam_masuk'], 15) . 
                str_pad($row['status'], 15) . 
                $row['keterangan'] . "\n";
}

// Set headers for download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="Laporan_Absensi_'.date('d-m-Y', strtotime($tanggal)).'.txt"');

// Output the content
echo $content;
