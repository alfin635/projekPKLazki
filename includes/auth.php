<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['login'])) { header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/siswa/') !== false || strpos($_SERVER['PHP_SELF'], '/guru/') !== false || strpos($_SERVER['PHP_SELF'], '/pembayaran/') !== false || strpos($_SERVER['PHP_SELF'], '/jenis_pembayaran/') !== false || strpos($_SERVER['PHP_SELF'], '/jenis_pembayaran/') !== false ? '../auth/login.php' : 'auth/login.php')); exit; }
function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
?>