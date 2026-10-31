<?php
require_once '../includes/auth.php';
require_once '../config/koneksi.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM siswa
        WHERE nis LIKE ? OR nama_siswa LIKE ? OR kelas LIKE ?
        ORDER BY
          CASE
            WHEN UPPER(TRIM(kelas)) LIKE 'VII%' THEN 1
            WHEN UPPER(TRIM(kelas)) LIKE 'VIII%' THEN 2
            WHEN UPPER(TRIM(kelas)) LIKE 'IX%' THEN 3
            ELSE 4
          END,
          kelas ASC, nama_siswa ASC";
$stmt = $conn->prepare($sql);
$like = "%$q%";
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$groups = [];
while ($row = $result->fetch_assoc()) {
    $className = trim((string)($row['kelas'] ?? ''));
    if ($className === '') $className = 'Belum ada kelas';
    $groups[$className][] = $row;
}
$totalSiswa = 0;
foreach ($groups as $groupRows) $totalSiswa += count($groupRows);

$page_title = 'Data Siswa';
$active = 'siswa';
$prefix = '../';
include '../includes/header.php';
?>
<div class="page-heading">
  <div>
    <h1>Data Siswa</h1>
    <p>Kelola identitas peserta didik berdasarkan masing-masing kelas.</p>
  </div>
  <a class="btn btn-primary" href="tambah.php"><i class="bi bi-plus-lg me-1"></i>Tambah Siswa</a>
</div>

<div class="panel mb-3">
  <div class="panel-head">
    <div>
      <strong>Daftar Peserta Didik</strong>
      <div class="text-secondary small mt-1"><?= number_format($totalSiswa) ?> siswa · <?= count($groups) ?> kelompok kelas</div>
    </div>
    <form class="d-flex gap-2 searchbox" method="get">
      <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Cari nama, NIS, kelas...">
      <button class="btn btn-outline-primary" type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
      <?php if ($q !== ''): ?><a class="btn btn-light border" href="index.php" title="Reset pencarian"><i class="bi bi-arrow-counterclockwise"></i></a><?php endif; ?>
    </form>
  </div>
</div>

<?php if (!$groups): ?>
  <div class="panel"><div class="empty py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Data siswa tidak ditemukan.</div></div>
<?php else: ?>
  <?php foreach ($groups as $className => $students): ?>
    <div class="panel mb-4">
      <div class="panel-head">
        <div>
          <strong><i class="bi bi-people me-2 text-primary"></i>Kelas <?= e($className) ?></strong>
          <div class="text-secondary small mt-1"><?= count($students) ?> siswa terdaftar</div>
        </div>
        <span class="badge-soft"><?= count($students) ?> siswa</span>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>JK</th><th>Orang Tua</th><th>No. HP</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($students as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($r['nis']) ?></td>
              <td><strong><?= e($r['nama_siswa']) ?></strong></td>
              <td><?= $r['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
              <td><?= e($r['nama_orang_tua'] ?: '-') ?></td>
              <td><?= e($r['no_hp'] ?: '-') ?></td>
              <td><span class="badge <?= $r['status'] === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= e(ucfirst(str_replace('_', ' ', $r['status']))) ?></span></td>
              <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= (int)$r['id_siswa'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                <a class="btn btn-sm btn-outline-danger" data-confirm="Hapus data siswa ini?" href="hapus.php?id=<?= (int)$r['id_siswa'] ?>" title="Hapus"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
