<?php
/**
 * Konfigurasi database lokal XAMPP.
 * Database yang digunakan sesuai permintaan: alfin
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "alfin";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");

    // Alias kompatibilitas untuk file lama yang menggunakan $mysqli.
    $mysqli = $conn;
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit(
        "Koneksi database gagal. Pastikan MySQL aktif dan database 'alfin' sudah dibuat. " .
        "Detail: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8")
    );
}
?>
