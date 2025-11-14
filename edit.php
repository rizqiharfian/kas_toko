<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$id = (int) ($_GET['id'] ?? 0);
if ($id<=0) { header('Location: home.php'); exit; }
$stmt = $mysqli->prepare('SELECT * FROM transaksi WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();
if (!$tx) { header('Location: home.php'); exit; }

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? $tx['tanggal'];
    $jenis = $_POST['jenis'] ?? $tx['jenis'];
    $kategori = $_POST['kategori'] ?? $tx['kategori'];
    $keterangan = $_POST['keterangan'] ?? $tx['keterangan'];
    $nominal = (int) ($_POST['nominal'] ?? $tx['nominal']);
    $stmt = $mysqli->prepare('UPDATE transaksi SET tanggal=?, jenis=?, kategori=?, keterangan=?, nominal=? WHERE id=?');
    $stmt->bind_param('ssssii', $tanggal, $jenis, $kategori, $keterangan, $nominal, $id);
    $stmt->execute();
    header('Location: home.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Transaksi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h3>Edit Transaksi</h3>
  <form method="post">
    <div class="mb-2"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= e($tx['tanggal']) ?>" required></div>
    <div class="mb-2"><label class="form-label">Jenis</label>
      <select name="jenis" class="form-select" required>
        <option value="masuk" <?= $tx['jenis']==='masuk' ? 'selected':'' ?>>Masuk</option>
        <option value="keluar" <?= $tx['jenis']==='keluar' ? 'selected':'' ?>>Keluar</option>
      </select>
    </div>
    <div class="mb-2"><label class="form-label">Kategori</label>
      <select name="kategori" class="form-select" required>
        <?php foreach ($KATEGORI as $k=>$label): ?>
          <option value="<?= e($k) ?>" <?= $tx['kategori']===$k ? 'selected':'' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-2"><label class="form-label">Nominal</label><input type="number" name="nominal" class="form-control" value="<?= e($tx['nominal']) ?>" required></div>
    <div class="mb-2"><label class="form-label">Keterangan</label><input type="text" name="keterangan" class="form-control" value="<?= e($tx['keterangan']) ?>"></div>
    <div class="d-grid"><button class="btn btn-primary">Simpan</button></div>
  </form>
  <a href="home.php" class="btn btn-link mt-2">Kembali</a>
</div>
</body>
</html>
