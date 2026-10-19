<?php
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
wajib_login('pembimbing');

$user = ambil($pdo, 'SELECT * FROM users WHERE id = ?', [(int) $_SESSION['user_id']])[0] ?? null;
if (!$user) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

$judul = 'Dashboard Pembimbing';
$aktif = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="wel">
  <p>Selamat datang kembali 👋</p>
  <h1><?= e($user['nama']) ?></h1>
  <span class="chip">Pembimbing</span>
</div>
<div class="card" style="margin-top:16px"><div class="kosong">Dashboard pembimbing dibuat di langkah berikutnya.</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
