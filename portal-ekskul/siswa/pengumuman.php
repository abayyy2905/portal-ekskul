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

$ekskulSaya = "SELECT ekskul_id FROM anggota_ekskul WHERE siswa_id = ? AND status = 'aktif'";
$opsi = ambil($pdo, "SELECT id, nama FROM ekskul WHERE id IN ($ekskulSaya) ORDER BY nama", [$sid]);

$q   = trim($_GET['q'] ?? '');
$fe  = (int) ($_GET['ekskul'] ?? 0);
$per = 8;

$where = "(p.ekskul_id IN ($ekskulSaya) OR p.ekskul_id IS NULL)";
$par   = [$sid];
if ($fe > 0) {
    $where .= ' AND p.ekskul_id = ?';
    $par[] = $fe;
}
if ($q !== '') {
    $where .= ' AND (p.judul LIKE ? OR p.isi LIKE ?)';
    $par[] = '%' . $q . '%';
    $par[] = '%' . $q . '%';
}

$total   = (int) ambil($pdo, "SELECT COUNT(*) AS n FROM pengumuman p WHERE $where", $par)[0]['n'];
$halaman = max(1, (int) ceil($total / $per));
$hal     = min($halaman, max(1, (int) ($_GET['hal'] ?? 1)));
$offset  = ($hal - 1) * $per;

$daftar = ambil($pdo,
    "SELECT p.*, e.nama AS ekskul_nama, u.nama AS penulis,
            TIMESTAMPDIFF(MINUTE, p.dibuat_pada, NOW()) AS menit
     FROM pengumuman p
     LEFT JOIN ekskul e ON e.id = p.ekskul_id
     JOIN users u ON u.id = p.dibuat_oleh
     WHERE $where
     ORDER BY p.dibuat_pada DESC
     LIMIT $per OFFSET $offset", $par);

$warna = ['#f97316', '#8b5cf6', '#06b6d4', '#22a55a', '#e11d48', '#0ea5e9'];
$link  = fn(int $n) => '?' . http_build_query(array_filter(['q' => $q, 'ekskul' => $fe ?: null, 'hal' => $n]));

$judul = 'Pengumuman';
$aktif = 'pengumuman';
require __DIR__ . '/../includes/header.php';
?>

<h2 class="judul-hal">Pengumuman</h2>
<p class="sub-hal">Pengumuman dari pembimbing ekskul yang kamu ikuti dan pengumuman umum sekolah.</p>

<form class="filter" method="get" action="pengumuman.php">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul atau isi pengumuman…" aria-label="Cari pengumuman">
  <select name="ekskul" aria-label="Filter ekskul" onchange="this.form.submit()">
    <option value="0">Semua ekskul</option>
    <?php foreach ($opsi as $o): ?>
      <option value="<?= (int) $o['id'] ?>" <?= $fe === (int) $o['id'] ? 'selected' : '' ?>><?= e($o['nama']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn" type="submit">Cari</button>
</form>

<div class="card">
  <?php if (!$daftar): ?>
    <div class="kosong"><?= $q !== '' ? 'Tidak ada pengumuman yang cocok dengan pencarian.' : 'Belum ada pengumuman.' ?></div>
  <?php endif; ?>
  <?php foreach ($daftar as $p): ?>
    <?php $asal = $p['ekskul_nama'] ? 'Pembina ' . $p['ekskul_nama'] : 'Pengumuman umum'; ?>
    <article class="pgm" style="--c:<?= $warna[(int) $p['ekskul_id'] % count($warna)] ?>">
      <div class="pf"><?= e(inisial($p['ekskul_nama'] ?: 'Pengumuman Umum')) ?></div>
      <div>
        <small><?= e($asal) ?></small>
        <b><?= e($p['judul']) ?></b>
        <p><?= nl2br(e($p['isi'])) ?></p>
        <small>Oleh <?= e($p['penulis']) ?> · <?= e(waktu_lalu((int) $p['menit'])) ?></small>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php if ($halaman > 1): ?>
  <nav class="pager" aria-label="Halaman">
    <?php if ($hal > 1): ?><a href="<?= e($link($hal - 1)) ?>">‹ Sebelumnya</a><?php else: ?><span class="off">‹ Sebelumnya</span><?php endif; ?>
    <?php for ($i = 1; $i <= $halaman; $i++): ?>
      <?php if ($i === $hal): ?><span class="on"><?= $i ?></span><?php else: ?><a href="<?= e($link($i)) ?>"><?= $i ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if ($hal < $halaman): ?><a href="<?= e($link($hal + 1)) ?>">Berikutnya ›</a><?php else: ?><span class="off">Berikutnya ›</span><?php endif; ?>
  </nav>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
