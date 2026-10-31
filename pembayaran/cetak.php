<?php
require_once '../includes/auth.php';
require_once '../config/koneksi.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$st = $conn->prepare("SELECT p.*, s.nama_siswa, s.nis, s.kelas, j.nama_jenis, u.nama AS petugas, pg.nama_sekolah, pg.alamat, pg.no_telepon, pg.kepala_sekolah FROM pembayaran p JOIN siswa s ON s.id_siswa=p.id_siswa JOIN jenis_pembayaran j ON j.id_jenis=p.id_jenis JOIN users u ON u.id_user=p.id_user LEFT JOIN pengaturan pg ON pg.id_pengaturan=1 WHERE p.id_pembayaran=? LIMIT 1");
$st->bind_param('i', $id); $st->execute(); $r = $st->get_result()->fetch_assoc();
if (!$r) { http_response_code(404); exit('Data bon kas tidak ditemukan. Pastikan transaksi masih tersedia.'); }
function terbilang($n) {
  $n = (int)round($n);
  $kata = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
  if ($n < 12) return $kata[$n];
  if ($n < 20) return terbilang($n - 10) . ' belas';
  if ($n < 100) return terbilang((int)($n/10)) . ' puluh' . (($n%10) ? ' ' . terbilang($n%10) : '');
  if ($n < 200) return 'seratus' . (($n-100) ? ' ' . terbilang($n-100) : '');
  if ($n < 1000) return terbilang((int)($n/100)) . ' ratus' . (($n%100) ? ' ' . terbilang($n%100) : '');
  if ($n < 2000) return 'seribu' . (($n-1000) ? ' ' . terbilang($n-1000) : '');
  if ($n < 1000000) return terbilang((int)($n/1000)) . ' ribu' . (($n%1000) ? ' ' . terbilang($n%1000) : '');
  if ($n < 1000000000) return terbilang((int)($n/1000000)) . ' juta' . (($n%1000000) ? ' ' . terbilang($n%1000000) : '');
  return terbilang((int)($n/1000000000)) . ' miliar' . (($n%1000000000) ? ' ' . terbilang($n%1000000000) : '');
}
$namaSekolah = $r['nama_sekolah'] ?: 'MTs Azki Alazhar';
$nomorBon = 'BK-' . str_pad((string)$r['id_pembayaran'], 5, '0', STR_PAD_LEFT);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bon Kas <?= e($nomorBon) ?></title>
<style>
*{box-sizing:border-box}body{margin:0;background:#eef2f7;color:#172b4d;font-family:Arial,Helvetica,sans-serif;font-size:13px}.toolbar{max-width:820px;margin:22px auto 12px;display:flex;justify-content:space-between;gap:10px}.toolbar a,.toolbar button{border:0;border-radius:7px;padding:10px 15px;text-decoration:none;font-weight:700;cursor:pointer;font-size:13px}.back{background:#fff;color:#203b60;border:1px solid #d8e1ed!important}.print{background:#1d4ed8;color:white}.sheet{width:190mm;min-height:130mm;margin:0 auto 24px;background:#fff;border:1px solid #d7dfeb;padding:12mm;box-shadow:0 8px 30px #18304f12}.school{text-align:center;border-bottom:2px solid #183b63;padding-bottom:10px}.school h1{font-size:19px;letter-spacing:.5px;margin:0 0 5px}.school p{margin:3px 0;color:#52647c;font-size:11px}.title{text-align:center;margin:14px 0 12px}.title h2{font-size:19px;margin:0;text-decoration:underline;letter-spacing:1px}.title p{font-size:11px;margin:5px 0;color:#52647c}.meta{display:flex;justify-content:space-between;gap:20px;margin:12px 0}.meta div{line-height:1.8}.meta strong{display:inline-block;min-width:90px}.bodyline{display:grid;grid-template-columns:150px 1fr;gap:8px;padding:9px 0;border-bottom:1px dotted #aab7c8;line-height:1.5}.label{font-weight:700}.amount{font-weight:800;font-size:18px;color:#142f52}.words{background:#f5f8fc;border:1px solid #dce5f0;border-radius:5px;padding:10px;font-style:italic;min-height:38px}.signatures{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;text-align:center;margin-top:24px}.signatures div{min-height:85px}.signatures .space{height:48px}.signatures strong{display:block;text-decoration:underline}.note{font-size:10px;color:#687991;margin-top:12px}.stamp{display:inline-block;border:1px dashed #9eacc0;padding:5px 10px;color:#667891;font-size:10px}@page{size:A5 portrait;margin:8mm}@media print{body{background:white}.toolbar{display:none}.sheet{width:auto;min-height:0;margin:0;border:1px solid #222;box-shadow:none;padding:8mm;page-break-inside:avoid}.school h1{font-size:17px}.title{margin:10px 0}.bodyline{padding:7px 0}.signatures{margin-top:18px}}
</style></head><body>
<div class="toolbar"><a class="back" href="index.php">← Kembali ke pembayaran</a><button class="print" onclick="window.print()">🖨 Cetak Bon Kas</button></div>
<main class="sheet"><header class="school"><h1><?= e(strtoupper($namaSekolah)) ?></h1><p><?= e($r['alamat'] ?: 'Administrasi Tata Usaha Sekolah') ?></p><?php if(!empty($r['no_telepon'])):?><p>Telp. <?= e($r['no_telepon']) ?></p><?php endif;?></header>
<div class="title"><h2>BON KAS</h2><p>BUKTI PENERIMAAN PEMBAYARAN</p></div>
<div class="meta"><div><strong>No. Bon</strong>: <?= e($nomorBon) ?><br><strong>No. Referensi</strong>: <?= e($r['no_transaksi']) ?></div><div><strong>Tanggal</strong>: <?= date('d-m-Y',strtotime($r['tanggal_bayar'])) ?><br><strong>Metode</strong>: <?= e($r['metode_pembayaran']) ?></div></div>
<div class="bodyline"><div class="label">Telah diterima dari</div><div>: <?= e($r['nama_siswa']) ?> (NIS: <?= e($r['nis']) ?>), Kelas <?= e($r['kelas'] ?: '-') ?></div></div>
<div class="bodyline"><div class="label">Uang sejumlah</div><div class="amount">: <?= rupiah($r['jumlah']) ?></div></div>
<div class="bodyline"><div class="label">Terbilang</div><div class="words"><?= e(ucfirst(trim(terbilang($r['jumlah'])))) ?> rupiah</div></div>
<div class="bodyline"><div class="label">Untuk pembayaran</div><div>: <?= e($r['nama_jenis']) ?><?= $r['keterangan'] ? ' — '.e($r['keterangan']) : '' ?></div></div>
<div class="signatures"><div><p>Penyetor</p><div class="space"></div><strong><?= e($r['nama_siswa']) ?></strong></div><div><p>Petugas Tata Usaha</p><div class="space"></div><strong><?= e($r['petugas']) ?></strong></div><div><p>Mengetahui</p><div class="space"></div><strong><?= e($r['kepala_sekolah'] ?: 'Kepala Sekolah') ?></strong></div></div>
<p class="note">Bon kas ini merupakan bukti pembayaran yang tercatat pada sistem administrasi sekolah. Simpan sebagai bukti transaksi.</p></main>
<script>window.addEventListener('load',()=>{if(new URLSearchParams(location.search).get('autoprint')==='1')window.print()});</script></body></html>
