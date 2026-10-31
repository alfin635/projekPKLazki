<?php
require_once '../includes/auth.php';
require_once '../config/koneksi.php';

/**
 * Normalisasi tingkat supaya variasi lama seperti "Semua Tingkat SMP",
 * "Kelas Semua Tingkat", "kelas vii", atau nilai kosong tidak membuat
 * jenis pembayaran aktif hilang dari dropdown.
 */
function normalisasiTingkatPembayaran($nilai): string {
    $v = strtoupper(trim((string)$nilai));
    if ($v === '' || strpos($v, 'SEMUA') !== false || $v === 'ALL') {
        return 'Semua Tingkat';
    }
    if (preg_match('/\bVIII\b|KELAS\s*8|TINGKAT\s*8/', $v)) return 'VIII';
    if (preg_match('/\bVII\b|KELAS\s*7|TINGKAT\s*7/', $v)) return 'VII';
    if (preg_match('/\bIX\b|KELAS\s*9|TINGKAT\s*9/', $v)) return 'IX';
    return $v;
}

$siswa = $conn->query("
    SELECT s.id_siswa, s.nis, s.nama_siswa, s.kelas,
           COALESCE(NULLIF(TRIM(k.tingkat), ''),
             CASE
               WHEN UPPER(TRIM(s.kelas)) LIKE 'VIII%' THEN 'VIII'
               WHEN UPPER(TRIM(s.kelas)) LIKE 'VII%' THEN 'VII'
               WHEN UPPER(TRIM(s.kelas)) LIKE 'IX%' THEN 'IX'
               ELSE 'VII'
             END
           ) AS tingkat
    FROM siswa s
    LEFT JOIN kelas k ON k.nama_kelas COLLATE utf8mb4_unicode_ci = s.kelas COLLATE utf8mb4_unicode_ci
    WHERE s.status = 'aktif'
    ORDER BY s.kelas, s.nama_siswa
");

$jenisResult = $conn->query("
    SELECT id_jenis, nama_jenis, nominal, tingkat, status
    FROM jenis_pembayaran
    WHERE LOWER(TRIM(status)) = 'aktif'
    ORDER BY nama_jenis
");
$jenisRows = [];
while ($j = $jenisResult->fetch_assoc()) {
    $j['tingkat_normal'] = normalisasiTingkatPembayaran($j['tingkat'] ?? '');
    $jenisRows[] = $j;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int)($_POST['id_siswa'] ?? 0);
    $jid = (int)($_POST['id_jenis'] ?? 0);
    $tanggal = trim($_POST['tanggal_bayar'] ?? '');
    $jumlah = filter_var($_POST['jumlah'] ?? null, FILTER_VALIDATE_FLOAT);
    $metode = $_POST['metode_pembayaran'] ?? 'Tunai';
    $ket = trim($_POST['keterangan'] ?? '');
    $uid = (int)($_SESSION['id_user'] ?? 0);
    $dateObj = DateTime::createFromFormat('!Y-m-d', $tanggal);
    $validDate = $dateObj && $dateObj->format('Y-m-d') === $tanggal;

    if ($sid < 1 || $jid < 1 || $jumlah === false || $jumlah <= 0 || !$validDate || !in_array($metode, ['Tunai', 'Transfer'], true)) {
        $error = 'Periksa kembali siswa, jenis pembayaran, tanggal, metode, dan nominal.';
    } else {
        $check = $conn->prepare("
            SELECT s.kelas,
                   COALESCE(NULLIF(TRIM(k.tingkat), ''),
                     CASE
                       WHEN UPPER(TRIM(s.kelas)) LIKE 'VIII%' THEN 'VIII'
                       WHEN UPPER(TRIM(s.kelas)) LIKE 'VII%' THEN 'VII'
                       WHEN UPPER(TRIM(s.kelas)) LIKE 'IX%' THEN 'IX'
                       ELSE 'VII'
                     END
                   ) AS tingkat_siswa,
                   j.tingkat AS tingkat_jenis
            FROM siswa s
            LEFT JOIN kelas k ON k.nama_kelas COLLATE utf8mb4_unicode_ci = s.kelas COLLATE utf8mb4_unicode_ci
            JOIN jenis_pembayaran j ON j.id_jenis = ? AND LOWER(TRIM(j.status)) = 'aktif'
            WHERE s.id_siswa = ? AND s.status = 'aktif'
        ");
        $check->bind_param('ii', $jid, $sid);
        $check->execute();
        $ok = $check->get_result()->fetch_assoc();

        if (!$ok) {
            $error = 'Siswa atau jenis pembayaran tidak aktif. Periksa status data pada menu terkait.';
        } else {
            $tingkatSiswa = normalisasiTingkatPembayaran($ok['tingkat_siswa'] ?? '');
            $tingkatJenis = normalisasiTingkatPembayaran($ok['tingkat_jenis'] ?? '');
            if ($tingkatJenis !== 'Semua Tingkat' && $tingkatJenis !== $tingkatSiswa) {
                $error = 'Jenis pembayaran ini hanya berlaku untuk kelas ' . htmlspecialchars($tingkatJenis, ENT_QUOTES, 'UTF-8') . '. Pilih jenis yang berlaku untuk kelas ' . htmlspecialchars($tingkatSiswa, ENT_QUOTES, 'UTF-8') . '.';
            } else {
                try {
                    $no = 'TRX-' . date('YmdHis') . '-' . random_int(100, 999);
                    $st = $conn->prepare('INSERT INTO pembayaran(no_transaksi,id_siswa,id_jenis,tanggal_bayar,jumlah,metode_pembayaran,keterangan,id_user) VALUES(?,?,?,?,?,?,?,?)');
                    $st->bind_param('siisdssi', $no, $sid, $jid, $tanggal, $jumlah, $metode, $ket, $uid);
                    $st->execute();
                    header('Location: cetak.php?id=' . $conn->insert_id);
                    exit;
              } catch (Throwable $e) {
    error_log($e->getMessage());
    $error = 'Gagal menyimpan: ' . $e->getMessage();
}
            }
        }
    }
}
$page_title = 'Catat Pembayaran';
$active = 'pembayaran';
$prefix = '../';
include '../includes/header.php'; ?>
<div class="page-heading">
    <div>
        <h1>Catat Pembayaran</h1>
        <p>Pilih tingkat/kelas dan siswa. Jenis pembayaran aktif akan menyesuaikan tingkat siswa.</p>
    </div><a href="index.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>
<div class="panel">
    <div class="panel-head"><strong><i class="bi bi-cash-coin text-primary me-2"></i>Informasi Transaksi</strong><span class="badge-soft">Bon Kas A5</span></div>
    <div class="panel-body">
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if (!$siswa->num_rows): ?><div class="alert alert-warning">Belum ada siswa aktif. Periksa menu Data Siswa.</div><?php endif; ?>
        <?php if (!count($jenisRows)): ?><div class="alert alert-warning">Tidak ditemukan jenis pembayaran aktif. Periksa kolom status pada menu Jenis Pembayaran.</div><?php endif; ?>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Filter Tingkat *</label><select id="filterTingkat" class="form-select">
                    <option value="">Semua Tingkat</option><option value="VII">Kelas VII</option><option value="VIII">Kelas VIII</option><option value="IX">Kelas IX</option>
                </select></div>
                <div class="col-md-8"><label class="form-label">Siswa *</label><select id="idSiswa" name="id_siswa" class="form-select" required>
                    <option value="">Pilih siswa</option>
                    <?php while ($s = $siswa->fetch_assoc()): ?>
                    <option value="<?= (int)$s['id_siswa'] ?>" data-tingkat="<?= e(normalisasiTingkatPembayaran($s['tingkat'])) ?>" data-kelas="<?= e($s['kelas']) ?>" <?= (($_POST['id_siswa'] ?? '') == (string)$s['id_siswa']) ? 'selected' : '' ?>><?= e($s['nis'] . ' — ' . $s['nama_siswa'] . ' (' . ($s['kelas'] ?: 'Kelas belum ditentukan') . ')') ?></option>
                    <?php endwhile; ?>
                </select></div>
                <div class="col-md-6"><label class="form-label">Jenis Pembayaran *</label><select id="jenis" name="id_jenis" class="form-select" required>
                    <option value="">Pilih siswa terlebih dahulu</option>
                    <?php foreach ($jenisRows as $j): ?>
                    <option value="<?= (int)$j['id_jenis'] ?>" data-tingkat="<?= e($j['tingkat_normal']) ?>" data-nominal="<?= e($j['nominal']) ?>" <?= (($_POST['id_jenis'] ?? '') == (string)$j['id_jenis']) ? 'selected' : '' ?>><?= e($j['nama_jenis'] . ' — ' . ($j['tingkat_normal'] === 'Semua Tingkat' ? 'Semua Tingkat' : 'Kelas ' . $j['tingkat_normal']) . ' — ' . rupiah($j['nominal'])) ?></option>
                    <?php endforeach; ?>
                </select><div class="form-text">Jenis pembayaran yang aktif dan berlaku untuk tingkat siswa akan ditampilkan.</div></div>
                <div class="col-md-6"><label class="form-label">Tanggal Bayar *</label><input type="date" name="tanggal_bayar" class="form-control" required value="<?= e($_POST['tanggal_bayar'] ?? date('Y-m-d')) ?>"></div>
                <div class="col-md-6"><label class="form-label">Jumlah Pembayaran (Rp) *</label><input type="number" min="1" step="1" id="jumlah" name="jumlah" class="form-control" required value="<?= e($_POST['jumlah'] ?? '') ?>" placeholder="Contoh: 100000"></div>
                <div class="col-md-6"><label class="form-label">Metode Pembayaran *</label><select name="metode_pembayaran" class="form-select"><option value="Tunai" <?= (($_POST['metode_pembayaran'] ?? 'Tunai') === 'Tunai' ? 'selected' : '') ?>>Tunai</option><option value="Transfer" <?= (($_POST['metode_pembayaran'] ?? '') === 'Transfer' ? 'selected' : '') ?>>Transfer</option></select></div>
                <div class="col-12"><label class="form-label">Keterangan / Periode</label><textarea name="keterangan" rows="3" class="form-control" placeholder="Contoh: SPP bulan Oktober 2026"><?= e($_POST['keterangan'] ?? '') ?></textarea></div>
            </div>
            <div class="d-flex gap-2 mt-4"><button class="btn btn-primary" <?= (!$siswa->num_rows || !count($jenisRows)) ? 'disabled' : '' ?>><i class="bi bi-check2-circle me-1"></i>Simpan &amp; Buka Bon Kas</button><a href="index.php" class="btn btn-light border">Batal</a></div>
        </form>
    </div>
</div>
<script>
const student = document.getElementById('idSiswa');
const filter = document.getElementById('filterTingkat');
const type = document.getElementById('jenis');
const amount = document.getElementById('jumlah');

function syncStudents() {
    const selectedFilter = filter.value;
    Array.from(student.options).forEach(o => {
        if (!o.value) return;
        o.hidden = !!selectedFilter && o.dataset.tingkat !== selectedFilter;
    });
    if (student.selectedOptions[0] && student.selectedOptions[0].hidden) student.value = '';
    syncTypes();
}
function syncTypes() {
    const selectedStudent = student.selectedOptions[0];
    const grade = selectedStudent ? selectedStudent.dataset.tingkat : '';
    let available = 0;
    Array.from(type.options).forEach(o => {
        if (!o.value) return;
        const tier = (o.dataset.tingkat || 'Semua Tingkat').trim();
        const allowed = !!grade && (tier === 'Semua Tingkat' || tier === grade);
        o.hidden = !allowed;
        o.disabled = !allowed;
        if (allowed) available++;
    });
    if (type.selectedOptions[0] && (type.selectedOptions[0].hidden || type.selectedOptions[0].disabled)) type.value = '';
    type.options[0].text = !grade ? 'Pilih siswa terlebih dahulu' : (available ? 'Pilih jenis pembayaran' : 'Tidak ada jenis pembayaran aktif untuk tingkat ini');
}
student.addEventListener('change', () => {
    filter.value = student.selectedOptions[0] ? (student.selectedOptions[0].dataset.tingkat || '') : '';
    syncStudents();
});
filter.addEventListener('change', syncStudents);
type.addEventListener('change', () => {
    const option = type.selectedOptions[0];
    if (option && option.dataset.nominal) amount.value = Math.round(Number(option.dataset.nominal));
});
syncStudents();
</script>
<?php include '../includes/footer.php'; ?>
