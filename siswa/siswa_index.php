<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../config/koneksi.php";

$data = mysqli_query($conn,
    "SELECT * FROM siswa ORDER BY id_siswa DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Data Siswa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="d-flex justify-content-between mb-3">

<h3>Data Siswa</h3>

<a href="tambah.php"
   class="btn btn-primary">
   + Tambah Siswa
</a>

</div>

<div class="card shadow">

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>
<th>No</th>
<th>NIS</th>
<th>Nama</th>
<th>L/P</th>
<th>Kelas</th>
<th>No HP</th>
<th>Aksi</th>
</tr>

</thead>

<tbody>

<?php
$no = 1;

while ($row = mysqli_fetch_assoc($data)):
?>

<tr>

<td><?= $no++ ?></td>

<td><?= htmlspecialchars($row['nis']) ?></td>

<td><?= htmlspecialchars($row['nama_siswa']) ?></td>

<td><?= $row['jenis_kelamin'] ?></td>

<td><?= htmlspecialchars($row['kelas']) ?></td>

<td><?= htmlspecialchars($row['no_hp']) ?></td>

<td>

<a href="edit.php?id=<?= $row['id_siswa'] ?>"
   class="btn btn-warning btn-sm">
   Edit
</a>

<a href="hapus.php?id=<?= $row['id_siswa'] ?>"
   onclick="return confirm('Hapus data siswa?')"
   class="btn btn-danger btn-sm">
   Hapus
</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<a href="../dashboard.php"
   class="btn btn-secondary">
   Kembali
</a>

</div>
</div>

</div>

</body>
</html>