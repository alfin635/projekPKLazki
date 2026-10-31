<?php require_once '../includes/auth.php';
require_once '../config/koneksi.php';
$row = [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_jenis'] ?? '');
    $nom = (float)($_POST['nominal'] ?? 0);
    $tingkat = $_POST['tingkat'] ?? 'Semua Tingkat';
    $ket = trim($_POST['keterangan'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    if ($nama === '' || $nom < 0 || !in_array($tingkat, ['Semua Tingkat', 'VII', 'VIII', 'IX'], true) || !in_array($status, ['aktif', 'nonaktif'], true)) {
        $error = 'Periksa nama, nominal, tingkat, dan status.';
        $row = $_POST;
    } else {
        $st = $conn->prepare('INSERT INTO jenis_pembayaran(nama_jenis,tingkat,nominal,keterangan,status) VALUES(?,?,?,?,?)');
        $st->bind_param('ssdss', $nama, $tingkat, $nom, $ket, $status);
        $st->execute();
        header('Location: index.php');
        exit;
    }
}
$page_title = 'Tambah Jenis Pembayaran';
$active = 'jenis';
$prefix = '../';
include '../includes/header.php'; ?><div class="page-heading">
    <div>
        <h1>Tambah Jenis Pembayaran</h1>
        <p>Tentukan kategori, nominal standar, dan tingkat SMP yang dikenai biaya.</p>
    </div><a href="index.php" class="btn btn-light border">Kembali</a>
</div>
<div class="panel">
    <div class="panel-body"><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama Jenis *</label><input name="nama_jenis" class="form-control" required value="<?= e($row['nama_jenis'] ?? '') ?>" placeholder="Contoh: SPP Bulanan"></div>
                <div class="col-md-6"><label class="form-label">Nominal Standar (Rp) *</label><input type="number" min="0" step="1" name="nominal" class="form-control" required value="<?= e($row['nominal'] ?? '0') ?>"></div>
                <div class="col-md-6"><label class="form-label">Berlaku untuk Tingkat *</label><select name="tingkat" class="form-select"><?php foreach (['Semua Tingkat', 'VII', 'VIII', 'IX'] as $t): ?><option value="<?= e($t) ?>" <?= ($row['tingkat'] ?? 'Semua Tingkat') === $t ? 'selected' : '' ?>><?= $t === 'Semua Tingkat' ? 'Semua Tingkat SMP' : 'Kelas ' . $t ?></option><?php endforeach; ?></select>
                    <div class="form-text">Jenis biaya khusus kelas VII hanya akan muncul untuk siswa kelas VII.</div>
                </div>
                <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select></div>
                <div class="col-12"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="3"><?= e($row['keterangan'] ?? '') ?></textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4"><button class="btn btn-primary">Simpan Jenis Pembayaran</button><a href="index.php" class="btn btn-light border">Batal</a></div>
        </form>
    </div>
</div><?php include '../includes/footer.php'; ?>