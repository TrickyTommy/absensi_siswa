<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    
    $sql = "INSERT INTO siswas (nis, nama, kelas, jurusan, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nis, $nama, $kelas, $jurusan);
    
    if ($stmt->execute()) {
        header("Location: list-siswa.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Siswa</title>
    <link rel="stylesheet" href="assets/AdminLTE/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="assets/AdminLTE/dist/css/adminlte.min.css">
    <style>
        .main-header, .main-sidebar, .brand-link, .nav-sidebar>.nav-item>.nav-link.active {
            background-color:rgb(254, 207, 2) !important;
        }
        .content-wrapper {
            background-color: #fffaf3;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar & Sidebar code here (copy from list-siswa.php) -->
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
        </ul>
      </nav>
    </div>
  </aside>
    
    <!-- Content -->
    <div class="content-wrapper p-4">
        <div class="content-header">
            <h1>Tambah Siswa Baru</h1>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-body">
                    <form action="tambah-siswa.php" method="POST">
                        <div class="form-group">
                            <label for="nis">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis" required>
                        </div>
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="kelas">Kelas</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" required>
                        </div>
                        <div class="form-group">
                            <label for="jurusan">Jurusan</label>
                            <input type="text" class="form-control" id="jurusan" name="jurusan" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="list-siswa.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
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
</body>
</html>