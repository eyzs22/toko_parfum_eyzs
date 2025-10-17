-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Okt 2025 pada 10.53
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_eka_yunizar`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_laporan_transaksi` (IN `p_tanggal_dari` DATE, IN `p_tanggal_sampai` DATE)   BEGIN
    SELECT 
        t.id_transaksi,
        t.tanggal,
        p.nama_pembeli,
        b.nama_barang,
        b.harga,
        t.jumlah,
        t.total_harga
    FROM transaksi t
    JOIN pembeli p ON t.id_pembeli = p.id_pembeli
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE (p_tanggal_dari IS NULL OR DATE(t.tanggal) >= p_tanggal_dari)
      AND (p_tanggal_sampai IS NULL OR DATE(t.tanggal) <= p_tanggal_sampai)
    ORDER BY t.tanggal DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `volume_ml` int(11) NOT NULL DEFAULT 50,
  `harga` decimal(30,0) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0
) ;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `volume_ml`, `harga`, `stok`) VALUES
('PRF-0AB40', 'ALCHEMIST 09', 20, 230000, 2),
('PRF-0E931', 'PHILEA 02', 50, 250000, 15),
('PRF-876EB', 'THE BODY SHOP', 30, 120000, 1),
('PRF-917AA', 'PHILEA', 50, 250000, 3),
('PRF-9E5EB', 'OCTARINE', 100, 350000, 2),
('PRF-B049D', 'ELEA 03', 50, 320000, 4),
('PRF-CC916', 'ELEA', 50, 120000, 11);

--
-- Trigger `barang`
--
DELIMITER $$
CREATE TRIGGER `before_insert_barang` BEFORE INSERT ON `barang` FOR EACH ROW BEGIN
    SET NEW.nama_barang = UPPER(NEW.nama_barang);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_update_barang` BEFORE UPDATE ON `barang` FOR EACH ROW BEGIN
    SET NEW.nama_barang = UPPER(NEW.nama_barang);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` varchar(20) NOT NULL,
  `nama_pembeli` varchar(255) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama_pembeli`, `alamat`) VALUES
('PLG-32222', 'Mellanda ', 'Krembung'),
('PLG-33886', 'Rani Nur', 'Jombang'),
('PLG-360F6', 'Fashih Nabil', 'Lamongan'),
('PLG-43017', 'Amanda', 'Sidoarjo'),
('PLG-66A56', 'Imel', 'Jambangan'),
('PLG-CCAAD', 'Eka Yunizar', 'Jetis Kulon');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(20) NOT NULL,
  `id_pembeli` varchar(20) NOT NULL,
  `id_barang` varchar(20) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pembeli`, `id_barang`, `jumlah`, `total_harga`, `tanggal`) VALUES
('TRX-06838', 'PLG-CCAAD', 'PRF-0AB40', 2, 460000.00, '2025-10-17 14:25:19'),
('TRX-5B688', 'PLG-66A56', 'PRF-876EB', 1, 120000.00, '2025-10-17 14:28:06'),
('TRX-99257', 'PLG-32222', 'PRF-9E5EB', 1, 350000.00, '2025-10-17 14:12:30'),
('TRX-9A8B4', 'PLG-43017', 'PRF-CC916', 2, 240000.00, '2025-10-17 15:40:22'),
('TRX-E7185', 'PLG-33886', 'PRF-B049D', 3, 960000.00, '2025-10-17 15:46:39');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_dashboard_stats`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_dashboard_stats` (
`total_transaksi` bigint(21)
,`total_pendapatan` decimal(37,2)
,`barang_terlaris` varchar(255)
,`jumlah_terjual_terlaris` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `view_dashboard_stats`
--
DROP TABLE IF EXISTS `view_dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_dashboard_stats`  AS SELECT (select count(0) from `transaksi`) AS `total_transaksi`, (select coalesce(sum(`transaksi`.`total_harga`),0) from `transaksi`) AS `total_pendapatan`, (select `b`.`nama_barang` from (`barang` `b` join `transaksi` `t` on(`b`.`id_barang` = `t`.`id_barang`)) group by `b`.`id_barang`,`b`.`nama_barang` order by sum(`t`.`jumlah`) desc limit 1) AS `barang_terlaris`, (select sum(`t`.`jumlah`) from (`transaksi` `t` join `barang` `b` on(`t`.`id_barang` = `b`.`id_barang`)) group by `b`.`id_barang` order by sum(`t`.`jumlah`) desc limit 1) AS `jumlah_terjual_terlaris` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `idx_barang_nama` (`nama_barang`);

--
-- Indeks untuk tabel `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`),
  ADD KEY `idx_pembeli_nama` (`nama_pembeli`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `idx_transaksi_tanggal` (`tanggal`),
  ADD KEY `idx_transaksi_pembeli` (`id_pembeli`),
  ADD KEY `idx_transaksi_barang` (`id_barang`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
