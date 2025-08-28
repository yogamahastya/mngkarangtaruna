<?php

// Pastikan skrip hanya bisa diakses oleh pengguna yang berwenang
// Ganti 'YOUR_SECRET_TOKEN' dengan token rahasia yang kuat dan unik
if (!isset($_POST['token']) || $_POST['token'] !== 'nxr232597') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Token tidak valid.']);
    exit;
}

// Gunakan __DIR__ untuk mendeteksi lokasi proyek
$repoPath = realpath(__DIR__ . '/..');

// Cek apakah direktori proyek valid
if (!is_dir($repoPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Direktori proyek tidak ditemukan.']);
    exit;
}

// Pindah ke direktori proyek
chdir($repoPath);

// Jalankan git pull dan tangkap output
$command = 'git pull 2>&1';
$output = shell_exec($command);

// Catat log
$logFile = 'git_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . "\n" . $output . "\n\n", FILE_APPEND);

// Periksa apakah pembaruan berhasil
if (strpos($output, 'Already up to date.') !== false || strpos($output, 'Updating') !== false) {
    // Jalankan perintah tambahan jika diperlukan, misalnya `composer install`
    // shell_exec('composer install');
    echo json_encode(['status' => 'success', 'message' => 'Pembaruan berhasil!']);
} else {
    http_response_code(500);
    // Berikan pesan error yang lebih informatif
    echo json_encode(['status' => 'error', 'message' => 'Pembaruan gagal. Silakan periksa log.', 'output' => $output]);
}

?>