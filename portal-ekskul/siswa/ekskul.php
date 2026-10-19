<?php
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
wajib_login('siswa');

$sid  = (int) $_SESSION['user_id'];
$user = ambil($pdo, 'SELECT * FROM users WHERE id = ?', [$sid])[0] ?? null;
if (!$user) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Proses tombol Gabung / Keluar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['bad', 'Sesi formulir habis. Muat ulang halaman lalu coba lagi.'];
    } else {
        $eid  = (int) ($_POST['ekskul_id'] ?? 0);
        $aksi = $_POST['aksi'] ?? '';
        $ek   = ambil($pdo, 'SELECT nama FROM ekskul WHERE id = ?', [$eid])[0] ?? null;

        if (!$ek) {
            $_SESSION['flash'] = ['bad', 'Ekskul tidak ditemukan.'];
        } elseif ($aksi === 'gabung') {
            $pdo->prepare(
                "INSERT INTO anggota_ekskul (ekskul_id, siswa_id, status) VALUES (?, ?, 'aktif')
                 ON DUPLICATE KEY UPDATE status = 'aktif'"
            )->execute([$eid, $sid]);
            $_SESSION['flash'] = ['ok', 'Kamu sekarang menjadi anggota ' . $ek['nama'] . '.'];
        } elseif ($aksi === 'keluar') {
            $pdo->prepare("UPDATE anggota_ekskul SET status = 'keluar' WHERE ekskul_id = ? AND siswa_id = ?")
                ->execute([$eid, $sid]);
            $_SESSION['flash'] = ['ok', 'Kamu sudah keluar dari ' . $ek['nama'] . '.'];
        }
    }
    header('Location: ekskul.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$daftar = ambil($pdo,
    "SELECT e.*, u.nama AS pembimbing,
            (SELECT COUNT(*) FROM anggota_ekskul a WHERE a.ekskul_id = e.id AND a.status = 'aktif') AS jumlah,
            (SELECT a.status FROM anggota_ekskul a WHERE a.ekskul_id = e.id AND a.siswa_id = ?) AS status_saya
     FROM ekskul e
     LEFT JOIN users u ON u.id = e.pembimbing_id
     ORDER BY e.nama", [$sid]);

$warna = [['#2b7bb9', '#0b3d5c'], ['#34495e', '#0c1c2a'], ['#b3262c', '#5c0f14'],
          ['#3f8f6b', '#103a2a'], ['#c47a1c', '#5a2f08'], ['#6a4fb0', '#2a1f55']];

$judul = 'Daftar Ekskul';
$aktif = 'ekskul';
require __DIR__ . '/../includes/header.php';
?>

<h2 class="judul-hal">Daftar Ekstrakurikuler</h2>
<p class="sub-hal">Pilih ekskul yang ingin kamu ikuti. Kamu bisa bergabung ke lebih dari satu.</p>

<?php if ($flash): ?>
  <div class="flash <?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div>
<?php endif; ?>

<?php if (!$daftar): ?>
  <div class="card"><div class="kosong">Belum ada ekskul yang terdaftar.</div></div>
<?php endif; ?>

<div class="grid-eks">
<?php foreach ($daftar as $k): ?>
  <?php $c = $warna[((int) $k['id'] - 1) % count($warna)]; $anggota = $k['status_saya'] === 'aktif'; ?>
  <article class="card eks-card">
    <div class="eks-top" style="--c1:<?= $c[0] ?>;--c2:<?= $c[1] ?>">
      <?php if ($anggota): ?><span class="lencana">✓ Sudah bergabung</span><?php endif; ?>
    </div>
    <div class="eks-body">
      <h3><?= e($k['nama']) ?></h3>
      <p><?= e($k['deskripsi']) ?></p>
      <div class="eks-meta">
        Pembimbing: <?= e($k['pembimbing'] ?? '-') ?> · <?= (int) $k['jumlah'] ?> anggota
      </div>
    </div>
    <form class="eks-aksi" method="post" action="ekskul.php"
          <?= $anggota ? 'onsubmit="return confirm(\'Yakin ingin keluar dari ekskul ini?\')"' : '' ?>>
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="ekskul_id" value="<?= (int) $k['id'] ?>">
      <?php if ($anggota): ?>
        <button class="btn abu" name="aksi" value="keluar">Keluar dari ekskul</button>
      <?php else: ?>
        <button class="btn" name="aksi" value="gabung">Gabung</button>
      <?php endif; ?>
    </form>
  </article>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
