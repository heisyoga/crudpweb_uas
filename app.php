<?php
// * Helper auth, CSRF, query, formatter, dan layout.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value)
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function tanggal($value)
{
    if (!$value) {
        return '-';
    }

    return date('d/m/Y', strtotime($value));
}

function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

function require_login()
{
    if (empty($_SESSION['login'])) {
        redirect('login.php');
    }
}

function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'Halaman ini hanya dapat diakses oleh admin.');
        redirect('dashboard.php');
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        if (!hash_equals(csrf_token(), $token)) {
            set_flash('danger', 'Token form tidak valid. Silakan coba lagi.');
            redirect('dashboard.php');
        }
    }
}

function current_user_name()
{
    return isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator';
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function query($sql)
{
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die('Query gagal: ' . mysqli_error($conn));
    }

    return $result;
}

function fetch_all($sql)
{
    $result = query($sql);
    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function fetch_one($sql)
{
    $result = query($sql);
    return mysqli_fetch_assoc($result);
}

function count_table($table)
{
    $allowed = array('user', 'supplier', 'pelanggan', 'barang', 'penjualan', 'pembelian');
    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $row = fetch_one('SELECT COUNT(*) AS total FROM `' . $table . '`');
    return (int) $row['total'];
}

function render_header($title, $active)
{
    $menu = array(
        'dashboard' => '',
        'user' => '',
        'supplier' => '',
        'pelanggan' => '',
        'barang' => '',
        'penjualan' => '',
        'pembelian' => '',
        'laporan' => '',
    );
    if (isset($menu[$active])) {
        $menu[$active] = 'active';
    }

    $masterOpen = in_array($active, array('user', 'supplier', 'pelanggan', 'barang'), true) ? 'menu-open' : '';
    $masterActive = $masterOpen ? 'active' : '';
    ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Project UAS | <?= e($title); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Toko Sederhana</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?= e(current_user_name()); ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $menu['dashboard']; ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item has-treeview <?= $masterOpen; ?>">
            <a href="#" class="nav-link <?= $masterActive; ?>">
              <i class="nav-icon fas fa-table"></i>
              <p>Data Master <i class="fas fa-angle-left right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <?php if (is_admin()): ?>
              <li class="nav-item"><a href="user.php" class="nav-link <?= $menu['user']; ?>"><i class="far fa-circle nav-icon"></i><p>Data User</p></a></li>
              <?php endif; ?>
              <li class="nav-item"><a href="supplier.php" class="nav-link <?= $menu['supplier']; ?>"><i class="far fa-circle nav-icon"></i><p>Data Supplier</p></a></li>
              <li class="nav-item"><a href="pelanggan.php" class="nav-link <?= $menu['pelanggan']; ?>"><i class="far fa-circle nav-icon"></i><p>Data Pelanggan</p></a></li>
              <li class="nav-item"><a href="barang.php" class="nav-link <?= $menu['barang']; ?>"><i class="far fa-circle nav-icon"></i><p>Data Barang</p></a></li>
            </ul>
          </li>
          <li class="nav-header">TRANSAKSI</li>
          <li class="nav-item">
            <a href="penjualan.php" class="nav-link <?= $menu['penjualan']; ?>">
              <i class="nav-icon fas fa-shopping-cart"></i>
              <p>Penjualan</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pembelian.php" class="nav-link <?= $menu['pembelian']; ?>">
              <i class="nav-icon fas fa-truck"></i>
              <p>Pembelian</p>
            </a>
          </li>
          <li class="nav-header">LAPORAN</li>
          <li class="nav-item">
            <a href="laporan.php" class="nav-link <?= $menu['laporan']; ?>">
              <i class="nav-icon fas fa-file"></i>
              <p>Laporan</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1><?= e($title); ?></h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active"><?= e($title); ?></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <?php $flash = get_flash(); if ($flash): ?>
          <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
        <?php endif; ?>
    <?php
}

function render_footer()
{
    ?>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block"><b>UAS</b> Pemrograman Web</div>
    <strong>CRUD Database Toko Sederhana.</strong>
  </footer>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script src="dist/js/adminlte.min.js"></script>
<script>
  $(function () {
    $('.datatable').DataTable({
      autoWidth: false,
      responsive: true
    });
  });
</script>
</body>
</html>
    <?php
}
