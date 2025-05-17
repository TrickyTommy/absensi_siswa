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
                    <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="list-siswa.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'list-siswa.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Daftar Siswa</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="absen_siswa.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'absen_siswa.php' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Absensi Siswa</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
