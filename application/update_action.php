<?php
// Pastikan skrip ini hanya dapat diakses melalui metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Metode tidak diizinkan.');
}

// Gunakan __DIR__ untuk mendeteksi lokasi proyek secara otomatis
$repoPath = __DIR__ . '/..';

// Pindah ke direktori proyek
chdir($repoPath);

// Jalankan perintah git pull dan tangkap outputnya
$output = shell_exec('git pull 2>&1');

// Tanggapan balik (response) ke browser dalam format JSON
if (strpos($output, 'Already up to date.') !== false || strpos($output, 'Updating') !== false) {
    echo json_encode(['status' => 'success', 'message' => 'Pembaruan berhasil!', 'output' => $output]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Pembaruan gagal.', 'output' => $output]);
}
?>