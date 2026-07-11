-- * Master database db_toko.
-- ! Import akan membuat ulang tabel.
CREATE DATABASE IF NOT EXISTS `db_toko` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_toko`;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `detail_penjualan`;
DROP TABLE IF EXISTS `detail_pembelian`;
DROP TABLE IF EXISTS `penjualan`;
DROP TABLE IF EXISTS `pembelian`;
DROP TABLE IF EXISTS `barang`;
DROP TABLE IF EXISTS `pelanggan`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `user`;
SET FOREIGN_KEY_CHECKS=1;

-- * Akun login.
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  PRIMARY KEY (`id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- * Master barang dan stok.
CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stok` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_barang`),
  UNIQUE KEY `kode_barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- * Transaksi penjualan.
CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_penjualan`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `detail_penjualan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_penjualan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_penjualan` (`id_penjualan`),
  KEY `id_barang` (`id_barang`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`id_penjualan`) REFERENCES `penjualan` (`id_penjualan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- * Transaksi pembelian.
CREATE TABLE `pembelian` (
  `id_pembelian` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_pembelian`),
  KEY `id_supplier` (`id_supplier`),
  CONSTRAINT `pembelian_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `detail_pembelian` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_pembelian` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_pembelian` (`id_pembelian`),
  KEY `id_barang` (`id_barang`),
  CONSTRAINT `detail_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian` (`id_pembelian`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_pembelian_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$10$SnIz/U96S5n4ZeL5BjP3aerFR4wh4GE.VzxN3yWoFow9Mcz9GxpEK', 'admin'),
(2, 'Kasir Toko', 'kasir', '$2y$10$SnIz/U96S5n4ZeL5BjP3aerFR4wh4GE.VzxN3yWoFow9Mcz9GxpEK', 'kasir');

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `telepon`, `alamat`) VALUES
(1, 'PT Sumber Makmur', '081234567890', 'Jl. Merdeka No. 10'),
(2, 'CV Jaya Abadi', '082233445566', 'Jl. Sudirman No. 25');

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `telepon`, `alamat`) VALUES
(1, 'Pelanggan Umum', '080000000000', '-'),
(2, 'Budi Santoso', '081122334455', 'Jl. Melati No. 3'),
(3, 'Siti Aminah', '082211223344', 'Jl. Kenanga No. 8');

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `harga_beli`, `harga_jual`, `stok`) VALUES
(1, 'BRG001', 'Beras 5 Kg', 62000.00, 70000.00, 30),
(2, 'BRG002', 'Minyak Goreng 2 L', 30000.00, 36000.00, 40),
(3, 'BRG003', 'Gula Pasir 1 Kg', 14000.00, 17000.00, 50),
(4, 'BRG004', 'Kopi Bubuk 200 gr', 18000.00, 23000.00, 25);

INSERT INTO `penjualan` (`id_penjualan`, `tanggal`, `id_pelanggan`, `total`) VALUES
(1, '2026-07-11', 1, 70000.00),
(2, '2026-07-11', 2, 72000.00);

INSERT INTO `detail_penjualan` (`id_detail`, `id_penjualan`, `id_barang`, `qty`, `harga`, `subtotal`) VALUES
(1, 1, 1, 1, 70000.00, 70000.00),
(2, 2, 2, 2, 36000.00, 72000.00);

INSERT INTO `pembelian` (`id_pembelian`, `tanggal`, `id_supplier`, `total`) VALUES
(1, '2026-07-10', 1, 310000.00),
(2, '2026-07-10', 2, 180000.00);

INSERT INTO `detail_pembelian` (`id_detail`, `id_pembelian`, `id_barang`, `qty`, `harga`, `subtotal`) VALUES
(1, 1, 1, 5, 62000.00, 310000.00),
(2, 2, 4, 10, 18000.00, 180000.00);
