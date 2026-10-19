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

// Pilihan filter
$opsi  = ambil($pdo, "SELECT id, nama FROM ekskul WHERE id IN ($ekskulSaya) ORDER BY nama", [$sid]);
$waktu = ($_GET['waktu'] ?? '') === 'lalu' ? 'lalu' : 'mendatang';
$fe    = (int) ($_GET['ekskul'] ?? 0);

$sql = "SELECT j.*, e.nama AS ekskul_nama
        FROM jadwal j JOIN ekskul e ON e.id = j.ekskul_id
        WHERE j.ekskul_id IN ($ekskulSaya)";
$par = [$sid];
if ($fe > 0) {
    $sql .= ' AND j.ekskul_id = ?';
    $par[] = $fe;
}
$sql .= $waktu === 'lalu'
    ? ' AND j.tanggal < CURDATE() ORDER BY j.tanggal DESC, j.jam_mulai DESC LIMIT 60'
    : ' AND j.tanggal >= CURDATE() ORDER BY j.tanggal, j.jam_mulai LIMIT 60';
$jadwal = ambil($pdo, $sql, $par);

// Kelompokkan per tanggal
$grup = [];
foreach ($jadwal as $j) {
    $grup[$j['tanggal']][] = $j;
}

$status = ['dalam_proses' => ['Dalam Proses', 'ok'], 'terjadwal' => ['Terjadwal', 'info'], 'selesai' => ['Selesai', 'ok']];
$jam = fn(?string $t) => $t ? str_replace(':', '.', substr($t, 0, 5)) : '';

$judul = 'Jadwal';
$aktif = 'jadwal';
require __DIR__ . '/../includes/header.php';
?>

<h2 class="judul-hal">Jadwal Kegiatan</h2>
<p class="sub-hal">Jadwal latihan dan agenda dari ekskul yang kamu ikuti.</p>

<form class="filter" method="get" action="jadwal.php">
  <div class="tabs">
    <a class="<?= $waktu === 'mendatang' ? 'on' : '' ?>" href="?waktu=mendatang<?= $fe ? '&ekskul=' . $fe : '' ?>">Mendatang</a>
    <a class="<?= $waktu === 'lalu' ? 'on' : '' ?>" href="?waktu=lalu<?= $fe ? '&ekskul=' . $fe : '' ?>">Sudah lewat</a>
  </div>
  <input type="hidden" name="waktu" value="<?= e($waktu) ?>">
  <select name="ekskul" aria-label="Filter ekskul" onchange="this.form.submit()">
    <option value="0">Semua ekskul</option>
    <?php foreach ($opsi as $o): ?>
      <option value="<?= (int) $o['id'] ?>" <?= $fe === (int) $o['id'] ? 'selected' : '' ?>><?= e($o['nama']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (!$opsi): ?>
  <div class="card"><div class="kosong">Kamu belum bergabung ke ekskul mana pun. Buka <a href="ekskul.php"><u>Daftar Ekskul</u></a> untuk bergabung.</div></div>
<?php elseif (!$grup): ?>
  <div class="card"><div class="kosong"><?= $waktu === 'lalu' ? 'Belum ada kegiatan yang sudah lewat.' : 'Tidak ada jadwal mendatang.' ?></div></div>
<?php endif; ?>

<?php foreach ($grup as $tanggal => $items): ?>
  <section class="grup">
    <h4><b><?= e(hari_label($tanggal)) ?></b> · <?= e(tanggal_panjang($tanggal)) ?></h4>
    <div class="card">
      <?php foreach ($items as $j): ?>
        <?php $st = $waktu === 'lalu' ? ['Selesai', 'ok'] : $status[$j['status']]; ?>
        <div class="jd">
          <div class="jam"><?= e($jam($j['jam_mulai'])) ?><?= $j['jam_selesai'] ? '–' . e($jam($j['jam_selesai'])) : '' ?></div>
          <div class="nama">
            <b><?= e($j['judul']) ?></b>
            <small><?= e($j['ekskul_nama']) ?> · <?= e($j['tempat']) ?></small>
          </div>
          <div class="tg">
            <span class="tag"><?= e(ucfirst($j['jenis'])) ?></span>
            <span class="tag <?= $st[1] ?>"><?= e($st[0]) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
