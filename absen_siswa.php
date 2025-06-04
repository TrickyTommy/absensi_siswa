<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

// Set timezone for Indonesia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete = $conn->prepare("DELETE FROM absensis WHERE id = ?");
    $delete->bind_param("i", $id);
    
    if ($delete->execute()) {
        $_SESSION['success'] = "Data absensi berhasil dihapus";
    } else {
        $_SESSION['error'] = "Gagal menghapus data";
    }
    header("Location: absen_siswa.php");
    exit;
}

// Handle Update
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    
    $update = $conn->prepare("UPDATE absensis SET status = ?, keterangan = ? WHERE id = ?");
    $update->bind_param("ssi", $status, $keterangan, $id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Data absensi berhasil diupdate";
    } else {
        $_SESSION['error'] = "Gagal mengupdate data";
    }
    header("Location: absen_siswa.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update'])) {
    $nis = $conn->real_escape_string($_POST['nis']);
    $status = $conn->real_escape_string($_POST['status']);
    $keterangan = $conn->real_escape_string($_POST['keterangan']);
    
    // Get student ID from NIS
    $stmt = $conn->prepare("SELECT id FROM siswas WHERE nis = ?");
    $stmt->bind_param("s", $nis);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($siswa = $result->fetch_assoc()) {
        $siswa_id = $siswa['id'];
        $tanggal = date('Y-m-d');
        $jam_masuk = date('H:i:s'); // This will now use the correct timezone
        
        // Check if student already has attendance for today
        $check = $conn->prepare("SELECT id FROM absensis WHERE siswa_id = ? AND tanggal = ?");
        $check->bind_param("is", $siswa_id, $tanggal);
        $check->execute();
        $existing = $check->get_result();
        
        if ($existing->num_rows > 0) {
            $_SESSION['error'] = "Siswa sudah diabsen hari ini";
        } else {
            // Get student name
            $stmt = $conn->prepare("SELECT nama FROM siswas WHERE id = ?");
            $stmt->bind_param("i", $siswa_id);
            $stmt->execute();
            $nama_result = $stmt->get_result();
            $nama_siswa = $nama_result->fetch_assoc()['nama'];

            $insert = $conn->prepare("INSERT INTO absensis (siswa_id, tanggal, jam_masuk, status, keterangan) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("issss", $siswa_id, $tanggal, $jam_masuk, $status, $keterangan);
            
            if ($insert->execute()) {
          $_SESSION['success'] = "Absensi untuk $nama_siswa berhasil dicatat pada jam " . $jam_masuk;
            } else {
          $_SESSION['error'] = "Gagal mencatat absensi";
            }
        }
          } else {
        $_SESSION['error'] = "NIS tidak ditemukan";
          }
          
          header("Location: absen_siswa.php");
          exit;
}

// Get filter values
$current_date = date('Y-m-d');
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : $current_date;
$filter_month = isset($_GET['filter_month']) ? $_GET['filter_month'] : '';
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';

// Get unique classes for filter dropdown
$kelas_query = "SELECT DISTINCT kelas FROM siswas ORDER BY kelas";
$kelas_result = $conn->query($kelas_query);

// Modify the main query to include kelas and default to current date, ordered by status
$query = "SELECT a.*, s.nama, s.nis, s.kelas
          FROM absensis a
          LEFT JOIN siswas s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = '$filter_date'";

// Add class filter condition
if ($filter_kelas) {
    $query .= " AND s.kelas = '$filter_kelas'";
}

// Remove the date filter condition since it's now in the base query
if ($filter_month) {
    $query = str_replace("WHERE DATE(a.tanggal) = '$filter_date'", "WHERE DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_month'", $query);
}

// Order by status with custom ordering to show absent students first
$query .= " ORDER BY 
           CASE 
               WHEN a.status = 'Alpha' THEN 1
               WHEN a.status = 'Sakit' THEN 2
               WHEN a.status = 'Izin' THEN 3
               WHEN a.status = 'Hadir' THEN 4
           END, 
           s.kelas, 
           a.jam_masuk DESC";
$result = $conn->query($query);

// Get attendance statistics
$stats_query = "SELECT 
    COUNT(DISTINCT s.id) as total_siswa,
    SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
    SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
    SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
    SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as total_alpha
    FROM siswas s
    LEFT JOIN absensis a ON s.id = a.siswa_id AND 1=1";

// Add date filter if specified
if ($filter_date) {
    $stats_query .= " AND DATE(a.tanggal) = '$filter_date'";
}

// Add month filter if specified
if ($filter_month) {
    $stats_query .= " AND DATE_FORMAT(a.tanggal, '%Y-%m') = '$filter_month'";
}

// Add class filter if specified
if ($filter_kelas) {
    $stats_query .= " WHERE s.kelas = '$filter_kelas'";
}

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
// Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Absensi Siswa</title>
    <link rel="stylesheet" href="assets/AdminLTE/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/AdminLTE/dist/css/adminlte.min.css">
    <style>
    .main-header, .main-sidebar, .brand-link, .nav-sidebar>.nav-item>.nav-link.active {
        background-color:rgb(254, 207, 2) !important; /* orange */
    }
    .content-wrapper {
      background-color: #fffaf3;
    }
    .nav-sidebar>.nav-item>.nav-link {
      color: white;
    }
    .nav-sidebar>.nav-item>.nav-link.active {
      font-weight: bold;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

 
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  <!-- Content -->
  <div class="content-wrapper p-4">
    <div class="content-header">
      <h1>Data Absensi Siswa</h1>
      
    </div>
    
    <!-- Add Input Form -->
    <div class="card mb-4"  >
      <h2 style="margin-left:20px; margin-top:20px;">Absen Siswa Dengan Barcode</h2>
      <div class="card-body">
      <form method="POST" class="row g-3" id="barcodeForm">
        <div class="col-md-3">
        <label class="form-label">NIS Siswa</label>
        <input type="text" name="nis" class="form-control" required autofocus id="barcodeInput">
        <input type="hidden" name="status" value="Hadir">
        </div>
        <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100">Simpan Absen</button>
        </div>
        <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100" >
          <a href="tambah_absen.php" style="color:white; width:100%;">Tambah Absen Manual</a>
        </button>
        </div>
      </form>
      </div>
    </div>

    <!-- Add Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Filter per Hari</label>
            <input type="date" name="filter_date" class="form-control" value="<?= $filter_date ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Filter per Bulan</label>
            <input type="month" name="filter_month" class="form-control" value="<?= $filter_month ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Filter Kelas</label>
            <select name="filter_kelas" class="form-control">
              <option value="">Semua Kelas</option>
              <?php while($kelas = $kelas_result->fetch_assoc()): ?>
                <option value="<?= $kelas['kelas'] ?>" <?= $filter_kelas === $kelas['kelas'] ? 'selected' : '' ?>>
                  <?= $kelas['kelas'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">&nbsp;</label>
            <div>
              <button type="submit" class="btn btn-primary">Filter</button>
              <a href="absen_siswa.php" class="btn btn-secondary">Reset</a>
              <?php if($filter_date): ?>
                <a href="generate_txt.php?tanggal=<?= $filter_date ?>" class="btn btn-info">
                  <i class="fas fa-file-alt"></i> Export TXT
                </a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?= $stats['total_siswa'] ?? 0 ?></h3>
                    <p>Total Siswa <?= $filter_kelas ? "Kelas " . $filter_kelas : "" ?></p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?= $stats['total_hadir'] ?? 0 ?></h3>
                    <p>Total Hadir</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?= $stats['total_sakit'] ?? 0 ?></h3>
                    <p>Total Sakit</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?= $stats['total_izin'] ?? 0 ?></h3>
                    <p>Total Izin</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?= $stats['total_alpha'] ?? 0 ?></h3>
                    <p>Total Alpha</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
      <table class="table table-bordered table-hover bg-white">
        <thead class="thead-dark">
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          // In the table display section, add status color highlighting
          while ($row = $result->fetch_assoc()) {
              $status_color = '';
              switch($row['status']) {
                  case 'Alpha': $status_color = 'table-danger'; break;
                  case 'Sakit': $status_color = 'table-warning'; break;
                  case 'Izin': $status_color = 'table-info'; break;
              }
              
              echo "<tr class='{$status_color}'>
                  <td>" . $no++ . "</td>
                  <td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>
                  <td>{$row['jam_masuk']}</td>
                  <td>{$row['nama']}</td>
                  <td>{$row['kelas']}</td>
                  <td>{$row['status']}</td>
                  <td>{$row['keterangan']}</td>
                  <td>
                      <a href='edit_absen.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                      <a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                  </td>
              </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer class="main-footer text-center">
    <strong>Copyright &copy; 2025 SMK.</strong>
  </footer>

</div>

<!-- Scripts -->
<script src="assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
<script src="assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/AdminLTE/dist/js/adminlte.min.js"></script>
<!-- <script>
  // Ensure focus stays on the barcode input field
  document.addEventListener('DOMContentLoaded', function() {
    const barcodeInput = document.getElementById('barcodeInput');
    
    // Focus on page load
    barcodeInput.focus();
    
    // Focus after form submission
    document.getElementById('barcodeForm').addEventListener('submit', function() {
      setTimeout(function() {
        barcodeInput.focus();
      }, 100);
    });
    
    // Refocus when focus is lost
    document.addEventListener('click', function() {
      barcodeInput.focus();
    });
  });
</script> -->
</body>
</html>
