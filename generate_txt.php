<?php
require_once 'koneksi.php';

// Get date parameter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query data with ordering for absent students first
$query = "SELECT a.*, s.nama, s.nis, s.kelas 
          FROM absensis a
          LEFT JOIN siswas s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = ?
          ORDER BY 
            CASE a.status 
                WHEN 'Alpha' THEN 1
                WHEN 'Sakit' THEN 2
                WHEN 'Izin' THEN 3
                ELSE 4
            END,
            s.kelas, a.jam_masuk";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();

// Create content with sections
$content = "LAPORAN ABSENSI SISWA\n";
$content .= "Tanggal: " . date('d-m-Y', strtotime($tanggal)) . "\n\n";



// Display non-present students first
$no = 1;
while($row = $result->fetch_assoc()) {
    if(in_array($row['status'], ['Alpha', 'Sakit', 'Izin'])) {
        if($no === 1) {
            $content .= str_pad("No", 5) . str_pad("NIS", 15) . str_pad("Nama", 40) . 
                       str_pad("Kelas", 10) . str_pad("Status", 15) . 
                       "Keterangan\n";
            $content .= str_repeat("-", 110) . "\n";
        }
        $content .= str_pad($no++, 5) . 
                   str_pad($row['nis'], 15) . 
                   str_pad($row['nama'], 40) . 
                   str_pad($row['kelas'], 10) . 
                   str_pad($row['status'], 15) . 
                   $row['keterangan'] . "\n";
    }
}
// Add summary section for non-present students
$content .= "DAFTAR KETIDAKHADIRAN:\n";
$content .= str_repeat("=", 110) . "\n";
// Add section for complete attendance list
$content .= "\n\nDAFTAR LENGKAP ABSENSI:\n";
$content .= str_repeat("=", 110) . "\n";
$content .= str_pad("No", 5) . str_pad("NIS", 15) . str_pad("Nama", 40) . 
           str_pad("Kelas", 10) . str_pad("Jam Masuk", 15) . 
           str_pad("Status", 15) . "Keterangan\n";
$content .= str_repeat("-", 110) . "\n";

// Reset result pointer and counter
$result->data_seek(0);
$no = 1;

// Display all students
while($row = $result->fetch_assoc()) {
    $content .= str_pad($no++, 5) . 
                str_pad($row['nis'], 15) . 
                str_pad($row['nama'], 40) . 
                str_pad($row['kelas'], 10) . 
                str_pad($row['jam_masuk'], 15) . 
                str_pad($row['status'], 15) . 
                $row['keterangan'] . "\n";
}

// Set headers for download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="Laporan_Absensi_'.date('d-m-Y', strtotime($tanggal)).'.txt"');

// Output the content
echo $content;
