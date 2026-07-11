<?php
// * Transaksi penjualan mengurangi stok.

require_once 'app.php';
require_login();
// ! Cek CSRF.
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];

    if ($aksi === 'tambah') {
        $tanggal = $_POST['tanggal'];
        $idPelanggan = (int) $_POST['id_pelanggan'];
        $idBarang = (int) $_POST['id_barang'];
        $qty = (int) $_POST['qty'];

        if ($qty < 1) {
            set_flash('danger', 'Jumlah barang tidak valid.');
            redirect('penjualan.php');
        }

        // ! Transaksi agar stok konsisten.
        mysqli_begin_transaction($conn);
        try {
            // ! Lock stok agar tidak minus.
            $stmt = mysqli_prepare($conn, 'SELECT * FROM barang WHERE id_barang = ? FOR UPDATE');
            mysqli_stmt_bind_param($stmt, 'i', $idBarang);
            mysqli_stmt_execute($stmt);
            $barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            if (!$barang || (int) $barang['stok'] < $qty) {
                throw new Exception('Stok barang tidak mencukupi.');
            }

            $harga = (float) $barang['harga_jual'];
            $subtotal = $harga * $qty;

            $stmt = mysqli_prepare($conn, 'INSERT INTO penjualan (tanggal, id_pelanggan, total) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sid', $tanggal, $idPelanggan, $subtotal);
            mysqli_stmt_execute($stmt);
            $idPenjualan = mysqli_insert_id($conn);

            $stmt = mysqli_prepare($conn, 'INSERT INTO detail_penjualan (id_penjualan, id_barang, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iiidd', $idPenjualan, $idBarang, $qty, $harga, $subtotal);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, 'UPDATE barang SET stok = stok - ? WHERE id_barang = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $qty, $idBarang);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            set_flash('success', 'Transaksi penjualan berhasil disimpan.');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', $e->getMessage() ?: 'Transaksi penjualan gagal disimpan.');
        }
    }

    if ($aksi === 'hapus') {
        $idPenjualan = (int) $_POST['id_penjualan'];
        mysqli_begin_transaction($conn);
        try {
            $details = fetch_all('SELECT * FROM detail_penjualan WHERE id_penjualan = ' . $idPenjualan);
            foreach ($details as $detail) {
                $stmt = mysqli_prepare($conn, 'UPDATE barang SET stok = stok + ? WHERE id_barang = ?');
                mysqli_stmt_bind_param($stmt, 'ii', $detail['qty'], $detail['id_barang']);
                mysqli_stmt_execute($stmt);
            }

            $stmt = mysqli_prepare($conn, 'DELETE FROM penjualan WHERE id_penjualan = ?');
            mysqli_stmt_bind_param($stmt, 'i', $idPenjualan);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            set_flash('success', 'Transaksi penjualan berhasil dihapus dan stok dikembalikan.');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', 'Transaksi penjualan gagal dihapus.');
        }
    }

    redirect('penjualan.php');
}

$pelanggans = fetch_all('SELECT * FROM pelanggan ORDER BY nama_pelanggan ASC');
$barangs = fetch_all('SELECT * FROM barang ORDER BY nama_barang ASC');
$penjualans = fetch_all("SELECT p.*, pl.nama_pelanggan FROM penjualan p LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan ORDER BY p.id_penjualan DESC");
render_header('Penjualan', 'penjualan');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Transaksi Penjualan</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Penjualan</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th width="170">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($penjualans as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= tanggal($row['tanggal']); ?></td>
            <td><?= e($row['nama_pelanggan'] ?: 'Umum'); ?></td>
            <td><?= rupiah($row['total']); ?></td>
            <td>
              <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetail<?= $row['id_penjualan']; ?>"><i class="fas fa-eye"></i> Detail</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_penjualan']; ?>"><i class="fas fa-trash"></i> Hapus</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="tambah">
      <div class="modal-header"><h4 class="modal-title">Tambah Penjualan</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
        <div class="form-group">
          <label>Pelanggan</label>
          <select name="id_pelanggan" class="form-control" required>
            <?php foreach ($pelanggans as $pelanggan): ?><option value="<?= $pelanggan['id_pelanggan']; ?>"><?= e($pelanggan['nama_pelanggan']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Barang</label>
          <select name="id_barang" class="form-control" required>
            <?php foreach ($barangs as $barang): ?><option value="<?= $barang['id_barang']; ?>"><?= e($barang['nama_barang']); ?> - Stok <?= e($barang['stok']); ?> - <?= rupiah($barang['harga_jual']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Jumlah</label><input type="number" name="qty" class="form-control" min="1" value="1" required></div>
        <small class="text-muted">Form sederhana ini mencatat satu barang per transaksi.</small>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($penjualans as $row): $details = fetch_all('SELECT d.*, b.kode_barang, b.nama_barang FROM detail_penjualan d JOIN barang b ON d.id_barang = b.id_barang WHERE d.id_penjualan = ' . (int) $row['id_penjualan']); ?>
<div class="modal fade" id="modalDetail<?= $row['id_penjualan']; ?>">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Detail Penjualan #<?= $row['id_penjualan']; ?></h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <table class="table table-bordered">
          <thead><tr><th>Kode</th><th>Barang</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
          <tbody>
            <?php foreach ($details as $detail): ?>
              <tr><td><?= e($detail['kode_barang']); ?></td><td><?= e($detail['nama_barang']); ?></td><td><?= e($detail['qty']); ?></td><td><?= rupiah($detail['harga']); ?></td><td><?= rupiah($detail['subtotal']); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button></div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalHapus<?= $row['id_penjualan']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_penjualan" value="<?= $row['id_penjualan']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus Penjualan</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus transaksi penjualan tanggal <strong><?= tanggal($row['tanggal']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
