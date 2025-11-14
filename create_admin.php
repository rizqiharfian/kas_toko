<?php
// create_admin.php - script singkat untuk membuat akun admin
// Usage: letakkan di folder project, buka lewat browser atau jalankan php create_admin.php dari terminal
// Jangan biarkan script ini di server produksi setelah digunakan.

require 'koneksi.php';

$username = 'admin';
$password_plain = 'admin123'; // ganti jika mau password lain
$name = 'Administrator';

$hash = password_hash($password_plain, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare('INSERT INTO users (username, password_hash, nama) VALUES (?,?,?)');
$stmt->bind_param('sss', $username, $hash, $name);
if ($stmt->execute()) {
    echo "Admin created: $username (password: $password_plain)\n";
} else {
    echo "Gagal membuat admin: " . $mysqli->error;
}
echo "\nNOTE: Hapus atau amankan file ini setelah admin dibuat.\n";
