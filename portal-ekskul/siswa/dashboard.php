<?php
require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
wajib_login('siswa');

$sid = (int) $_SESSION['user_id'];

// Data siswa
$user = ambil($pdo, 'SELECT * FROM users WHERE id = ?', [$sid])[0] ?? null;
if (!$user) {
    header('Location: ' . BASE_URL . '/logout.php');
    exit;
}

// Potongan SQL: ekskul yang diikuti siswa
$ekskulSaya = "SELECT ekskul_id FROM anggota_ekskul WHERE siswa_id = ? AND status = 'aktif'";

// Pengumuman terbaru
$pengumuman = ambil($pdo,
    "SELECT p.*, e.nama AS ekskul_nama, TIMESTAMPDIFF(MINUTE, p.dibuat_pada, NOW()) AS menit
     FROM pengumuman p
     LEFT JOIN ekskul e ON e.id = p.ekskul_id
     WHERE p.ekskul_id IN ($ekskulSaya) OR p.ekskul_id IS NULL
     ORDER BY p.dibuat_pada DESC LIMIT 3", [$sid]);

$baruHariIni = ambil($pdo,
    "SELECT COUNT(*) AS n FROM pengumuman
     WHERE DATE(dibuat_pada) = CURDATE() AND (ekskul_id IN ($ekskulSaya) OR ekskul_id IS NULL)", [$sid])[0]['n'];

// Tugas yang belum dikumpulkan
$tugas = ambil($pdo,
    "SELECT t.* FROM tugas t
     WHERE t.ekskul_id IN ($ekskulSaya)
       AND t.tenggat >= NOW() - INTERVAL 7 DAY
       AND NOT EXISTS (SELECT 1 FROM pengumpulan_tugas k WHERE k.tugas_id = t.id AND k.siswa_id = ?)
     ORDER BY t.tenggat", [$sid, $sid]);
$tugasAktif = count(array_filter($tugas, fn($t) => strtotime($t['tenggat']) >= time()));

// Jadwal 7 hari ke depan
$jadwal = ambil($pdo,
    "SELECT * FROM jadwal
     WHERE ekskul_id IN ($ekskulSaya)
       AND tanggal BETWEEN CURDATE() AND CURDATE() + INTERVAL 6 DAY
     ORDER BY tanggal, jam_mulai", [$sid]);

// Capaian semester terakhir (rata-rata dari semua ekskul)
$nilai = ambil($pdo,
    "SELECT semester, AVG(kemandirian) AS k, AVG(kerjasama) AS s, AVG(kedisiplinan) AS d, AVG(kreativitas) AS r
     FROM penilaian
     WHERE siswa_id = ? AND semester = (SELECT semester FROM penilaian WHERE siswa_id = ? ORDER BY id DESC LIMIT 1)
     GROUP BY semester", [$sid, $sid])[0] ?? null;
$capaian = $nilai ? [
    ['Kemandirian', round($nilai['k']), '#003d26'],
    ['Kerjasama',   round($nilai['s']), '#22a55a'],
    ['Kedisiplinan', round($nilai['d']), '#8b5cf6'],
    ['Kreativitas', round($nilai['r']), '#f97316'],
] : [];
$rataRata = $capaian ? round(array_sum(array_column($capaian, 1)) / count($capaian)) : null;

// Tabel "Agenda & Tugas Berjalan"
$status = [
    'dalam_proses' => ['Dalam Proses', 'ok'],
    'terjadwal'    => ['Terjadwal', 'info'],
    'selesai'      => ['Selesai', 'ok'],
];
$baris = [];
foreach ($jadwal as $j) {
    $baris[] = [
        'urut'   => $j['tanggal'] . ' ' . $j['jam_mulai'],
        'nama'   => $j['judul'],
        'jenis'  => [ucfirst($j['jenis']), ''],
        'status' => $status[$j['status']],
        'detail' => $j['tempat'] . ', ' . hari_label($j['tanggal']) . ' ' . substr($j['jam_mulai'], 0, 5),
        'aksi'   => ['Lihat Detail', false],
    ];
}
foreach ($tugas as $t) {
    $lewat = strtotime($t['tenggat']) < time();
    $baris[] = [
        'urut'   => $t['tenggat'],
        'nama'   => $t['judul'],
        'jenis'  => ['Tugas', 'warn'],
        'status' => $lewat ? ['Terlambat', 'bad'] : ['Belum Selesai', 'bad'],
        'detail' => 'Deadline: ' . hari_label(substr($t['tenggat'], 0, 10)),
        'aksi'   => ['Unggah Bukti', true],
    ];
}
usort($baris, fn($a, $b) => strcmp($a['urut'], $b['urut']));
$baris = array_slice($baris, 0, 6);

$warna = ['#f97316', '#8b5cf6', '#06b6d4', '#22a55a', '#e11d48', '#0ea5e9'];

$judul = 'Dashboard Utama';
$aktif = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>

<div class="wel">
  <p>Selamat datang kembali 👋</p>
  <h1><?= e($user['nama']) ?></h1>
  <span class="chip">NIS <?= e($user['nis']) ?></span>
  <span class="chip">Tahun Angkatan <?= e((string) $user['angkatan']) ?></span>
</div>

<div class="kpi">
  <div class="card"><b class="n" style="color:#8b5cf6"><?= (int) $baruHariIni ?></b><span>Pengumuman</span><em>Baru hari ini</em></div>
  <div class="card"><b class="n" style="color:#dc2626"><?= (int) $tugasAktif ?></b><span>Tugas Aktif</span><em>Harus diselesaikan</em></div>
  <div class="card"><b class="n"><?= count($jadwal) ?></b><span>Agenda Minggu Ini</span><em>Terjadwal</em></div>
  <div class="card"><b class="n" style="color:#16a34a"><?= $rataRata !== null ? $rataRata . '%' : '-' ?></b><span>Capaian Rata-rata</span><em><?= $nilai ? e('Semester ' . $nilai['semester']) : 'Belum ada penilaian' ?></em></div>
</div>

<div class="dua">
  <div class="card">
    <div class="ch">Pengumuman Terbaru <a href="pengumuman.php">Lihat semua →</a></div>
    <?php if (!$pengumuman): ?>
      <div class="kosong">Belum ada pengumuman untuk ekskul yang kamu ikuti.</div>
    <?php endif; ?>
    <?php foreach ($pengumuman as $p): ?>
      <?php $asal = $p['ekskul_nama'] ? 'Pembina ' . $p['ekskul_nama'] : 'Pengumuman umum'; ?>
      <div class="ann" style="--c:<?= $warna[(int) $p['ekskul_id'] % count($warna)] ?>">
        <div class="pf"><?= e(inisial($asal === 'Pengumuman umum' ? 'Pengumuman Umum' : $p['ekskul_nama'])) ?></div>
        <div>
          <small><?= e($asal) ?></small>
          <b><?= e($p['judul']) ?></b>
          <small><?= e($p['isi']) ?></small>
          <small>Dibuat <?= e(waktu_lalu((int) $p['menit'])) ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="ch">Capaian Semester <a href="#">Detail →</a></div>
    <div class="bars">
      <?php if (!$capaian): ?><div class="kosong">Belum ada nilai semester ini.</div><?php endif; ?>
      <?php foreach ($capaian as [$label, $persen, $warnaBar]): ?>
        <div class="bar" style="--c:<?= $warnaBar ?>">
          <div><?= e($label) ?><span><?= (int) $persen ?>%</span></div>
          <i><u style="width:<?= (int) $persen ?>%"></u></i>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card tw">
  <div class="ch">Agenda &amp; Tugas Berjalan <a href="jadwal.php">Lihat semua →</a></div>
  <table>
    <thead><tr><th>NAMA KEGIATAN</th><th>JENIS</th><th>STATUS</th><th>DETAIL</th><th>AKSI</th></tr></thead>
    <tbody>
    <?php if (!$baris): ?>
      <tr><td colspan="5" class="kosong">Tidak ada agenda atau tugas dalam waktu dekat.</td></tr>
    <?php endif; ?>
    <?php foreach ($baris as $b): ?>
      <tr>
        <td><b><?= e($b['nama']) ?></b></td>
        <td><span class="tag <?= $b['jenis'][1] ?>"><?= e($b['jenis'][0]) ?></span></td>
        <td><span class="tag <?= $b['status'][1] ?>"><?= e($b['status'][0]) ?></span></td>
        <td><?= e($b['detail']) ?></td>
        <td><a class="b2 <?= $b['aksi'][1] ? 'd' : '' ?>" href="#"><?= e($b['aksi'][0]) ?></a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
