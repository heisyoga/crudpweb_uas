<?php
// * CRUD user khusus admin.

require_once 'app.php';
// ! Hanya admin.
require_admin();
// ! Cek CSRF.
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'];
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'admin';

    if ($aksi === 'tambah') {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO user (nama, username, password, role) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssss', $nama, $username, $hash, $role);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data user berhasil ditambahkan.');
        } catch (mysqli_sql_exception $e) {
            set_flash('danger', 'Username sudah digunakan atau data user tidak valid.');
        }
    }

    if ($aksi === 'edit') {
        $id = (int) $_POST['id_user'];
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, 'UPDATE user SET nama = ?, username = ?, password = ?, role = ? WHERE id_user = ?');
                mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $username, $hash, $role, $id);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE user SET nama = ?, username = ?, role = ? WHERE id_user = ?');
                mysqli_stmt_bind_param($stmt, 'sssi', $nama, $username, $role, $id);
            }
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data user berhasil diperbarui.');
        } catch (mysqli_sql_exception $e) {
            set_flash('danger', 'Username sudah digunakan atau data user tidak valid.');
        }
    }

    if ($aksi === 'hapus') {
        $id = (int) $_POST['id_user'];
        if (isset($_SESSION['id_user']) && (int) $_SESSION['id_user'] === $id) {
            set_flash('danger', 'User yang sedang login tidak boleh dihapus.');
        } else {
            $stmt = mysqli_prepare($conn, 'DELETE FROM user WHERE id_user = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Data user berhasil dihapus.');
        }
    }

    redirect('user.php');
}

$users = fetch_all('SELECT * FROM user ORDER BY id_user DESC');
render_header('Data User', 'user');
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Master User</h3>
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus"></i> Tambah User</button>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped datatable">
      <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th width="160">Aksi</th></tr></thead>
      <tbody>
        <?php $no = 1; foreach ($users as $row): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= e($row['nama']); ?></td>
            <td><?= e($row['username']); ?></td>
            <td><?= e($row['role']); ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEdit<?= $row['id_user']; ?>"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalHapus<?= $row['id_user']; ?>"><i class="fas fa-trash"></i> Hapus</button>
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
      <div class="modal-header"><h4 class="modal-title">Tambah User</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="admin">Admin</option><option value="kasir">Kasir</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
    </form>
  </div>
</div>

<?php foreach ($users as $row): ?>
<div class="modal fade" id="modalEdit<?= $row['id_user']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="edit"><input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
      <div class="modal-header"><h4 class="modal-title">Edit User</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" value="<?= e($row['nama']); ?>" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" value="<?= e($row['username']); ?>" required></div>
        <div class="form-group"><label>Password Baru</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti"></div>
        <div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="admin" <?= $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option><option value="kasir" <?= $row['role'] === 'kasir' ? 'selected' : ''; ?>>Kasir</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
<div class="modal fade" id="modalHapus<?= $row['id_user']; ?>">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <?= csrf_field(); ?>
      <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
      <div class="modal-header"><h4 class="modal-title">Hapus User</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">Yakin ingin menghapus user <strong><?= e($row['nama']); ?></strong>?</div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Hapus</button></div>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php render_footer(); ?>
