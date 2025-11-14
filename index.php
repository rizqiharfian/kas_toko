<?php
// index.php (safe) - login
session_start();
require 'koneksi.php';

// basic checks
if (!isset($mysqli) || !$mysqli) {
    die('Koneksi database tidak ditemukan. Periksa koneksi di koneksi.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // prepare statement
    $sql = 'SELECT id, password, nama FROM users WHERE username = ? LIMIT 1';
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false) {
        // tampilkan error DB yang jelas untuk debugging (hapus/ubah di produksi)
        die('Query prepare gagal: ' . $mysqli->error);
    }

    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    }
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;

    if ($row && isset($row['password']) && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = !empty($row['nama']) ? $row['nama'] : $row['username'];
        header('Location: home.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - Aplikasi Kas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-3">Login Aplikasi Kas</h4>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-2">
              <label class="form-label">Username</label>
              <input class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input class="form-control" name="password" type="password" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary">Masuk</button>
            </div>
          </form>
          <hr>
          <small class="text-muted">Jika belum ada akun, jalankan <code>create_admin.php</code> sekali untuk membuat admin.</small>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
