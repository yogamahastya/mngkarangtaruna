<?php
// File: cron_trigger.php

$settingsFile = __DIR__ . '/auto_update_status.json';

// Cek apakah file status ada dan bisa dibaca.
if (file_exists($settingsFile) && is_readable($settingsFile)) {
    $statusContent = file_get_contents($settingsFile);
    $statusData = json_decode($statusContent, true);

    // Periksa status 'auto_update'.
    if (isset($statusData['auto_update']) && $statusData['auto_update'] === true) {
        // Jalankan skrip updater.
        // Lebih baik gunakan jalur absolut untuk php dan skrip updater.
        $phpPath = '/usr/bin/php'; // Pastikan jalur ini benar di server Anda.
        $updaterPath = __DIR__ . '/updater.php';
        
        // Perintah untuk menjalankan updater di latar belakang.
        exec("$phpPath $updaterPath > /dev/null 2>&1");
    }
}
?>