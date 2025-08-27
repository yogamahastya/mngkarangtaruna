<?php
// File: config.php

// Konfigurasi Nama Organisasi
if (!defined('ORGANIZATION_NAME')) {
    define('ORGANIZATION_NAME', 'Karang Taruna Apa Aja Lah');
}

// Ganti dengan jumlah iuran!!!
if (!defined('DUES_MONTHLY_FEE')) {
    define('DUES_MONTHLY_FEE', 10000); 
}

// Konfigurasi Absensi
if (!defined('COOLDOWN_SECONDS')) {
    define('COOLDOWN_SECONDS', 43200); // Cooldown untuk absen (12 jam)
}

// **START MODIFIKASI: Tambahkan definisi jabatan**
if (!defined('JABATAN_OPTIONS')) {
    define('JABATAN_OPTIONS', [
        'Anggota',
        'Ketua',
        'Wakil Ketua',
        'Bendahara',
        'Sekretaris',
        'Humas'
    ]);
}
// **END MODIFIKASI**
?>