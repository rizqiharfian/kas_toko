<?php
require 'koneksi.php';

$KATEGORI = [
    'pembelian_unit' => 'Pembelian Unit',
    'sparepart'      => 'Sparepart',
    'atk'            => 'ATK',
    'transport'      => 'Transport',
    'ekspedisi'      => 'Ekspedisi Manual',
    'ongkir'         => 'Ongkir Paket',
    'makan'          => 'Uang Makan',
    'servis'          => 'Dana Servis',
    'perlengkapan'          => 'Perlengkapan Toko'
];

// FILTER
$q_from = $_GET['from'] ?? '';
$q_to = $_GET['to'] ?? '';
$q_kategori = $_GET['kategori'] ?? '';
$q_jenis = $_GET['jenis'] ?? '';

$where = [];
$params = [];
$types  = '';

if ($q_from) { $where[] = 'tanggal >= ?'; $params[] = $q_from; $types .= 's'; }
if ($q_to) { $where[] = 'tanggal <= ?'; $params[] = $q_to; $types .= 's'; }
if ($q_kategori) { $where[] = 'kategori = ?'; $params[] = $q_kategori; $types .= 's'; }
if ($q_jenis) { $where[] = 'jenis = ?'; $params[] = $q_jenis; $types .= 's'; }

$where_sql = count($where) ? 'WHERE ' . implode(" AND ", $where) : '';

$sql = "
    SELECT t.*, u.nama AS user_name
    FROM transaksi t
    LEFT JOIN users u ON u.id = t.user_id
    $where_sql
    ORDER BY tanggal DESC
";

$stmt = $mysqli->prepare($sql);
if (!$stmt) die("SQL error: " . $mysqli->error);

if (count($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

// Excel-friendly CSV
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=transaksi.csv");

// Tambahkan BOM agar Excel mengenali UTF-8
echo "\xEF\xBB\xBF";

$outf = fopen("php://output", "w");

// Gunakan SEMICOLON agar Excel memisahkan kolom dengan benar
$delimiter = ";";

// HEADER
fputcsv($outf, ["Tanggal", "Jenis", "Kategori", "Keterangan", "Nominal", "User"], $delimiter);

// DATA
while ($r = $res->fetch_assoc()) {
    fputcsv($outf, [
        $r['tanggal'],
        ucfirst($r['jenis']),
        $KATEGORI[$r['kategori']] ?? $r['kategori'],
        $r['keterangan'],
        number_format($r['nominal'], 0, ',', '.'), // Format Rp
        $r['user_name']
    ], $delimiter);
}

fclose($outf);
exit;
