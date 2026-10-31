-- Jalankan satu kali melalui phpMyAdmin setelah memilih database alfin.
-- Buat nama kelas konsisten dan sambungkan kembali siswa dari nama kelas lama.
START TRANSACTION;
UPDATE siswa SET kelas='VII-A' WHERE kelas='1';
UPDATE siswa SET kelas='VII-B' WHERE kelas='XI';
UPDATE kelas SET nama_kelas='VII-A', tingkat='VII' WHERE nama_kelas='1' AND tingkat='VII';
UPDATE kelas SET nama_kelas='VII-B', tingkat='VII' WHERE nama_kelas='XI' AND tingkat='VII';
COMMIT;
