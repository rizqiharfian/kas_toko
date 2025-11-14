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

$q_from = $_GET['from'] ?? '';
$q_to = $_GET['to'] ?? '';
$q_kategori = $_GET['kategori'] ?? '';
$q_jenis = $_GET['jenis'] ?? '';

$where = [];
$params = [];
$types = '';
if ($q_from) { $where[] = 'tanggal >= ?'; $params[] = $q_from; $types.='s'; }
if ($q_to) { $where[] = 'tanggal <= ?'; $params[] = $q_to; $types.='s'; }
if ($q_kategori) { $where[] = 'kategori = ?'; $params[] = $q_kategori; $types.='s'; }
if ($q_jenis) { $where[] = 'jenis = ?'; $params[] = $q_jenis; $types.='s'; }

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT t.*, u.nama as user_name FROM transaksi t LEFT JOIN users u ON t.user_id = u.id $where_sql ORDER BY tanggal DESC";
$stmt = $mysqli->prepare($sql);
if ($stmt) {
    if (count($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $transactions = $res->fetch_all(MYSQLI_ASSOC);
} else {
    $transactions = [];
}
$total_masuk = 0;
$total_keluar = 0;

foreach ($transactions as $t) {
    if ($t['jenis'] === 'masuk') {
        $total_masuk += $t['nominal'];
    } else {
        $total_keluar += $t['nominal'];
    }
}

$saldo = $total_masuk - $total_keluar;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Cetak Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { font-size: 14px; }
    table th { background: #f5f5f5; font-weight: 600; }
    table td, table th { padding: 8px !important; }

    /* Rata kanan untuk nominal */
    .text-end { text-align: right; }

    @media print {
        .no-print { display: none !important; }
        table { font-size: 13px; }
    }
</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="no-print mb-4">

    <div class="card p-3">
      <div class="d-flex flex-wrap gap-1">

            <!-- PER HARI -->
            <a href="print.php?from=<?= date('Y-m-d') ?>&to=<?= date('Y-m-d') ?>"
               class="btn btn-outline-primary">Harian</a>

            <!-- PER MINGGU -->
            <?php 
                $monday = date('Y-m-d', strtotime('monday this week'));
                $sunday = date('Y-m-d', strtotime('sunday this week'));
            ?>
            <a href="print.php?from=<?= $monday ?>&to=<?= $sunday ?>"
               class="btn btn-outline-primary">PerMinggu</a>

            <!-- PER BULAN -->
            <?php 
                $firstMonth = date('Y-m-01');
                $lastMonth = date('Y-m-t');
            ?>
            <a href="print.php?from=<?= $firstMonth ?>&to=<?= $lastMonth ?>"
               class="btn btn-outline-primary">PerBulan</a>

            <!-- PER TAHUN -->
            <?php 
                $firstYear = date('Y-01-01');
                $lastYear = date('Y-12-31');
            ?>
            <a href="print.php?from=<?= $firstYear ?>&to=<?= $lastYear ?>"
               class="btn btn-outline-primary">PerTahun</a>
        </div>
        <hr>
        <h6 class="mb-3">Filter Hari</h6>
        <!-- CUSTOM RANGE -->
        <form action="print.php" method="GET" class="row g-2">
            <div class="col-md-4">
                <label class="form-label small">Dari Tanggal</label>
                <input type="date" name="from" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="date" name="to" class="form-control" required>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

</div>
    <h4>Daftar Transaksi</h4>
    <div class="no-print"><button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button></div>
  </div>
  <table class="table table-sm">
    <thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th>User</th></tr></thead>
    <tbody>
      <?php foreach ($transactions as $t): ?>
      <tr>
        <td><?= e($t['tanggal']) ?></td>
        <td><?= e($t['jenis']) ?></td>
        <td><?= e($KATEGORI[$t['kategori']] ?? $t['kategori']) ?></td>
        <td><?= e($t['keterangan']) ?></td>
        <td>Rp <?= number_format($t['nominal'],0,',','.') ?></td>
        <td><?= e($t['user_name'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!count($transactions)): ?>
      <tr><td colspan="6" class="text-center">Tidak ada transaksi</td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4">Total Pendapatan (Masuk)</th>
            <th colspan="2">Rp <?= number_format($total_masuk,0,',','.') ?></th>
        </tr>
        <tr>
            <th colspan="4">Total Pengeluaran (Keluar)</th>
            <th colspan="2">Rp <?= number_format($total_keluar,0,',','.') ?></th>
        </tr>
        <tr>
            <th colspan="4">Saldo Akhir</th>
            <th colspan="2"><strong>Rp <?= number_format($saldo,0,',','.') ?></strong></th>
        </tr>
    </tfoot>
  </table>
</div>
</body>
</html>
