<?php
header('Content-Type: application/json');

// Token keamanan
$token = $_POST['token'] ?? $_GET['token'] ?? null;
if ($token !== 'nxr232597') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak. Token tidak valid.']);
    exit;
}

// ======================
// KONFIGURASI
// ======================
$repoPath     = realpath(__DIR__ . '/..'); // root project
$localVersion = __DIR__ . '/version.json'; // versi lokal
$remoteUrl    = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";

// ======================
// AMBIL VERSI LOKAL
// ======================
if (!file_exists($localVersion)) {
    file_put_contents($localVersion, json_encode(['version' => '0.0.0']));
}
$localData    = json_decode(file_get_contents($localVersion), true);
$currentLocal = $localData['version'] ?? "0.0.0";

// ======================
// AMBIL VERSI REMOTE (GitHub)
// ======================
$remoteData   = @file_get_contents($remoteUrl);
if ($remoteData === false) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal ambil versi remote dari GitHub.']);
    exit;
}
$remoteJson   = json_decode($remoteData, true);
$currentRemote = $remoteJson['version'] ?? "0.0.0";

// ======================
// CEK VERSI
// ======================
if ($currentLocal === $currentRemote) {
    echo json_encode(['status' => 'success', 'message' => 'Sudah versi terbaru ('.$currentLocal.')']);
    exit;
}

// ======================
// JALANKAN UPDATE
// ======================
chdir($repoPath);
$command = 'git reset --hard HEAD && git pull 2>&1';
$output  = shell_exec($command);

// Catat log
$logFile = __DIR__ . '/git_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . "\n" . $output . "\n\n", FILE_APPEND);

// Kalau sukses, update versi lokal
if (strpos($output, 'Updating') !== false || strpos($output, 'Fast-forward') !== false) {
    file_put_contents($localVersion, json_encode(['version' => $currentRemote]));
    echo json_encode(['status' => 'success', 'message' => 'Update berhasil ke versi '.$currentRemote, 'output' => $output]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Update gagal', 'output' => $output]);
}

?>