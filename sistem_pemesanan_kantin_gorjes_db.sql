-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 04, 2026 at 03:34 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_pemesanan_kantin_gorjes_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan_baku`
--

CREATE TABLE `bahan_baku` (
  `id_bahan_baku` int(11) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `harga_satuan_bahan` decimal(12,2) NOT NULL,
  `stok` decimal(10,2) DEFAULT 0.00,
  `stok_kritis` decimal(10,2) DEFAULT 0.00,
  `status_ketersediaan` enum('Tersedia','Menipis','Habis') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bahan_baku`
--

INSERT INTO `bahan_baku` (`id_bahan_baku`, `nama_bahan`, `satuan`, `harga_satuan_bahan`, `stok`, `stok_kritis`, `status_ketersediaan`) VALUES
(1, 'Tepung Terigu', 'Kg', 13000.00, 20.00, 5.00, 'Tersedia'),
(2, 'Tepung Tapioka', 'Kg', 12000.00, 14.31, 5.00, 'Tersedia'),
(3, 'Tepung Panir', 'Kg', 18000.00, 8.50, 3.00, 'Tersedia'),
(4, 'Tepung Maizena', 'Kg', 15000.00, 5.00, 2.00, 'Tersedia'),
(5, 'Tepung Custard', 'Kg', 35000.00, 3.00, 1.00, 'Tersedia'),
(6, 'Beras', 'Kg', 16000.00, 38.90, 10.00, 'Tersedia'),
(7, 'Mie Samyang', 'Pack', 15000.00, 18.80, 5.00, 'Tersedia'),
(8, 'Fettuccine Pasta', 'Pack', 18000.00, 14.20, 3.00, 'Tersedia'),
(9, 'Ayam Fillet', 'Kg', 50000.00, 7.28, 3.00, 'Tersedia'),
(10, 'Daging Ayam Giling', 'Kg', 45000.00, 8.85, 3.00, 'Tersedia'),
(11, 'Ikan Tenggiri', 'Kg', 70000.00, 8.00, 2.00, 'Tersedia'),
(12, 'Ikan Dori Fillet', 'Kg', 65000.00, 8.00, 2.00, 'Tersedia'),
(13, 'Udang', 'Kg', 85000.00, 7.20, 2.00, 'Tersedia'),
(14, 'Sosis', 'Pack', 25000.00, 10.00, 3.00, 'Tersedia'),
(15, 'Smoked Beef', 'Pack', 35000.00, 8.80, 2.00, 'Tersedia'),
(16, 'Telur', 'Butir', 2500.00, 59.76, 20.00, 'Tersedia'),
(17, 'Keju Mozzarella', 'Kg', 95000.00, -26.00, 1.00, 'Habis'),
(18, 'Keju Cheddar', 'Kg', 85000.00, -1.00, 1.00, 'Habis'),
(19, 'Keju Bubuk', 'Kg', 90000.00, 2.66, 1.00, 'Tersedia'),
(20, 'Susu Cair', 'Liter', 18000.00, 10.00, 2.00, 'Tersedia'),
(21, 'Whipping Cream', 'Liter', 45000.00, 4.52, 1.00, 'Tersedia'),
(22, 'Butter', 'Kg', 90000.00, 4.70, 1.00, 'Tersedia'),
(23, 'Cream Cheese', 'Kg', 120000.00, 3.00, 1.00, 'Tersedia'),
(24, 'Susu Kental Manis', 'Kaleng', 13000.00, 20.00, 5.00, 'Tersedia'),
(25, 'Bawang Putih', 'Kg', 35000.00, 4.55, 1.00, 'Tersedia'),
(26, 'Bawang Merah', 'Kg', 40000.00, 4.55, 1.00, 'Tersedia'),
(27, 'Bawang Bombay', 'Kg', 30000.00, 4.94, 1.00, 'Tersedia'),
(28, 'Daun Bawang', 'Ikat', 8000.00, 10.00, 2.00, 'Tersedia'),
(29, 'Seledri', 'Ikat', 7000.00, 9.82, 2.00, 'Tersedia'),
(30, 'Kol/Kubis', 'Kg', 12000.00, 10.00, 2.00, 'Tersedia'),
(31, 'Wortel', 'Kg', 15000.00, 10.00, 2.00, 'Tersedia'),
(32, 'Cabai', 'Kg', 40000.00, 3.00, 1.00, 'Tersedia'),
(33, 'Daun Basil', 'Ikat', 10000.00, 9.10, 2.00, 'Tersedia'),
(34, 'Kentang', 'Kg', 18000.00, 10.00, 2.00, 'Tersedia'),
(35, 'Timun', 'Kg', 12000.00, 4.28, 1.00, 'Tersedia'),
(36, 'Selada', 'Kg', 18000.00, 5.00, 1.00, 'Tersedia'),
(37, 'Melon', 'Buah', 25000.00, 10.00, 2.00, 'Tersedia'),
(38, 'Semangka', 'Buah', 30000.00, 10.00, 2.00, 'Tersedia'),
(39, 'Mangga', 'Kg', 35000.00, 10.00, 2.00, 'Tersedia'),
(40, 'Lemon', 'Kg', 40000.00, 3.00, 1.00, 'Tersedia'),
(41, 'Biskuit Marie', 'Pack', 12000.00, 10.00, 2.00, 'Tersedia'),
(42, 'Coklat Bubuk', 'Kg', 70000.00, 3.00, 1.00, 'Tersedia'),
(43, 'Dark Chocolate', 'Kg', 95000.00, 3.00, 1.00, 'Tersedia'),
(44, 'Chocolate Chips', 'Kg', 75000.00, 5.00, 1.00, 'Tersedia'),
(45, 'Agar-Agar', 'Pack', 6000.00, 20.00, 5.00, 'Tersedia'),
(46, 'Gelatin', 'Pack', 18000.00, 10.00, 2.00, 'Tersedia'),
(47, 'Vanilla Essence', 'Botol', 25000.00, 5.00, 1.00, 'Tersedia'),
(48, 'Santan', 'Liter', 18000.00, 10.00, 2.00, 'Tersedia'),
(49, 'Sagu Mutiara', 'Kg', 22000.00, 5.00, 1.00, 'Tersedia'),
(50, 'Croissant Dough', 'Pack', 35000.00, 10.00, 2.00, 'Tersedia'),
(51, 'Kulit Lumpia', 'Pack', 15000.00, 10.00, 2.00, 'Tersedia'),
(52, 'Gula Pasir', 'Kg', 17000.00, 15.00, 5.00, 'Tersedia'),
(53, 'Gula Halus', 'Kg', 20000.00, 10.00, 2.00, 'Tersedia'),
(54, 'Gula Merah', 'Kg', 22000.00, 0.40, 1.00, 'Menipis'),
(55, 'Baking Powder', 'Pack', 12000.00, 10.00, 2.00, 'Tersedia'),
(56, 'Garam', 'Kg', 10000.00, 4.97, 1.00, 'Tersedia'),
(57, 'Merica', 'Kg', 80000.00, 1.98, 1.00, 'Tersedia'),
(58, 'Kaldu Bubuk', 'Pack', 12000.00, 9.98, 2.00, 'Tersedia'),
(59, 'MSG/Penyedap', 'Pack', 9000.00, 10.00, 2.00, 'Tersedia'),
(60, 'Bawang Bubuk', 'Pack', 15000.00, 4.91, 1.00, 'Tersedia'),
(61, 'Cabai Bubuk', 'Pack', 18000.00, 4.97, 1.00, 'Tersedia'),
(62, 'Parsley/Oregano', 'Pack', 25000.00, 5.00, 1.00, 'Tersedia'),
(63, 'Minyak Goreng', 'Liter', 18000.00, 18.34, 5.00, 'Tersedia'),
(64, 'Mayonnaise', 'Botol', 25000.00, 10.00, 2.00, 'Tersedia'),
(65, 'Saus Mentai', 'Botol', 35000.00, 8.00, 2.00, 'Tersedia'),
(66, 'Saus Tomat', 'Botol', 15000.00, 10.00, 2.00, 'Tersedia'),
(67, 'Saus Sambal', 'Botol', 16000.00, 9.29, 2.00, 'Tersedia'),
(68, 'Kecap', 'Botol', 18000.00, 9.09, 2.00, 'Tersedia'),
(69, 'Saus Tiram', 'Botol', 22000.00, 7.55, 2.00, 'Tersedia'),
(70, 'Fish Sauce', 'Botol', 30000.00, 4.78, 1.00, 'Tersedia'),
(71, 'Tartar Sauce', 'Botol', 28000.00, 4.54, 1.00, 'Tersedia'),
(72, 'Box Nasi', 'Pcs', 1500.00, 226.00, 50.00, 'Tersedia'),
(73, 'Box Dessert', 'Pcs', 1000.00, 300.00, 50.00, 'Tersedia'),
(74, 'Cup Sauce', 'Pcs', 300.00, 500.00, 100.00, 'Tersedia'),
(75, 'Cup Minuman', 'Pcs', 800.00, 300.00, 50.00, 'Tersedia'),
(76, 'Tutup Cup', 'Pcs', 300.00, 300.00, 50.00, 'Tersedia'),
(77, 'Sendok Plastik', 'Pcs', 250.00, 426.00, 100.00, 'Tersedia'),
(78, 'Garpu Plastik', 'Pcs', 250.00, 500.00, 100.00, 'Tersedia'),
(79, 'Sedotan', 'Pcs', 150.00, 500.00, 100.00, 'Tersedia'),
(80, 'Tisu', 'Pack', 12000.00, 46.30, 10.00, 'Tersedia'),
(81, 'Plastik Kemasan', 'Pcs', 500.00, 426.00, 100.00, 'Tersedia'),
(82, 'Paper Bag', 'Pcs', 1200.00, 200.00, 30.00, 'Tersedia'),
(83, 'Stiker Label', 'Pcs', 250.00, 500.00, 100.00, 'Tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `id_produk` int(11) NOT NULL,
  `id_bahan_baku` int(11) NOT NULL,
  `qty` decimal(10,4) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` (`id_produk`, `id_bahan_baku`, `qty`, `subtotal`) VALUES
(1, 2, 0.0300, 360.00),
(1, 9, 0.0500, 2250.00),
(1, 17, 1.0000, 2500.00),
(1, 18, 0.0200, 1900.00),
(1, 27, 0.0100, 350.00),
(1, 30, 0.0200, 160.00),
(1, 60, 0.0050, 50.00),
(1, 61, 0.0020, 160.00),
(2, 7, 0.2000, 3600.00),
(2, 15, 0.2000, 5000.00),
(2, 18, 1.0000, 2500.00),
(2, 19, 0.0300, 2550.00),
(2, 21, 0.0800, 1440.00),
(2, 22, 0.0500, 2250.00),
(2, 27, 0.0100, 350.00),
(2, 29, 0.0300, 900.00),
(2, 61, 0.0020, 160.00),
(2, 67, 0.0050, 125.00),
(2, 68, 0.0100, 180.00),
(3, 17, 2.0000, 5000.00),
(3, 21, 0.1500, 2700.00),
(3, 23, 0.0100, 900.00),
(3, 24, 0.0500, 6000.00),
(3, 51, 0.0050, 125.00),
(3, 55, 0.0300, 510.00),
(4, 2, 0.0300, 360.00),
(4, 10, 0.0700, 4900.00),
(4, 17, 1.0000, 2500.00),
(4, 27, 0.0100, 350.00),
(4, 30, 0.0200, 160.00),
(4, 60, 0.0050, 50.00),
(4, 62, 0.0050, 60.00),
(5, 2, 0.0300, 360.00),
(5, 10, 0.0500, 3500.00),
(5, 17, 1.0000, 2500.00),
(5, 54, 0.2000, 3000.00),
(5, 60, 0.0030, 30.00),
(5, 68, 0.0300, 540.00),
(5, 71, 0.0200, 320.00),
(6, 2, 0.0800, 960.00),
(6, 20, 0.0100, 900.00),
(6, 60, 0.0030, 30.00),
(6, 64, 0.0030, 45.00),
(6, 68, 0.0300, 540.00),
(7, 6, 1.0000, 15000.00),
(7, 19, 0.0300, 2550.00),
(7, 20, 0.0100, 900.00),
(7, 65, 0.0050, 90.00),
(7, 69, 0.0100, 250.00),
(8, 2, 0.0300, 360.00),
(8, 9, 0.0500, 2250.00),
(8, 17, 1.0000, 2500.00),
(8, 27, 0.0100, 350.00),
(8, 68, 0.0100, 180.00),
(8, 70, 0.0300, 1050.00),
(9, 2, 0.0500, 600.00),
(9, 9, 0.0800, 3600.00),
(9, 17, 1.0000, 2500.00),
(9, 27, 0.0100, 350.00),
(9, 68, 0.0100, 180.00),
(9, 70, 0.0500, 1750.00),
(10, 3, 0.0300, 540.00),
(10, 8, 0.1000, 5000.00),
(10, 16, 0.0300, 1050.00),
(10, 17, 1.0000, 2500.00),
(10, 19, 0.0200, 1700.00),
(10, 60, 0.0030, 30.00),
(10, 61, 0.0020, 160.00),
(10, 68, 0.0200, 360.00),
(11, 23, 0.0200, 1800.00),
(11, 24, 0.0500, 6000.00),
(11, 41, 0.1000, 1200.00),
(11, 51, 0.0030, 75.00),
(11, 55, 0.0200, 340.00),
(12, 21, 0.2000, 3600.00),
(12, 42, 0.0200, 1400.00),
(12, 43, 0.0200, 1900.00),
(12, 45, 1.0000, 6000.00),
(12, 51, 0.0020, 50.00),
(12, 55, 0.0200, 340.00),
(13, 21, 0.2000, 3600.00),
(13, 32, 0.0500, 650.00),
(13, 45, 1.0000, 6000.00),
(13, 51, 0.0020, 50.00),
(13, 55, 0.0150, 255.00),
(14, 38, 0.2500, 6250.00),
(15, 39, 0.2500, 7500.00),
(16, 21, 0.0500, 900.00),
(16, 40, 0.1000, 3500.00),
(16, 51, 0.0020, 50.00),
(16, 52, 0.1000, 1800.00),
(16, 53, 0.0300, 660.00),
(16, 55, 0.0200, 340.00),
(17, 1, 0.0500, 650.00),
(17, 18, 1.0000, 2500.00),
(17, 23, 0.0200, 1800.00),
(17, 44, 0.0200, 1500.00),
(17, 51, 0.0020, 50.00),
(17, 55, 0.0200, 340.00),
(17, 58, 0.0030, 36.00),
(17, 60, 0.0010, 10.00),
(18, 23, 0.0100, 900.00),
(18, 54, 1.0000, 35000.00),
(18, 55, 0.0100, 170.00),
(19, 1, 0.0100, 130.00),
(19, 3, 0.0200, 360.00),
(19, 8, 0.0300, 1500.00),
(19, 18, 1.0000, 2500.00),
(19, 19, 0.0200, 1700.00),
(19, 21, 0.0300, 540.00),
(19, 29, 0.0100, 300.00),
(19, 34, 0.0200, 300.00),
(19, 54, 0.2000, 3000.00),
(19, 60, 0.0020, 20.00),
(19, 62, 0.0020, 24.00),
(19, 68, 0.0200, 360.00),
(20, 3, 0.0300, 540.00),
(20, 6, 0.1500, 2400.00),
(20, 9, 0.1000, 5000.00),
(20, 16, 1.0000, 2500.00),
(20, 35, 0.0300, 360.00),
(20, 63, 0.0200, 360.00),
(20, 67, 0.0200, 320.00),
(20, 72, 1.0000, 1500.00),
(20, 77, 1.0000, 250.00),
(20, 80, 0.0500, 600.00),
(20, 81, 1.0000, 500.00),
(21, 6, 0.1500, 2400.00),
(21, 9, 0.1000, 5000.00),
(21, 25, 0.0100, 350.00),
(21, 26, 0.0100, 400.00),
(21, 33, 0.0200, 200.00),
(21, 63, 0.0150, 270.00),
(21, 69, 0.0100, 220.00),
(21, 70, 0.0050, 150.00),
(21, 72, 1.0000, 1500.00),
(21, 77, 1.0000, 250.00),
(21, 80, 0.0500, 600.00),
(21, 81, 1.0000, 500.00),
(22, 3, 0.0300, 540.00),
(22, 6, 0.1500, 2400.00),
(22, 12, 0.1000, 6500.00),
(22, 16, 1.0000, 2500.00),
(22, 34, 0.1000, 1800.00),
(22, 63, 0.0300, 540.00),
(22, 71, 0.0200, 560.00),
(22, 72, 1.0000, 1500.00),
(22, 77, 1.0000, 250.00),
(22, 80, 0.0500, 600.00),
(22, 81, 1.0000, 500.00),
(23, 3, 0.0300, 540.00),
(23, 6, 0.1500, 2400.00),
(23, 13, 0.0800, 6800.00),
(23, 16, 1.0000, 2500.00),
(23, 63, 0.0300, 540.00),
(23, 67, 0.0200, 320.00),
(23, 72, 1.0000, 1500.00),
(23, 77, 1.0000, 250.00),
(23, 80, 0.0500, 600.00),
(23, 81, 1.0000, 500.00),
(24, 9, 0.1200, 6000.00),
(24, 25, 0.0100, 350.00),
(24, 26, 0.0100, 400.00),
(24, 33, 0.0200, 200.00),
(24, 56, 0.0020, 20.00),
(24, 57, 0.0020, 160.00),
(24, 63, 0.0200, 360.00),
(24, 69, 0.0100, 220.00),
(24, 70, 0.0050, 150.00),
(25, 3, 0.0400, 720.00),
(25, 9, 0.1200, 6000.00),
(25, 16, 1.0000, 2500.00),
(25, 56, 0.0030, 30.00),
(25, 57, 0.0020, 160.00),
(25, 58, 0.0050, 60.00),
(25, 63, 0.0300, 540.00),
(26, 3, 0.0400, 720.00),
(26, 12, 0.1200, 7800.00),
(26, 16, 1.0000, 2500.00),
(26, 34, 0.1500, 2700.00),
(26, 40, 0.0200, 800.00),
(26, 56, 0.0020, 20.00),
(26, 63, 0.0400, 720.00),
(26, 71, 0.0200, 560.00),
(27, 10, 0.1000, 4500.00),
(27, 25, 0.0100, 350.00),
(27, 26, 0.0100, 400.00),
(27, 48, 0.0300, 540.00),
(27, 56, 0.0030, 30.00),
(27, 57, 0.0020, 160.00),
(27, 58, 0.0050, 60.00),
(28, 3, 0.0400, 720.00),
(28, 13, 0.1000, 8500.00),
(28, 16, 1.0000, 2500.00),
(28, 56, 0.0020, 20.00),
(28, 63, 0.0400, 720.00),
(28, 67, 0.0200, 320.00);

-- --------------------------------------------------------

--
-- Table structure for table `cabang_kantin`
--

CREATE TABLE `cabang_kantin` (
  `id_cabang_kantin` int(11) NOT NULL,
  `nama_cabang` varchar(100) NOT NULL,
  `alamat_cabang` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cabang_kantin`
--

INSERT INTO `cabang_kantin` (`id_cabang_kantin`, `nama_cabang`, `alamat_cabang`) VALUES
(1, 'SMP Islam Al Azhar 2 Jakarta Selatan', 'Jakarta Selatan'),
(2, 'SMP Islam Al Azhar 1 Kebayoran', 'Kebayoran');

-- --------------------------------------------------------

--
-- Table structure for table `catering`
--

CREATE TABLE `catering` (
  `id_catering` int(11) NOT NULL,
  `tanggal_acara` date NOT NULL,
  `lokasi_acara` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catering`
--

INSERT INTO `catering` (`id_catering`, `tanggal_acara`, `lokasi_acara`) VALUES
(3, '2026-05-05', 'tes'),
(5, '2026-05-26', 'uahush'),
(6, '2026-05-17', 'tes'),
(7, '2026-05-08', 'tessss'),
(8, '2026-05-21', 'ololo'),
(9, '2026-05-14', 'tes'),
(10, '2026-05-04', 'hixhuhd'),
(11, '2026-05-14', 'tes'),
(12, '2026-06-27', 'Kebayoran lama');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_pesanan`, `id_produk`, `qty`, `subtotal`) VALUES
(19, 24, 5, 110000.00),
(19, 25, 6, 132000.00),
(20, 2, 6, 120000.00),
(20, 5, 23, 230000.00),
(20, 10, 8, 80000.00),
(21, 20, 24, 600000.00),
(21, 21, 40, 1000000.00),
(21, 23, 10, 250000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id_pegawai` int(11) NOT NULL,
  `nama_pegawai` varchar(100) NOT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `nama_pegawai`, `jabatan`, `password`) VALUES
(1, 'Indri', 'Admin', '123456'),
(2, 'Rina Putri', 'Pegawai Catering', '123456'),
(3, 'Dewi Lestari', 'Pegawai Kantin', '123456'),
(4, 'Siska Nur', 'Pegawai Dapur', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat_pelanggan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_telp`, `alamat_pelanggan`) VALUES
(1, 'Ahmad Fauzi', '081234567890', 'Jakarta Selatan'),
(2, 'Siti Aminah', '081234567891', 'Kebayoran'),
(3, 'Rizky Maulana', '081234567892', 'Depok'),
(4, 'Dina Maharani', '081234567893', 'Jakarta Selatan'),
(5, 'Andi Saputra', '081234567894', 'Tangerang');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `status_pesanan` varchar(50) NOT NULL,
  `total_harga` decimal(12,2) DEFAULT 0.00,
  `jenis_pesanan` enum('Kantin','Catering') NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_pegawai` int(11) NOT NULL,
  `id_cabang_kantin` int(11) DEFAULT NULL,
  `id_catering` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `tanggal_pesanan`, `status_pesanan`, `total_harga`, `jenis_pesanan`, `id_pelanggan`, `id_pegawai`, `id_cabang_kantin`, `id_catering`, `catatan`) VALUES
(19, '2026-06-03 12:14:59', 'Selesai', 242000.00, 'Kantin', 1, 1, 2, NULL, ''),
(20, '2026-06-03 12:56:02', 'Selesai', 430000.00, 'Kantin', 5, 1, 1, NULL, ''),
(21, '2026-06-04 03:30:28', 'Diproses', 1850000.00, 'Catering', 4, 1, NULL, 12, '');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `harga_pokok` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `kategori`, `harga_jual`, `harga_pokok`) VALUES
(1, 'Dimsum Keju Lumer', 'Makanan (Kantin)', 8000.00, 7730.00),
(2, 'Fettuccine Carbonara', 'Makanan (Kantin)', 20000.00, 13000.00),
(3, 'Brulee', 'Makanan (Kantin)', 25000.00, 17000.00),
(4, 'Siomay', 'Makanan (Kantin)', 15000.00, 9000.00),
(5, 'Batagor', 'Makanan (Kantin)', 10000.00, 6000.00),
(6, 'Cimol Keju', 'Makanan (Kantin)', 10000.00, 6500.00),
(7, 'Samyang Keju', 'Makanan (Kantin)', 20000.00, 14000.00),
(8, 'Dimsum Mentai Small', 'Makanan (Kantin)', 10000.00, 6500.00),
(9, 'Dimsum Mentai Medium', 'Makanan (Kantin)', 15000.00, 10000.00),
(10, 'Mini Cordon Bleu', 'Makanan (Kantin)', 10000.00, 7000.00),
(11, 'Cheese Cake', 'Dessert (Kantin)', 18000.00, 9415.00),
(12, 'Puding Dark Choco', 'Dessert (Kantin)', 12000.00, 13290.00),
(13, 'Silky Puding', 'Dessert (Kantin)', 8000.00, 10555.00),
(14, 'Slice Fruit Melon', 'Dessert (Kantin)', 10000.00, 6250.00),
(15, 'Slice Fruit Semangka', 'Dessert (Kantin)', 10000.00, 7500.00),
(16, 'Sago Mango', 'Dessert (Kantin)', 15000.00, 7250.00),
(17, 'Soft Cookies', 'Snack (Kantin)', 12000.00, 6886.00),
(18, 'Croffle', 'Snack (Kantin)', 8000.00, 5000.00),
(19, 'Aneka Risoles', 'Snack (Kantin)', 7000.00, 10734.00),
(20, 'Paket Nasi Box 1', 'Paket Nasi Box', 25000.00, 14330.00),
(21, 'Paket Nasi Box 2', 'Paket Nasi Box', 25000.00, 11840.00),
(22, 'Paket Nasi Box 3', 'Paket Nasi Box', 25000.00, 17690.00),
(23, 'Paket Nasi Box 4', 'Paket Nasi Box', 25000.00, 15950.00),
(24, 'Chicken Basil Thailand', 'Catering Harian', 22000.00, 7860.00),
(25, 'Ayam Fillet Crispy', 'Catering Harian', 22000.00, 10010.00),
(26, 'Fish and Chips', 'Catering Harian', 22000.00, 15820.00),
(27, 'Sate Lilit', 'Catering Harian', 22000.00, 6040.00),
(28, 'Ebi Furai', 'Catering Harian', 22000.00, 12780.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  ADD PRIMARY KEY (`id_bahan_baku`);

--
-- Indexes for table `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id_produk`,`id_bahan_baku`),
  ADD KEY `fk_bom_bahan` (`id_bahan_baku`);

--
-- Indexes for table `cabang_kantin`
--
ALTER TABLE `cabang_kantin`
  ADD PRIMARY KEY (`id_cabang_kantin`);

--
-- Indexes for table `catering`
--
ALTER TABLE `catering`
  ADD PRIMARY KEY (`id_catering`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_pesanan`,`id_produk`),
  ADD KEY `fk_detail_produk` (`id_produk`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_pegawai`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `fk_pesanan_pelanggan` (`id_pelanggan`),
  ADD KEY `fk_pesanan_pegawai` (`id_pegawai`),
  ADD KEY `fk_pesanan_cabang` (`id_cabang_kantin`),
  ADD KEY `fk_pesanan_catering` (`id_catering`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bahan_baku`
--
ALTER TABLE `bahan_baku`
  MODIFY `id_bahan_baku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `cabang_kantin`
--
ALTER TABLE `cabang_kantin`
  MODIFY `id_cabang_kantin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `catering`
--
ALTER TABLE `catering`
  MODIFY `id_catering` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_pegawai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `fk_bom_bahan` FOREIGN KEY (`id_bahan_baku`) REFERENCES `bahan_baku` (`id_bahan_baku`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bom_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_cabang` FOREIGN KEY (`id_cabang_kantin`) REFERENCES `cabang_kantin` (`id_cabang_kantin`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_catering` FOREIGN KEY (`id_catering`) REFERENCES `catering` (`id_catering`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pesanan_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
