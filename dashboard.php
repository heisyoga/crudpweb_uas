<?php
// * Dashboard ringkasan toko.

require_once 'app.php';
require_login();

$totalBarang = count_table('barang');
$totalSupplier = count_table('supplier');
$totalPelanggan = count_table('pelanggan');
$totalPenjualan = count_table('penjualan');
$totalPembelian = count_table('pembelian');

$omzet = fetch_one("SELECT COALESCE(SUM(total), 0) AS total FROM penjualan");
$biaya = fetch_one("SELECT COALESCE(SUM(total), 0) AS total FROM pembelian");
$barangTerbaru = fetch_all("SELECT * FROM barang ORDER BY id_barang DESC LIMIT 5");
$penjualanTerbaru = fetch_all("SELECT p.*, pl.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan ORDER BY p.id_penjualan DESC LIMIT 5");

render_header('Dashboard', 'dashboard');
?>
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= $totalBarang; ?></h3><p>Data Barang</p></div>
      <div class="icon"><i class="fas fa-box"></i></div>
      <a href="barang.php" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3><?= $totalPelanggan; ?></h3><p>Pelanggan</p></div>
      <div class="icon"><i class="fas fa-users"></i></div>
      <a href="pelanggan.php" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= $totalSupplier; ?></h3><p>Supplier</p></div>
      <div class="icon"><i class="fas fa-truck"></i></div>
      <a href="supplier.php" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
      <div class="inner"><h3><?= $totalPenjualan; ?></h3><p>Transaksi Penjualan</p></div>
      <div class="icon"><i class="fas fa-shopping-cart"></i></div>
      <a href="penjualan.php" class="small-box-footer">Lihat detail <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Ringkasan Keuangan</h3></div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-5">Total Penjualan</dt><dd class="col-sm-7 text-success font-weight-bold"><?= rupiah($omzet['total']); ?></dd>
          <dt class="col-sm-5">Total Pembelian</dt><dd class="col-sm-7 text-danger font-weight-bold"><?= rupiah($biaya['total']); ?></dd>
          <dt class="col-sm-5">Jumlah Pembelian</dt><dd class="col-sm-7"><?= $totalPembelian; ?> transaksi</dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card card-secondary">
      <div class="card-header"><h3 class="card-title">Barang Terbaru</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0">
          <thead><tr><th>Kode</th><th>Nama</th><th>Stok</th><th>Harga Jual</th></tr></thead>
          <tbody>
            <?php foreach ($barangTerbaru as $barang): ?>
              <tr>
                <td><?= e($barang['kode_barang']); ?></td>
                <td><?= e($barang['nama_barang']); ?></td>
                <td><?= e($barang['stok']); ?></td>
                <td><?= rupiah($barang['harga_jual']); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$barangTerbaru): ?><tr><td colspan="4" class="text-center">Belum ada data</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">Penjualan Terbaru</h3></div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped">
      <thead><tr><th>Tanggal</th><th>Pelanggan</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($penjualanTerbaru as $row): ?>
          <tr>
            <td><?= tanggal($row['tanggal']); ?></td>
            <td><?= e($row['nama_pelanggan'] ?: 'Umum'); ?></td>
            <td><?= rupiah($row['total']); ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$penjualanTerbaru): ?><tr><td colspan="3" class="text-center">Belum ada transaksi</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php render_footer(); ?>
