# CRUD Database Toko Sederhana - Project UAS Pemrograman Web

Aplikasi web **CRUD Database Toko Sederhana** berbasis **PHP Native** dan **MySQL/MariaDB** dengan tampilan **AdminLTE**. Project ini dibuat untuk memenuhi UAS mata kuliah Pemrograman Web.

Aplikasi menyediakan fitur autentikasi, dashboard, data master, transaksi penjualan/pembelian, laporan, serta file master database `db_toko.sql` agar project mudah dijalankan di XAMPP.

---

## ✨ Fitur Utama

### Authentication

- Login dan logout
- Session guard untuk halaman aplikasi
- Password disimpan menggunakan hash
- Regenerate session ID setelah login

### Dashboard

- Ringkasan jumlah barang, pelanggan, supplier, transaksi penjualan, dan pembelian
- Ringkasan total penjualan dan pembelian
- Tabel barang terbaru dan transaksi terbaru

### Data Master

- CRUD Data User
- CRUD Data Supplier
- CRUD Data Pelanggan
- CRUD Data Barang
- Semua CRUD menggunakan modal popup Bootstrap
- Data tabel menggunakan DataTables

### Transaksi

- Transaksi Penjualan
  - Memilih pelanggan dan barang
  - Otomatis mengurangi stok barang
  - Detail transaksi dapat dilihat dari modal
- Transaksi Pembelian
  - Memilih supplier dan barang
  - Otomatis menambah stok barang
  - Detail transaksi dapat dilihat dari modal

### Laporan

- Filter laporan berdasarkan rentang tanggal
- Laporan penjualan
- Laporan pembelian
- Ringkasan total penjualan, total pembelian, dan selisih

### Keamanan Dasar

- Prepared statement untuk login dan query penting
- CSRF token untuk form POST
- Role admin untuk akses Data User
- Escape output dengan `htmlspecialchars`
- Transaksi database untuk menjaga konsistensi stok

---

## 🛠️ Tech Stack

- PHP Native
- MySQL / MariaDB
- AdminLTE
- Bootstrap 4
- jQuery
- DataTables
- Font Awesome
- XAMPP

---

## 📁 Struktur Project

```text
crudpweb_uas/
├── app.php                # Helper utama: auth, CSRF, layout, query, formatter
├── koneksi.php            # Koneksi database MySQL
├── index.php              # Redirect awal berdasarkan status login
├── login.php              # Halaman login
├── logout.php             # Logout session
├── dashboard.php          # Dashboard ringkasan toko
├── user.php               # CRUD data user
├── supplier.php           # CRUD data supplier
├── pelanggan.php          # CRUD data pelanggan
├── barang.php             # CRUD data barang
├── penjualan.php          # Transaksi penjualan
├── pembelian.php          # Transaksi pembelian
├── laporan.php            # Laporan penjualan dan pembelian
├── db_toko.sql            # Master database
├── dist/                  # Asset AdminLTE
├── plugins/               # Plugin AdminLTE, jQuery, DataTables, Bootstrap
└── pages/                 # Referensi halaman bawaan AdminLTE
```

---

## 🗄️ Struktur Database

Database yang digunakan: `db_toko`

Tabel utama:

| Tabel | Keterangan |
| --- | --- |
| `user` | Data akun login admin/kasir |
| `supplier` | Data supplier barang |
| `pelanggan` | Data pelanggan toko |
| `barang` | Data produk/barang dan stok |
| `penjualan` | Header transaksi penjualan |
| `detail_penjualan` | Detail barang dalam transaksi penjualan |
| `pembelian` | Header transaksi pembelian |
| `detail_pembelian` | Detail barang dalam transaksi pembelian |

File database sudah tersedia di:

```text
db_toko.sql
```

---

## 🚀 Cara Menjalankan Project

### 1. Clone / Download Project

Letakkan folder project di direktori `htdocs` XAMPP.

Contoh path macOS:

```text
/Applications/XAMPP/xamppfiles/htdocs/crudpweb_uas
```

Contoh path Windows:

```text
C:\xampp\htdocs\crudpweb_uas
```

### 2. Jalankan XAMPP

Aktifkan service berikut:

- Apache
- MySQL

### 3. Import Database

Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Lalu import file:

```text
db_toko.sql
```

File SQL akan membuat database `db_toko` beserta tabel dan data contoh.

### 4. Akses Aplikasi

Buka browser dan akses:

```text
http://localhost/crudpweb_uas/
```

---

## 🔐 Akun Login Demo

### Admin

```text
Username: admin
Password: admin123
```

### Kasir

```text
Username: kasir
Password: admin123
```

> Catatan: Akun admin dapat mengakses Data User. Akun kasir dapat mengakses fitur operasional toko selain manajemen user.

---

## 🧭 Halaman Aplikasi

| File | Fungsi |
| --- | --- |
| `index.php` | Redirect ke login/dashboard |
| `login.php` | Form login |
| `logout.php` | Logout dan destroy session |
| `dashboard.php` | Ringkasan data toko |
| `user.php` | CRUD user/admin/kasir |
| `supplier.php` | CRUD supplier |
| `pelanggan.php` | CRUD pelanggan |
| `barang.php` | CRUD barang dan stok |
| `penjualan.php` | Transaksi penjualan |
| `pembelian.php` | Transaksi pembelian |
| `laporan.php` | Laporan transaksi berdasarkan tanggal |

---

## 📝 Catatan

Project ini menggunakan gaya komentar **Better Comments** pada file PHP dan SQL agar bagian penting mudah dibaca:

```php
// * Penjelasan fitur utama
// ! Catatan penting / keamanan
// ? Penjelasan validasi/helper
```

Contoh bagian yang diberi komentar:

- Auth guard
- CSRF protection
- Role admin
- Transaksi stok penjualan/pembelian
- Query laporan
- Struktur database SQL

---

## ✅ Checklist Requirement UAS

| Requirement | Status | Mandatory
| --- | --- | --- |
| Authentication | ✅ Selesai | Yes |
| Modal Popup CRUD | ✅ Selesai | Yes |
| Dashboard | ✅ Selesai | Yes |
| Penjualan | ✅ Selesai | No |
| Pembelian | ✅ Selesai | No |
| Laporan | ✅ Selesai | No |
| Data Master | ✅ Selesai | Yes |
| SQL file `db_toko` | ✅ Selesai | Yes |
| UI AdminLTE | ✅ Selesai | Yes |

---

## 🧪 Validasi

Validasi syntax PHP sudah dilakukan menggunakan PHP bawaan XAMPP:

```text
No syntax errors detected
```

pada semua file PHP aplikasi.

---

## 📌 Catatan Pengembangan

- Project ini menggunakan PHP Native/prosedural agar mudah dipahami untuk pembelajaran dasar pemrograman web.
- Import `db_toko.sql` disarankan dilakukan sebelum menjalankan aplikasi.
- Jika database belum di-import, halaman yang membutuhkan data akan menampilkan error koneksi/query.
- Folder `dist/`, `plugins/`, dan `pages/` berasal dari template AdminLTE.

---

## 👨‍💻 Author

Project UAS Pemrograman Web - CRUD Database Toko Sederhana.
