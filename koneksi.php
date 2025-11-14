<?php
// koneksi.php - konfigurasi koneksi database
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'kas_toko';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('Koneksi gagal: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

