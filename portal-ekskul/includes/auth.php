<?php
// Fungsi login dan penjagaan halaman
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const BASE_URL = '/portal-ekskul';

function sudah_login(): bool
{
    return isset($_SESSION['user_id']);
}

function dashboard_url(string $peran): string
{
    return BASE_URL . '/' . $peran . '/dashboard.php';
}

// Panggil di awal halaman yang harus login.
// Contoh: wajib_login('siswa');
function wajib_login(?string $peran = null): void
{
    if (!sudah_login()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    if ($peran !== null && $_SESSION['peran'] !== $peran) {
        header('Location: ' . dashboard_url($_SESSION['peran']));
        exit;
    }
}

function e(?string $teks): string
{
    return htmlspecialchars($teks ?? '', ENT_QUOTES, 'UTF-8');
}
