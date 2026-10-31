-- Jalankan SATU KALI pada database alfin yang sudah ada.
CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(30) NOT NULL,
  `tingkat` enum('VII','VIII','IX') NOT NULL,
  `wali_kelas` varchar(100) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kelas`), UNIQUE KEY `nama_kelas` (`nama_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO kelas (nama_kelas,tingkat,status) VALUES
('VII-A','VII','aktif'),('VII-B','VII','aktif'),('VIII-A','VIII','aktif'),('VIII-B','VIII','aktif'),('IX-A','IX','aktif'),('IX-B','IX','aktif');
ALTER TABLE jenis_pembayaran ADD COLUMN IF NOT EXISTS tingkat varchar(20) NOT NULL DEFAULT 'Semua Tingkat';
