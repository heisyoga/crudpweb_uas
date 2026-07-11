<?php
// * Transaksi pembelian menambah stok.

require_once 'app.php';
require_login();
// ! Cek CSRF.
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];

    if ($aksi === 'tambah') {
        $tanggal = $_POST['tanggal'];
        $idSupplier = (int) $_POST['id_supplier'];
        $idBarang = (int) $_POST['id_barang'];
        $qty = (int) $_POST['qty'];
        $harga = (float) $_POST['harga'];
        $subtotal = $harga * $qty;

        if ($qty < 1 || $harga < 1) {
            set_flash('danger', 'Jumlah dan harga harus lebih dari nol.');
            redirect('pembelian.php');
        }

        // ! Transaksi agar stok konsisten.
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, 'INSERT INTO pembelian (tanggal, id_supplier, total) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sid', $tanggal, $idSupplier, $subtotal);
            mysqli_stmt_execute($stmt);
            $idPembelian = mysqli_insert_id($conn);

            $stmt = mysqli_prepare($conn, 'INSERT INTO detail_pembelian (id_pembelian, id_barang, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iiidd', $idPembelian, $idBarang, $qty, $harga, $subtotal);
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($conn, 'UPDATE barang SET stok = stok + ?, harga_beli = ? WHERE id_barang = ?');
            mysqli_stmt_bind_param($stmt, 'idi', $qty, $harga, $idBarang);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            set_flash('success', 'Transaksi pembelian berhasil disimpan.');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', 'Transaksi pembelian gagal disimpan.');
        }
    }

    // ! Cek stok sebelum hapus pembelian.
    if ($aksi === 'hapus') {
        $idPembelian = (int) $_POST['id_pembelian'];
        mysqli_begin_transaction($conn);
        try {
            $details = fetch_all('SELECT * FROM detail_pembelian WHERE id_pembelian = ' . $idPembelian);
            foreach ($details as $detail) {
                $stmt = mysqli_prepare($conn, 'SELECT stok FROM barang WHERE id_barang = ? FOR UPDATE');
                mysqli_stmt_bind_param($stmt, 'i', $detail['id_barang']);
                mysqli_stmt_execute($stmt);
                $barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if (!$barang || (int) $barang['stok'] < (int) $detail['qty']) {
                    throw new Exception('Pembelian tidak bisa dihapus karena stok barang sudah terpakai.');
                }
            }

            foreach ($details as $detail) {
                $stmt = mysqli_prepare($conn, 'UPDATE barang SET stok = stok - ? WHERE id_barang = ?');
                mysqli_stmt_bind_param($stmt, 'ii', $detail['qty'], $detail['id_barang']);
                mysqli_stmt_execute($stmt);
            }

            $stmt = mysqli_prepare($conn, 'DELETE FROM pembelian WHERE id_pembelian = ?');
            mysqli_stmt_bind_param($stmt, 'i', $idPembelian);
            mysqli_stmt_execute($stmt);

            mysqli_commit($conn);
            set_flash('success', 'Transaksi pembelian berhasil dihapus dan stok dikurangi.');
        } catch (Exception $e) {
            mysqli_rollback($conn);
            set_flash('danger', $e->getMessage() ?: 'Transaksi pembelian gagal dihapus.');
        }
    }

    redirect('pembelian.php');
}

$suppliers = fetch_all('SELECT * FROM supplier ORDER BY nama_supplier ASC');
$barangs = fetch_all('SELECT * FROM barang ORDER BY nama_barang ASC');
$pembelians = fetch_all("SELECT p.*, s.nama_supplier FROM pembelian p LEFT JOIN supplier s ON p.id_supplier = s.id_supplier ORDER BY p.id_pembelian DESC");
render_header('Pembelian', 'pembelian');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Transaksi Pembelian</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Pembelian</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Tanggal</th><th>Supplier</th><th>Total</th><th width="170">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($pembelians as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= tanggal($row['tanggal']); ?></td>
            <td><?= e($row['nama_supplier']); ?></td>
            <td><?= rupiah($row['total']); ?></td>
            <td>
              <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetail<?= $row['id_pembelian']; ?>"><i class="fas fa-eye"></i> Detail</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_pembelian']; ?>"><i class="fas fa-trash"></i> Hapus</button>
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
      <div class="modal-header"><h4 class="modal-title">Tambah Pembelian</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required></div>
        <div class="form-group"><label>Supplier</label><select name="id_supplier" class="form-control" required><?php foreach ($suppliers as $supplier): ?><option value="<?= $supplier['id_supplier']; ?>"><?= e($supplier['nama_supplier']); ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Barang</label><select name="id_barang" class="form-control" required><?php foreach ($barangs as $barang): ?><option value="<?= $barang['id_barang']; ?>"><?= e($barang['nama_barang']); ?> - Stok <?= e($barang['stok']); ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Jumlah</label><input type="number" name="qty" class="form-control" min="1" value="1" required></div>
        <div class="form-group"><label>Harga Beli</label><input type="number" name="harga" class="form-control" min="1" required></div>
        <small class="text-muted">Form sederhana ini mencatat satu barang per transaksi.</small>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($pembelians as $row): $details = fetch_all('SELECT d.*, b.kode_barang, b.nama_barang FROM detail_pembelian d JOIN barang b ON d.id_barang = b.id_barang WHERE d.id_pembelian = ' . (int) $row['id_pembelian']); ?>
<div class="modal fade" id="modalDetail<?= $row['id_pembelian']; ?>">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Detail Pembelian #<?= $row['id_pembelian']; ?></h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
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
<div class="modal fade" id="modalHapus<?= $row['id_pembelian']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_pembelian" value="<?= $row['id_pembelian']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus Pembelian</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus transaksi pembelian tanggal <strong><?= tanggal($row['tanggal']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
