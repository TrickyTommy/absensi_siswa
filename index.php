<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = is_array($_SESSION['user']) ? $_SESSION['user'] : ['name' => $_SESSION['user']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home Page</title>
  <!-- AdminLTE -->
  <link rel="stylesheet" href="assets/AdminLTE/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/AdminLTE/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="assets/AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Custom Orange Theme -->
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
      <h1>Selamat Datang, <?php echo htmlspecialchars($user['name']); ?>!</h1>
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
