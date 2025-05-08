<?php
include 'koneksi.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM siswas WHERE id=$id");
$data = $result->fetch_assoc();

// Define available jurusan options
$jurusan_options = [
    'RPL' => 'Rekayasa Perangkat Lunak',
    'TKJ' => 'Teknik Komputer dan Jaringan',
    'AKL' => 'Akuntansi dan Keuangan Lembaga',
    
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];

    $stmt = $conn->prepare("UPDATE siswas SET nis=?, nama=?, kelas=?, jurusan=? WHERE id=?");
    $stmt->bind_param("ssssi", $nis, $nama, $kelas, $jurusan, $id);
    $stmt->execute();
    header("Location: list-siswa.php");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Siswa</title>
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
            <a href="list-siswa.php" class="nav-link ">
              <i class="nav-icon fas fa-users"></i>
              <p>Daftar Siswa</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
<div class="wrapper">
<div class="content-wrapper p-4">
    <div class="content-header">
        <h3>Edit Siswa</h3>
    </div>
    <div class="content">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" name="nis" class="form-control" value="<?= htmlspecialchars($data['nis']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($data['kelas']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jurusan</label>
                        <select name="jurusan" class="form-control" required>
                            <option value="">Pilih Jurusan</option>
                            <?php foreach($jurusan_options as $kode => $nama_jurusan): ?>
                                <option value="<?= $kode ?>" <?= ($data['jurusan'] == $kode) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nama_jurusan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="list-siswa.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
