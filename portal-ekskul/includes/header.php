<?php
// Kerangka halaman setelah login.
// Sebelum include, halaman harus mengisi: $judul, $aktif, $user (baris dari tabel users)
$peran = $_SESSION['peran'];
$base  = BASE_URL . '/' . $peran . '/';

// Menu: [label, nama file, sudah dibuat?]
$menus = [
    'siswa' => [
        'dashboard'  => ['Dashboard',      'dashboard.php',  true],
        'ekskul'     => ['Daftar Ekskul',  'ekskul.php',     true],
        'jadwal'     => ['Jadwal',         'jadwal.php',     true],
        'tugas'      => ['Tugas & Agenda', 'tugas.php',      false],
        'absensi'    => ['Absensi',        'absensi.php',    false],
        'pengumuman' => ['Pengumuman',     'pengumuman.php', true],
        'penilaian'  => ['Penilaian',      'penilaian.php',  false],
    ],
    'pembimbing' => [
        'dashboard'  => ['Dashboard',      'dashboard.php',  true],
        'ekskul'     => ['Daftar Ekskul',  'ekskul.php',     false],
        'anggota'    => ['Kelola Anggota', 'anggota.php',    false],
        'jadwal'     => ['Jadwal',         'jadwal.php',     false],
        'tugas'      => ['Tugas',          'tugas.php',      false],
        'absensi'    => ['Absensi',        'absensi.php',    false],
        'pengumuman' => ['Pengumuman',     'pengumuman.php', false],
    ],
];
$keterangan = $peran === 'siswa' ? 'Kelas ' . ($user['kelas'] ?? '-') : 'Pembimbing';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul) ?> | Trisula Praja</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<div class="app">
  <aside>
    <div class="brand"><span>🔱</span><div>SMA TRISULA PRAJA<br>EKSTRAKURIKULER</div></div>
    <small>MENU UTAMA</small>
    <?php foreach ($menus[$peran] as $kunci => [$label, $file, $ada]): ?>
      <?php if ($ada): ?>
        <a class="<?= $kunci === $aktif ? 'on' : '' ?>" href="<?= e($base . $file) ?>"><?= e($label) ?></a>
      <?php else: ?>
        <span class="mati" title="Segera hadir"><?= e($label) ?></span>
      <?php endif; ?>
    <?php endforeach; ?>
    <a class="keluar" href="<?= BASE_URL ?>/logout.php">Keluar</a>
  </aside>

  <div class="isi">
    <header class="atas">
      <h5><?= e($judul) ?></h5>
      <input type="search" placeholder="Cari pengumuman, tugas…" aria-label="Cari">
      <div class="me">
        <div class="pf"><?= e(inisial($user['nama'])) ?></div>
        <div><b><?= e($user['nama']) ?></b><br><small><?= e($keterangan) ?></small></div>
      </div>
    </header>
    <main>
