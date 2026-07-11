<?php
// * Laporan penjualan dan pembelian.

require_once 'app.php';
require_login();

// ? Validasi format tanggal.
function valid_date_or_default($value, $default)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if ($date && $date->format('Y-m-d') === $value) {
        return $value;
    }

    return $default;
}

$tanggalAwal = valid_date_or_default(isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '', date('Y-m-01'));
$tanggalAkhir = valid_date_or_default(isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '', date('Y-m-d'));

$stmt = mysqli_prepare($conn, "SELECT p.*, pl.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan WHERE p.tanggal BETWEEN ? AND ? ORDER BY p.tanggal DESC, p.id_penjualan DESC");
mysqli_stmt_bind_param($stmt, 'ss', $tanggalAwal, $tanggalAkhir);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$penjualans = array();
while ($row = mysqli_fetch_assoc($result)) {
    $penjualans[] = $row;
}

$stmt = mysqli_prepare($conn, "SELECT p.*, s.nama_supplier FROM pembelian p LEFT JOIN supplier s ON p.id_supplier = s.id_supplier WHERE p.tanggal BETWEEN ? AND ? ORDER BY p.tanggal DESC, p.id_pembelian DESC");
mysqli_stmt_bind_param($stmt, 'ss', $tanggalAwal, $tanggalAkhir);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pembelians = array();
while ($row = mysqli_fetch_assoc($result)) {
    $pembelians[] = $row;
}

$stmt = mysqli_prepare($conn, 'SELECT COALESCE(SUM(total), 0) AS total FROM penjualan WHERE tanggal BETWEEN ? AND ?');
mysqli_stmt_bind_param($stmt, 'ss', $tanggalAwal, $tanggalAkhir);
mysqli_stmt_execute($stmt);
$totalPenjualan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare($conn, 'SELECT COALESCE(SUM(total), 0) AS total FROM pembelian WHERE tanggal BETWEEN ? AND ?');
mysqli_stmt_bind_param($stmt, 'ss', $tanggalAwal, $tanggalAkhir);
mysqli_stmt_execute($stmt);
$totalPembelian = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$labaKotor = (float) $totalPenjualan['total'] - (float) $totalPembelian['total'];

render_header('Laporan', 'laporan');
?>
<div class="card card-primary">
  <div class="card-header"><h3 class="card-title">Filter Laporan</h3></div>
  <form method="get">
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <div class="form-group"><label>Tanggal Awal</label><input type="date" name="tanggal_awal" class="form-control" value="<?= e($tanggalAwal); ?>"></div>
        </div>
        <div class="col-md-5">
          <div class="form-group"><label>Tanggal Akhir</label><input type="date" name="tanggal_akhir" class="form-control" value="<?= e($tanggalAkhir); ?>"></div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <div class="form-group w-100"><button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Tampilkan</button></div>
        </div>
      </div>
    </div>
  </form>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="info-box bg-success">
      <span class="info-box-icon"><i class="fas fa-shopping-cart"></i></span>
      <div class="info-box-content"><span class="info-box-text">Total Penjualan</span><span class="info-box-number"><?= rupiah($totalPenjualan['total']); ?></span></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-box bg-danger">
      <span class="info-box-icon"><i class="fas fa-truck"></i></span>
      <div class="info-box-content"><span class="info-box-text">Total Pembelian</span><span class="info-box-number"><?= rupiah($totalPembelian['total']); ?></span></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-box bg-info">
      <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
      <div class="info-box-content"><span class="info-box-text">Selisih</span><span class="info-box-number"><?= rupiah($labaKotor); ?></span></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Laporan Penjualan</h3></div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($penjualans as $row): ?>
          <tr><td><?= $no++; ?></td><td><?= tanggal($row['tanggal']); ?></td><td><?= e($row['nama_pelanggan'] ?: 'Umum'); ?></td><td><?= rupiah($row['total']); ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Laporan Pembelian</h3></div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Tanggal</th><th>Supplier</th><th>Total</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($pembelians as $row): ?>
          <tr><td><?= $no++; ?></td><td><?= tanggal($row['tanggal']); ?></td><td><?= e($row['nama_supplier']); ?></td><td><?= rupiah($row['total']); ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php render_footer(); ?>
