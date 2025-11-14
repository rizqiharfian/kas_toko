<?php
session_start();
require 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$KATEGORI = [
    'pembelian_unit' => 'Pembelian Unit',
    'sparepart'      => 'Sparepart',
    'atk'            => 'ATK',
    'transport'      => 'Transport',
    'ekspedisi'      => 'Ekspedisi Manual',
    'ongkir'         => 'Ongkir Paket',
    'makan'          => 'Uang Makan',
    'servis'         => 'Dana Servis',
    'perlengkapan'   => 'Perlengkapan Toko'
];


// ----------------------------
// HANDLE INPUT (ADD / EDIT / DELETE)
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';

    if ($do === 'add') {
        $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');
        $jenis      = $_POST['jenis'] ?? '';
        $kategori   = $_POST['kategori'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';
        $nominal    = (int) ($_POST['nominal'] ?? 0);
        $user_id    = $_SESSION['user_id'];

        if (($jenis==='masuk' || $jenis==='keluar') && isset($KATEGORI[$kategori]) && $nominal>0) {
            $stmt = $mysqli->prepare(
                "INSERT INTO transaksi (tanggal, jenis, kategori, keterangan, nominal, user_id)
                 VALUES (?,?,?,?,?,?)"
            );
            $stmt->bind_param('ssssii', $tanggal, $jenis, $kategori, $keterangan, $nominal, $user_id);
            $stmt->execute();
            header('Location: home.php');
            exit;
        }

    } elseif ($do === 'edit') {
        $id         = (int) $_POST['id'];
        $tanggal    = $_POST['tanggal'];
        $jenis      = $_POST['jenis'];
        $kategori   = $_POST['kategori'];
        $keterangan = $_POST['keterangan'];
        $nominal    = (int) $_POST['nominal'];

        $stmt = $mysqli->prepare(
            "UPDATE transaksi SET tanggal=?, jenis=?, kategori=?, keterangan=?, nominal=? WHERE id=?"
        );
        $stmt->bind_param('ssssii', $tanggal, $jenis, $kategori, $keterangan, $nominal, $id);
        $stmt->execute();
        header('Location: home.php');
        exit;

    } elseif ($do === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $mysqli->prepare("DELETE FROM transaksi WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header('Location: home.php');
        exit;
    }
}


// ----------------------------
// FILTER
// ----------------------------
$q_from     = $_GET['from'] ?? '';
$q_to       = $_GET['to'] ?? '';
$q_kategori = $_GET['kategori'] ?? '';
$q_jenis    = $_GET['jenis'] ?? '';

$where = [];
$params = [];
$types  = '';

if ($q_from)     { $where[] = "tanggal >= ?";  $params[] = $q_from;     $types .= 's'; }
if ($q_to)       { $where[] = "tanggal <= ?";  $params[] = $q_to;       $types .= 's'; }
if ($q_kategori) { $where[] = "kategori = ?";  $params[] = $q_kategori; $types .= 's'; }
if ($q_jenis)    { $where[] = "jenis = ?";     $params[] = $q_jenis;    $types .= 's'; }

$where_sql = count($where) ? "WHERE ".implode(" AND ", $where) : "";


// ----------------------------------------
// FIX UTAMA — ganti u.name menjadi u.nama
// ----------------------------------------
$sql = "
    SELECT t.*, COALESCE(u.nama, u.username) AS user_name
    FROM transaksi t
    LEFT JOIN users u ON t.user_id = u.id
    $where_sql
    ORDER BY tanggal DESC
";

$stmt = $mysqli->prepare($sql);
if ($stmt) {
    if (count($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);

} else {
    die("Query gagal: ".$mysqli->error);
}


// Hitung total saldo
$total_masuk  = (int) $mysqli->query("SELECT IFNULL(SUM(nominal),0) as t FROM transaksi WHERE jenis='masuk'")->fetch_assoc()['t'];
$total_keluar = (int) $mysqli->query("SELECT IFNULL(SUM(nominal),0) as t FROM transaksi WHERE jenis='keluar'")->fetch_assoc()['t'];
$saldo_all    = $total_masuk - $total_keluar;

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>KAS DR.COM</title>
<link rel="icon" type="image/png" href="assets/drcom.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="home.php">
        <img src="assets/drcom.png" 
             alt="Logo DR.COM" 
             style="height:35px; width:auto; margin-right:8px;">
        <span>DR.COM</span>
    </a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <!-- navbar -->
        <li class="nav-item"><span class="nav-link">Hi, <?= e($_SESSION['user_name'] ?? ($_SESSION['username'] ?? 'Administrator')) ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>


<div class="container my-4">
  <div class="row mb-3">

    <!-- SIDEBAR KIRI -->
    <div class="col-md-4">

      <div class="card mb-2">
        <div class="card-body text-center">
          <h6>Saldo Semua</h6>
          <h4>Rp <?= number_format($saldo_all,0,',','.') ?></h4>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h6>Tambah Transaksi</h6>
          <form method="post">
            <input type="hidden" name="do" value="add">
            <div class="mb-2"><label class="form-label">Tanggal</label>
              <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-2"><label class="form-label">Jenis</label>
              <select name="jenis" class="form-select" required>
                <option value="masuk">Masuk (Pembelian Unit)</option>
                <option value="keluar">Keluar</option>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Kategori</label>
              <select name="kategori" class="form-select" required>
                <?php foreach ($KATEGORI as $k=>$v): ?>
                <option value="<?= e($k) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-2"><label class="form-label">Nominal</label>
              <input type="number" name="nominal" class="form-control" required>
            </div>
            <div class="mb-2"><label class="form-label">Keterangan</label>
              <input type="text" name="keterangan" class="form-control">
            </div>
            <div class="d-grid"><button class="btn btn-success">Simpan</button></div>
          </form>
        </div>
      </div>

      <div class="card mt-2">
        <div class="card-body">
          <h6>Export</h6>
          <a class="btn btn-outline-primary w-100 mb-2"
             href="export_excel.php?from=<?= e($q_from) ?>&to=<?= e($q_to) ?>&kategori=<?= e($q_kategori) ?>&jenis=<?= e($q_jenis) ?>">
            Export Excel (CSV)
          </a>
          <a class="btn btn-outline-secondary w-100"
             href="print.php?from=<?= e($q_from) ?>&to=<?= e($q_to) ?>&kategori=<?= e($q_kategori) ?>&jenis=<?= e($q_jenis) ?>"
             target="_blank">
            Cetak / Export PDF
          </a>
        </div>
      </div>
    </div>


    <!-- KANAN -->
    <div class="col-md-8">
      <div class="card mb-3">
        <div class="card-body">
          <form class="row g-2" method="get">
            <div class="col-md-3"><input type="date" name="from" class="form-control" value="<?= e($q_from) ?>"></div>
            <div class="col-md-3"><input type="date" name="to" class="form-control" value="<?= e($q_to) ?>"></div>
            <div class="col-md-3">
              <select name="kategori" class="form-select">
                <option value="">Semua Kategori</option>
                <?php foreach ($KATEGORI as $k=>$v): ?>
                <option value="<?= e($k) ?>" <?= $q_kategori===$k ? 'selected':'' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select name="jenis" class="form-select">
                <option value="">Semua Jenis</option>
                <option value="masuk" <?= $q_jenis==='masuk' ? 'selected':'' ?>>Masuk</option>
                <option value="keluar" <?= $q_jenis==='keluar' ? 'selected':'' ?>>Keluar</option>
              </select>
            </div>
            <div class="col-12 text-end mt-2">
              <button class="btn btn-primary">Filter</button>
              <a class="btn btn-link" href="home.php">Reset</a>
            </div>
          </form>
        </div>
      </div>


      <div class="card">
        <div class="card-body">
          <h5>Daftar Transaksi</h5>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jenis</th>
                  <th>Kategori</th>
                  <th>Keterangan</th>
                  <th>Nominal</th>
                  <th>User</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($transactions)): foreach ($transactions as $t): ?>
                <tr>
                  <td><?= e($t['tanggal']) ?></td>
                  <td><?= e(ucfirst($t['jenis'])) ?></td>
                  <td><?= e($KATEGORI[$t['kategori']] ?? $t['kategori']) ?></td>
                  <td><?= e($t['keterangan']) ?></td>
                  <td>Rp <?= number_format($t['nominal'],0,',','.') ?></td>
                  <td><?= e($t['user_name'] ?? ($t['username'] ?? '-')) ?></td>
                  <td>
                    <a href="edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="post" style="display:inline-block" onsubmit="return confirm('Hapus transaksi?')">
                      <input type="hidden" name="do" value="delete">
                      <input type="hidden" name="id" value="<?= $t['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="7" class="text-center">Tidak ada transaksi</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

</body>
</html>
