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

if (count($params)) $stmt->bind_param($types, ...$params);

$stmt->execute();
$res = $stmt->get_result();

// HEADER UNTUK EXCEL
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transaksi.xls");
header("Pragma: no-cache");
header("Expires: 0");

// TABEL HTML —> Excel akan membacanya sebagai spreadsheet
echo '
<style>
    table {
        border-collapse: collapse;
    }
    th {
        background: #e1e1e1;
        font-weight: bold;
        padding: 8px;
        border: 1px solid #999;
    }
    td {
        padding: 6px;
        border: 1px solid #ccc;
    }
</style>
';

echo "<table>";
echo "<tr>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th>Kategori</th>
        <th>Keterangan</th>
        <th>Nominal</th>
        <th>User</th>
      </tr>";

while ($r = $res->fetch_assoc()) {
    echo "<tr>
            <td>{$r['tanggal']}</td>
            <td>".ucfirst($r['jenis'])."</td>
            <td>".($KATEGORI[$r['kategori']] ?? $r['kategori'])."</td>
            <td>{$r['keterangan']}</td>
            <td>".number_format($r['nominal'],0,',','.')."</td>
            <td>{$r['user_name']}</td>
          </tr>";
}

echo "</table>";
exit;
?>
