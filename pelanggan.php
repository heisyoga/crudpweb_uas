<?php
// * CRUD data pelanggan.

require_once 'app.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];
    $nama = isset($_POST['nama_pelanggan']) ? trim($_POST['nama_pelanggan']) : '';
    $telepon = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';

    if ($aksi === 'tambah') {
        $stmt = mysqli_prepare($conn, 'INSERT INTO pelanggan (nama_pelanggan, telepon, alamat) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $nama, $telepon, $alamat);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data pelanggan berhasil ditambahkan.');
    }

    if ($aksi === 'edit') {
        $id = (int) $_POST['id_pelanggan'];
        $stmt = mysqli_prepare($conn, 'UPDATE pelanggan SET nama_pelanggan = ?, telepon = ?, alamat = ? WHERE id_pelanggan = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $telepon, $alamat, $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data pelanggan berhasil diperbarui.');
    }

    if ($aksi === 'hapus') {
        $id = (int) $_POST['id_pelanggan'];
        $stmt = mysqli_prepare($conn, 'DELETE FROM pelanggan WHERE id_pelanggan = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Data pelanggan berhasil dihapus.');
    }

    redirect('pelanggan.php');
}

$pelanggans = fetch_all('SELECT * FROM pelanggan ORDER BY id_pelanggan DESC');
render_header('Data Pelanggan', 'pelanggan');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Master Pelanggan</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Pelanggan</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Nama Pelanggan</th><th>Telepon</th><th>Alamat</th><th width="160">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($pelanggans as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= e($row['nama_pelanggan']); ?></td>
            <td><?= e($row['telepon']); ?></td>
            <td><?= e($row['alamat']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $row['id_pelanggan']; ?>"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_pelanggan']; ?>"><i class="fas fa-trash"></i> Hapus</button>
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
      <div class="modal-header"><h4 class="modal-title">Tambah Pelanggan</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama Pelanggan</label><input type="text" name="nama_pelanggan" class="form-control" required></div>
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon" class="form-control"></div>
        <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control" rows="3"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($pelanggans as $row): ?>
<div class="modal fade" id="modalEdit<?= $row['id_pelanggan']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="edit"><input type="hidden" name="id_pelanggan" value="<?= $row['id_pelanggan']; ?>">
      <div class="modal-header"><h4 class="modal-title">Edit Pelanggan</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama Pelanggan</label><input type="text" name="nama_pelanggan" class="form-control" value="<?= e($row['nama_pelanggan']); ?>" required></div>
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon" class="form-control" value="<?= e($row['telepon']); ?>"></div>
        <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control" rows="3"><?= e($row['alamat']); ?></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalHapus<?= $row['id_pelanggan']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_pelanggan" value="<?= $row['id_pelanggan']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus Pelanggan</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus pelanggan <strong><?= e($row['nama_pelanggan']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
