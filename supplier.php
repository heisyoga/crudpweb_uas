<?php
// * CRUD data supplier.

require_once 'app.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];
    $nama = isset($_POST['nama_supplier']) ? trim($_POST['nama_supplier']) : '';
    $telepon = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare($conn, 'INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $telepon, $alamat);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data supplier berhasil ditambahkan.');
    }

    if ($aksi === 'edit') {
        $id = (int) $_POST['id_supplier'];
        $stmt = mysqli_prepare($conn, 'UPDATE supplier SET nama_supplier = ?, telepon = ?, alamat = ? WHERE id_supplier = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $telepon, $alamat, $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data supplier berhasil diperbarui.');
    }

    if ($aksi === 'hapus') {
        $id = (int) $_POST['id_supplier'];
        $stmt = mysqli_prepare($conn, 'DELETE FROM supplier WHERE id_supplier = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data supplier berhasil dihapus.');
    }

    redirect('supplier.php');
}

$suppliers = fetch_all('SELECT * FROM supplier ORDER BY id_supplier DESC');
render_header('Data Supplier', 'supplier');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Master Supplier</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Supplier</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Nama Supplier</th><th>Telepon</th><th>Alamat</th><th width="160">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($suppliers as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= e($row['nama_supplier']); ?></td>
            <td><?= e($row['telepon']); ?></td>
            <td><?= e($row['alamat']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $row['id_supplier']; ?>"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_supplier']; ?>"><i class="fas fa-trash"></i> Hapus</button>
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
      <div class="modal-header"><h4 class="modal-title">Tambah Supplier</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama Supplier</label><input type="text" name="nama_supplier" class="form-control" required></div>
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon" class="form-control"></div>
        <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control" rows="3"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($suppliers as $row): ?>
<div class="modal fade" id="modalEdit<?= $row['id_supplier']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="edit"><input type="hidden" name="id_supplier" value="<?= $row['id_supplier']; ?>">
      <div class="modal-header"><h4 class="modal-title">Edit Supplier</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama Supplier</label><input type="text" name="nama_supplier" class="form-control" value="<?= e($row['nama_supplier']); ?>" required></div>
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon" class="form-control" value="<?= e($row['telepon']); ?>"></div>
        <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control" rows="3"><?= e($row['alamat']); ?></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalHapus<?= $row['id_supplier']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_supplier" value="<?= $row['id_supplier']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus Supplier</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus supplier <strong><?= e($row['nama_supplier']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
