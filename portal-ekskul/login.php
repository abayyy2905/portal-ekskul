<?php
require __DIR__ . '/config/koneksi.php';
require __DIR__ . '/includes/auth.php';

// Kalau sudah login, langsung ke dashboard
if (sudah_login()) {
    header('Location: ' . dashboard_url($_SESSION['peran']));
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$username = '';
$peran = 'siswa';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $peran    = ($_POST['peran'] ?? '') === 'pembimbing' ? 'pembimbing' : 'siswa';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $error = 'Sesi formulir habis. Muat ulang halaman lalu coba lagi.';
    } elseif ($username === '' || $password === '') {
        $error = 'Isi username dan password terlebih dahulu.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND peran = ? LIMIT 1');
        $stmt->execute([$username, $peran]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['peran']   = $user['peran'];
            header('Location: ' . dashboard_url($user['peran']));
            exit;
        }
        $error = 'Username, password, atau peran tidak sesuai.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Portal Ekstrakurikuler Trisula Praja</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Chango&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body>
  <div class="top"><div class="crest">🔱</div></div>

  <main class="wrap">
    <section class="judul">
      <h1>PORTAL<br>EKSTRAKURIKULER</h1>
      <h2>TRISULA PRAJA</h2>
    </section>

    <section class="kolom">
      <form class="box" method="post" action="login.php" novalidate>
        <h3>Selamat Datang Kembali!</h3>
        <p class="sub">Masuk untuk melanjutkan ke dasbor Anda</p>

        <?php if ($error): ?>
          <div class="error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" placeholder="Masukkan username" autocomplete="username" autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password">

        <label>Silakan pilih peran Anda!</label>
        <div class="peran">
          <label><input type="radio" name="peran" value="siswa" <?= $peran === 'siswa' ? 'checked' : '' ?>><span>Siswa</span></label>
          <label><input type="radio" name="peran" value="pembimbing" <?= $peran === 'pembimbing' ? 'checked' : '' ?>><span>Pembimbing</span></label>
        </div>

        <button class="masuk" type="submit">Login</button>
        <p class="lupa"><a href="#">Lupa password?</a></p>
      </form>
      <p class="catatan">Dengan masuk, Anda menyetujui Syarat Layanan dan Kebijakan Privasi kami.</p>
    </section>
  </main>
</body>
</html>
