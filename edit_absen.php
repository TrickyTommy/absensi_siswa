<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $conn->real_escape_string($_POST['status']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    
    $update = $conn->prepare("UPDATE absensis SET status = ?, keterangan = ? WHERE id = ?");
    $update->bind_param("ssi", $status, $keterangan, $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Data absensi berhasil diupdate";
        header("Location: absen_siswa.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal mengupdate data";
    }
}

// Get current data
$query = "SELECT a.*, s.nama, s.nis
          FROM absensis a
          LEFT JOIN siswas s ON a.siswa_id = s.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: absen_siswa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Absensi Siswa</title>
    <link rel="stylesheet" href="assets/AdminLTE/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/AdminLTE/dist/css/adminlte.min.css">
    <style>
        .main-header, .main-sidebar { background-color:rgb(254, 207, 2) !important; }
        .content-wrapper { background-color: #fffaf3; }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper p-4">
        <div class="content-header">
            <h1>Edit Data Absensi</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Siswa</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="text" class="form-control" value="<?= date('d-m-Y', strtotime($data['tanggal'])) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Jam Masuk</label>
                        <input type="text" class="form-control" value="<?= $data['jam_masuk'] ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Hadir" <?= $data['status'] == 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                            <option value="Sakit" <?= $data['status'] == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                            <option value="Izin" <?= $data['status'] == 'Izin' ? 'selected' : '' ?>>Izin</option>
                            <option value="Alpha" <?= $data['status'] == 'Alpha' ? 'selected' : '' ?>>Alpha</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="absen_siswa.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
<script src="assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
