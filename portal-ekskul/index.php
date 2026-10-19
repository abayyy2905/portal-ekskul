<?php
// Halaman Portal (landing page)
require __DIR__ . '/config/koneksi.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/helpers.php';

$tujuan = sudah_login() ? dashboard_url($_SESSION['peran']) : BASE_URL . '/login.php';
$labelMasuk = sudah_login() ? 'Ke Dashboard' : 'Masuk';

$ekskul = ambil($pdo, 'SELECT id, nama, deskripsi FROM ekskul ORDER BY id LIMIT 6');
$warna = [['#2b7bb9', '#0b3d5c'], ['#34495e', '#0c1c2a'], ['#b3262c', '#5c0f14'],
          ['#3f8f6b', '#103a2a'], ['#c47a1c', '#5a2f08'], ['#6a4fb0', '#2a1f55']];

// Foto gedung: taruh file assets/img/gedung.jpg untuk menggantikan latar abu-abu
$adaFoto = file_exists(__DIR__ . '/assets/img/gedung.jpg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Ekstrakurikuler Trisula Praja</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Chango&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/portal.css">
</head>
<body>

<nav class="nav">
  <div class="crest">🔱</div>
  <a href="#home">Home</a>
  <a href="#sejarah">Sejarah Singkat</a>
  <a href="#pendiri">Pendiri</a>
  <a href="#visi">Visi dan Misi</a>
  <a href="#ekskul">Ekskul Terbaik</a>
  <a href="<?= e($tujuan) ?>"><?= e($labelMasuk) ?></a>
</nav>

<section class="hero" id="home">
  <small>Selamat datang di</small>
  <h1>PORTAL<br>EKSTRAKURIKULER</h1>
  <h2>TRISULA PRAJA</h2>
  <div class="row">
    <a class="btn-p y" href="<?= e($tujuan) ?>"><?= e($labelMasuk) ?></a>
    <a class="btn-p o" href="#sejarah">Pelajari Selengkapnya</a>
  </div>
</section>

<div class="foto" <?= $adaFoto ? 'style="background-image:url(\'' . BASE_URL . '/assets/img/gedung.jpg\')"' : '' ?>>
  <?php if (!$adaFoto): ?>Foto gedung SMA Trisula Praja<?php endif; ?>
</div>

<section class="sec" id="sejarah">
  <h3>Sejarah Singkat</h3>
  <p>SMA Trisula Praja adalah sekolah menengah atas yang dirancang sebagai tempat tumbuhnya prestasi akademik, karakter, dan kepemimpinan siswa. Sejak berdiri, sekolah terus mengembangkan kegiatan ekstrakurikuler agar setiap siswa dapat menyalurkan minat dan bakatnya.</p>
</section>

<section class="sec" id="pendiri">
  <h3>Para Pendiri Sekolah</h3>
  <div class="pendiri">
    <div class="fo"><div class="av">AP</div><div><b>Dr. H. Arif Prasetyo, M.Pd.</b>Pendiri sekaligus pembina nilai dan visi sekolah.</div></div>
    <div class="fo"><div class="av">SR</div><div><b>Dra. Siti Rahmawati, M.Pd.</b>Bertanggung jawab atas pengembangan kurikulum dan kompetensi guru.</div></div>
    <div class="fo"><div class="av">GS</div><div><b>H. Gilang Saputra, M.Kom.</b>Memimpin pengembangan teknologi dan fasilitas digital sekolah.</div></div>
  </div>
</section>

<section class="sec" id="visi">
  <h3>Visi dan Misi Sekolah</h3>
  <p class="tengah">Menjadi sekolah yang unggul dalam membentuk generasi berkarakter, berprestasi, dan berwawasan global.</p>
  <ol>
    <li>Menyelenggarakan pendidikan berkualitas dengan pendekatan kreatif dan inovatif.</li>
    <li>Membentuk siswa yang beriman, berintegritas, dan bertanggung jawab.</li>
    <li>Mengembangkan bakat siswa melalui kegiatan ekstrakurikuler.</li>
    <li>Membangun kerja sama dengan masyarakat dan lembaga pendidikan lain.</li>
  </ol>
</section>

<section class="sec">
  <div class="stats">
    <div><b>Siswa Baru 2025/2026</b><span>320 Siswa</span></div>
    <div><b>Siswa Aktif</b><span>1.248 Siswa</span></div>
    <div><b>Siswa International</b><span>24 Siswa</span></div>
  </div>
</section>

<section class="sec" id="ekskul">
  <h3>Rekomendasi Ekskul Terbaik</h3>
  <?php if (!$ekskul): ?><p class="tengah">Belum ada ekskul yang terdaftar.</p><?php endif; ?>
  <div class="eks">
    <?php foreach ($ekskul as $k): ?>
      <?php $c = $warna[((int) $k['id'] - 1) % count($warna)]; ?>
      <div>
        <i style="--c1:<?= $c[0] ?>;--c2:<?= $c[1] ?>"></i>
        <b><?= e($k['nama']) ?></b>
        <p><?= e($k['deskripsi']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="sec peran">
  <h3>Peran SMA Trisula Praja</h3>
  <ul>
    <li>Menyediakan wadah pengembangan minat dan bakat siswa.</li>
    <li>Mengelola jadwal, tugas, dan absensi kegiatan secara digital.</li>
    <li>Menilai perkembangan karakter siswa secara berkala.</li>
  </ul>
  <a class="btn-p y" href="<?= e($tujuan) ?>"><?= e($labelMasuk) ?></a>
</section>

<p class="motto">“Cerdas dalam Ilmu, Tangguh dalam Karakter, Unggul dalam Prestasi.”</p>

<footer class="foot">
  <div>
    <span>SMA Trisula Praja<br>Pekanbaru, Riau, Indonesia</span>
    <span>Layanan Informasi<br>Pendaftaran siswa baru<br>Jadwal dan pengumuman</span>
    <span>Hubungi Kami<br>Telepon sekolah<br>Email sekolah</span>
  </div>
</footer>

</body>
</html>
