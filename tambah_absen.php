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
    $nama_siswa = $conn->real_escape_string($_POST['nama_siswa']);
    $status = isset($_POST['status']) ? $conn->real_escape_string($_POST['status']) : 'Hadir';
    $keterangan = isset($_POST['keterangan']) ? $conn->real_escape_string($_POST['keterangan']) : '';
    
    // Validate if nama_siswa exists in database with COLLATE for case-insensitive comparison
    $stmt = $conn->prepare("SELECT id FROM siswas WHERE nama COLLATE utf8mb4_general_ci = ?");
    $stmt->bind_param("s", $nama_siswa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Add debugging
    if ($result === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: absen_siswa.php");
        exit;
    }

    if ($result->num_rows === 0) {
        // Add debugging information
        $_SESSION['error'] = "Nama siswa tidak ditemukan: '$nama_siswa'. Pastikan nama sesuai dengan daftar yang tersedia.";
        header("Location: absen_siswa.php");
        exit;
    }

    // Get student ID from nama
    $siswa = $result->fetch_assoc();
    $siswa_id = $siswa['id'];
    
    // Check if manual date is provided, otherwise use system date
    $tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
    $jam_masuk = date('H:i:s');
    
    // Check if student already has attendance for the selected date
    $check = $conn->prepare("SELECT id FROM absensis WHERE siswa_id = ? AND tanggal = ?");
    $check->bind_param("is", $siswa_id, $tanggal);
    $check->execute();
    $existing = $check->get_result();
    
    if ($existing->num_rows > 0) {
        $_SESSION['error'] = "Siswa sudah diabsen pada tanggal tersebut";
    } else {
        $insert = $conn->prepare("INSERT INTO absensis (siswa_id, tanggal, jam_masuk, status, keterangan) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issss", $siswa_id, $tanggal, $jam_masuk, $status, $keterangan);
        
        if ($insert->execute()) {
            $_SESSION['success'] = "Absensi berhasil dicatat pada tanggal " . date('d-m-Y', strtotime($tanggal));
        } else {
            $_SESSION['error'] = "Gagal mencatat absensi";
        }
    }
    header("Location: absen_siswa.php");
    exit;
}
//batas code
// Regular query for displaying data
$today = date('Y-m-d');
$query = "SELECT a.*, s.nama, s.nis
          FROM absensis a
          LEFT JOIN siswas s ON a.siswa_id = s.id 
          WHERE DATE(a.tanggal) = '$today'
          ORDER BY a.jam_masuk DESC";
$result = $conn->query($query);

// Get attendance statistics for today only
$stats_query = "SELECT 
    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
    SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
    SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
    SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as total_alpha
    FROM absensis 
    WHERE DATE(tanggal) = '$today'";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Add this code after database connection and before form handling
$query_siswa = "SELECT nama FROM siswas ORDER BY nama ASC";
$result_siswa = $conn->query($query_siswa);
$daftar_siswa = [];
while($row = $result_siswa->fetch_assoc()) {
    $daftar_siswa[] = $row['nama'];
}

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

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Logout</a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-orange elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-light">SMK Dashboard</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item">
            <a href="index.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="list-siswa.php" class="nav-link ">
              <i class="nav-icon fas fa-users"></i>
              <p>Daftar Siswa</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="absen_siswa.php" class="nav-link active">
              <i class="nav-icon fas fa-users"></i>
              <p>Absensi Siswa</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper p-4">
    <div class="content-header">
      <h1>Data Absensi Siswa</h1>
    </div>
    
    <!-- Add Input Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Nama Siswa</label>
            <input type="text" name="nama_siswa" class="form-control" list="daftarSiswa" required autocomplete="off">
            <datalist id="daftarSiswa">
                <?php foreach($daftar_siswa as $nama): ?>
                    <option value="<?php echo htmlspecialchars($nama); ?>">
                <?php endforeach; ?>
            </datalist>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
              <option value="Hadir">Hadir</option>
              <option value="Sakit">Sakit</option>
              <option value="Izin">Izin</option>
              <option value="Alpha">Alpha</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
          </div>
          <div class="col-md-5">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Simpan Absen</button>
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
            <th>Status</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                  <td>" . $no++ . "</td>
                  <td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>
                  <td>{$row['jam_masuk']}</td>
                  <td>{$row['nama']}</td>
                  <td>
                    <form method='POST' style='display:inline;'>
                      <input type='hidden' name='id' value='{$row['id']}'>
                      <select name='status' class='form-control form-control-sm' style='width:auto;'>
                        <option value='Hadir' ".($row['status'] == 'Hadir' ? 'selected' : '').">Hadir</option>
                        <option value='Sakit' ".($row['status'] == 'Sakit' ? 'selected' : '').">Sakit</option>
                        <option value='Izin' ".($row['status'] == 'Izin' ? 'selected' : '').">Izin</option>
                        <option value='Alpha' ".($row['status'] == 'Alpha' ? 'selected' : '').">Alpha</option>
                      </select>
                  </td>
                  <td>
                      <input type='text' name='keterangan' value='{$row['keterangan']}' class='form-control form-control-sm'>
                  </td>
                  <td>
                      <button type='submit' name='update' class='btn btn-sm btn-warning'>Update</button>
                    </form>
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
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var input = document.querySelector('input[name="nama_siswa"]');
    var datalist = document.getElementById('daftarSiswa');
    var valid = false;
    
    for(var i = 0; i < datalist.options.length; i++) {
        if(input.value === datalist.options[i].value) {
            valid = true;
            break;
        }
    }
    
    if(!valid) {
        e.preventDefault();
        alert('Pilih nama siswa dari daftar yang tersedia');
    }
});
</script>
</body>
</html>
