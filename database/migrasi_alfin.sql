-- Jalankan satu kali setelah mengimpor database alfin.sql.
-- Aman untuk data yang sudah ada: tidak menghapus tabel atau transaksi.
ALTER TABLE `jenis_pembayaran`
  ADD COLUMN IF NOT EXISTS `tingkat` VARCHAR(20) NOT NULL DEFAULT 'Semua Tingkat' AFTER `nama_jenis`;

-- Pastikan data kelas lama tetap ada. Tidak mengubah nama/tingkat otomatis
-- karena nilai yang ada perlu dikonfirmasi dengan data resmi sekolah.
CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_kelas` VARCHAR(50) NOT NULL,
  `tingkat` VARCHAR(10) NOT NULL,
  `wali_kelas` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas_nama` (`nama_kelas`),
  KEY `idx_kelas_tingkat` (`tingkat`),
  KEY `idx_kelas_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tambahkan kelas yang tersimpan pada siswa tetapi belum ada di tabel kelas.
INSERT IGNORE INTO `kelas` (`nama_kelas`, `tingkat`, `status`)
SELECT DISTINCT TRIM(s.`kelas`),
       CASE
         WHEN UPPER(TRIM(s.`kelas`)) LIKE 'VIII%' THEN 'VIII'
         WHEN UPPER(TRIM(s.`kelas`)) LIKE 'VII%' THEN 'VII'
         WHEN UPPER(TRIM(s.`kelas`)) LIKE 'IX%' THEN 'IX'
         ELSE 'VII'
       END,
       'aktif'
FROM `siswa` s
WHERE s.`kelas` IS NOT NULL AND TRIM(s.`kelas`) <> '';
