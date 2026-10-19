<?php
// Fungsi bantu yang dipakai banyak halaman
date_default_timezone_set('Asia/Jakarta');

// Menjalankan query dan mengembalikan semua baris
function ambil(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// "Budi Setiawan" -> "BS"
function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim($nama));
    $hasil = mb_substr($kata[0], 0, 1);
    if (count($kata) > 1) {
        $hasil .= mb_substr($kata[1], 0, 1);
    }
    return mb_strtoupper($hasil);
}

// 125 -> "2 jam yang lalu"
function waktu_lalu(int $menit): string
{
    if ($menit < 1)    return 'baru saja';
    if ($menit < 60)   return $menit . ' menit yang lalu';
    if ($menit < 1440) return floor($menit / 60) . ' jam yang lalu';
    return floor($menit / 1440) . ' hari yang lalu';
}

// "2026-10-20" -> "Hari ini" / "Besok" / "20 Okt"
function hari_label(string $tanggal): string
{
    $hari  = (new DateTime('today'))->diff(new DateTime($tanggal))->format('%r%a');
    if ($hari == 0)  return 'Hari ini';
    if ($hari == 1)  return 'Besok';
    if ($hari == -1) return 'Kemarin';
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = new DateTime($tanggal);
    return $t->format('j') . ' ' . $bulan[(int) $t->format('n')];
}

// "2026-10-20" -> "Selasa, 20 Oktober 2026"
function tanggal_panjang(string $tanggal): string
{
    $hari  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $t = new DateTime($tanggal);
    return $hari[(int) $t->format('w')] . ', ' . $t->format('j') . ' ' . $bulan[(int) $t->format('n')] . ' ' . $t->format('Y');
}
