<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php'; // pastikan file koneksi database

// Define filter options
$jurusan_options = [
    'RPL' => 'Rekayasa Perangkat Lunak',
    'TKJ' => 'Teknik Komputer dan Jaringan',
    'AKL' => 'Akuntansi dan Keuangan Lembaga'
];

// Get unique kelas values from database
$kelas_query = "SELECT DISTINCT kelas FROM siswas ORDER BY kelas ASC";
$kelas_result = $conn->query($kelas_query);
$kelas_options = [];
while($row = $kelas_result->fetch_assoc()) {
    $kelas_options[] = $row['kelas'];
}

// Get filter values
$filter_jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';
$filter_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

// Build query with filters
$query = "SELECT * FROM siswas WHERE 1=1";
if (!empty($filter_jurusan)) {
    $query .= " AND jurusan = '" . $conn->real_escape_string($filter_jurusan) . "'";
}
if (!empty($filter_kelas)) {
    $query .= " AND kelas = '" . $conn->real_escape_string($filter_kelas) . "'";
}
$query .= " ORDER BY id ASC";

// Execute single query for results
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daftar Siswa</title>
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
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="list-siswa.php" class="nav-link active">
              <i class="nav-icon fas fa-users"></i>
              <p>Daftar Siswa</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="absen_siswa.php" class="nav-link ">
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
    <!-- Add Filter Form -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Filter Jurusan</label>
                    <select name="jurusan" class="form-control">
                        <option value="">Semua Jurusan</option>
                        <?php foreach($jurusan_options as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= ($filter_jurusan == $kode) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nama) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Filter Kelas</label>
                    <select name="kelas" class="form-control">
                        <option value="">Semua Kelas</option>
                        <?php foreach($kelas_options as $kelas): ?>
                <option value="<?= htmlspecialchars($kelas) ?>" <?= ($filter_kelas == $kelas) ? 'selected' : '' ?>>
                    Kelas <?= htmlspecialchars($kelas) ?>
                </option>
            <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="list-siswa.php" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
    <div class="content-header d-flex justify-content-between align-items-center">

      <h1>Data Siswa</h1>
      <a href="tambah-siswa.php" class="btn btn-warning ">
        <i class="fas fa-plus"></i>Tambah Siswa</a>
    </div>
    <div class="content">
    <form method="POST" action="cetak-pilih-kartu.php" target="_blank">
    <button type="submit" class="btn btn-primary mb-3">Cetak Kartu yang Dipilih</button>
      <table class="table table-bordered table-hover bg-white">
        <thead class="thead-dark">
          <tr>
          <th><input type="checkbox" id="checkAll"></th>
            <th>#</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Jurusan</th>
            <th>Tanggal Input</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($row = $result->fetch_assoc()) {
              echo "<tr>

               <td><input type='checkbox' name='ids[]' value='{$row['id']}'></td>
               
                <td>{$no}</td>
                <td>{$row['nis']}</td>
                <td>{$row['nama']}</td>
                <td>{$row['kelas']}</td>
                <td>{$row['jurusan']}</td>
                <td>{$row['created_at']}</td>
                <td>
                    <a href='edit-siswa.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='hapus-siswa.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin ingin menghapus?')\">Hapus</a>
                    <a href='cetak-kartu.php?id={$row['id']}' class='btn btn-sm btn-primary' target='_blank'>Cetak Kartu</a>
                </td>
              </tr>";
              $no++;
          }
          ?>
        </tbody>
      </table>
    </form>
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
<!-- Script untuk centang semua -->
<script>
document.getElementById("checkAll").onclick = function() {
  let checkboxes = document.querySelectorAll('input[name="ids[]"]');
  for (let checkbox of checkboxes) {
    checkbox.checked = this.checked;
  }
};
</script>
</body>
</html>
