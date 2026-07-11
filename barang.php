<?php
// * CRUD data barang dan stok.

require_once 'app.php';
require_login();
// ! Cek CSRF.
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];
    $kode = isset($_POST['kode_barang']) ? trim($_POST['kode_barang']) : '';
    $nama = isset($_POST['nama_barang']) ? trim($_POST['nama_barang']) : '';
    $hargaBeli = isset($_POST['harga_beli']) ? (float) $_POST['harga_beli'] : 0;
    $hargaJual = isset($_POST['harga_jual']) ? (float) $_POST['harga_jual'] : 0;
    $stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    if ($aksi === 'tambah') {
        try {
            $stmt = mysqli_prepare($conn, 'INSERT INTO barang (kode_barang, nama_barang, harga_beli, harga_jual, stok) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssddi', $kode, $nama, $hargaBeli, $hargaJual, $stok);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data barang berhasil ditambahkan.');
        } catch (mysqli_sql_exception $e) {
            set_flash('danger', 'Kode barang sudah digunakan atau data tidak valid.');
        }
    }

    if ($aksi === 'edit') {
        $id = (int) $_POST['id_barang'];
        try {
            $stmt = mysqli_prepare($conn, 'UPDATE barang SET kode_barang = ?, nama_barang = ?, harga_beli = ?, harga_jual = ?, stok = ? WHERE id_barang = ?');
            mysqli_stmt_bind_param($stmt, 'ssddii', $kode, $nama, $hargaBeli, $hargaJual, $stok, $id);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data barang berhasil diperbarui.');
        } catch (mysqli_sql_exception $e) {
            set_flash('danger', 'Kode barang sudah digunakan atau data tidak valid.');
        }
    }

    // ! Tidak bisa hapus barang yang sudah dipakai transaksi.
    if ($aksi === 'hapus') {
        $id = (int) $_POST['id_barang'];
        try {
            $stmt = mysqli_prepare($conn, 'DELETE FROM barang WHERE id_barang = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data barang berhasil dihapus.');
        } catch (mysqli_sql_exception $e) {
            set_flash('danger', 'Data barang tidak bisa dihapus karena sudah dipakai transaksi.');
        }
    }

    redirect('barang.php');
}

$barangs = fetch_all('SELECT * FROM barang ORDER BY id_barang DESC');
render_header('Data Barang', 'barang');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Master Barang</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Barang</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th width="160">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($barangs as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= e($row['kode_barang']); ?></td>
            <td><?= e($row['nama_barang']); ?></td>
            <td><?= rupiah($row['harga_beli']); ?></td>
            <td><?= rupiah($row['harga_jual']); ?></td>
            <td><?= e($row['stok']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $row['id_barang']; ?>"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_barang']; ?>"><i class="fas fa-trash"></i> Hapus</button>
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
      <div class="modal-header"><h4 class="modal-title">Tambah Barang</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Kode Barang</label><input type="text" name="kode_barang" class="form-control" required></div>
        <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" class="form-control" required></div>
        <div class="form-group"><label>Harga Beli</label><input type="number" name="harga_beli" class="form-control" min="0" required></div>
        <div class="form-group"><label>Harga Jual</label><input type="number" name="harga_jual" class="form-control" min="0" required></div>
        <div class="form-group"><label>Stok</label><input type="number" name="stok" class="form-control" min="0" required></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($barangs as $row): ?>
<div class="modal fade" id="modalEdit<?= $row['id_barang']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="edit"><input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
      <div class="modal-header"><h4 class="modal-title">Edit Barang</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Kode Barang</label><input type="text" name="kode_barang" class="form-control" value="<?= e($row['kode_barang']); ?>" required></div>
        <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" class="form-control" value="<?= e($row['nama_barang']); ?>" required></div>
        <div class="form-group"><label>Harga Beli</label><input type="number" name="harga_beli" class="form-control" min="0" value="<?= e($row['harga_beli']); ?>" required></div>
        <div class="form-group"><label>Harga Jual</label><input type="number" name="harga_jual" class="form-control" min="0" value="<?= e($row['harga_jual']); ?>" required></div>
        <div class="form-group"><label>Stok</label><input type="number" name="stok" class="form-control" min="0" value="<?= e($row['stok']); ?>" required></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalHapus<?= $row['id_barang']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus Barang</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus barang <strong><?= e($row['nama_barang']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
