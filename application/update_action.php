<?php
header('Content-Type: application/json');

// === KONFIGURASI ===
$secretToken = "nxr232597"; // ganti kalau perlu
$logFile     = __DIR__ . "/git_log.txt";
$repoPath    = realpath(__DIR__ . "/.."); // root project
$localVersionFile  = $repoPath . "/application/version.json";
$remoteUrl   = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";

// === CEK TOKEN ===
$token = $_POST['token'] ?? $_GET['token'] ?? null;
if ($token !== $secretToken) {
    http_response_code(403);
    echo json_encode([
        "status"  => "error",
        "message" => "Akses ditolak. Token tidak valid."
    ]);
    exit;
}

// === CEK DIREKTORI ===
if (!$repoPath || !is_dir($repoPath)) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Direktori proyek tidak ditemukan."
    ]);
    exit;
}

// === CEK VERSION LOCAL ===
$localVersion = null;
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

// === CEK VERSION REMOTE ===
$remoteData = @file_get_contents($remoteUrl);
if ($remoteData === false) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Gagal mengambil version.json dari GitHub."
    ]);
    exit;
}
$remoteData = json_decode($remoteData, true);
$remoteVersion = $remoteData['version'] ?? null;

// === BANDINGKAN ===
if ($localVersion === $remoteVersion) {
    echo json_encode([
        "status"  => "info",
        "message" => "Sudah versi terbaru.",
        "version" => $localVersion
    ]);
    exit;
}

// === PINDAH KE ROOT PROJECT ===
chdir($repoPath);

// === JALANKAN GIT PULL ===
$command = "sudo -u root git pull 2>&1";
$output  = shell_exec($command);

// === SIMPAN LOG ===
file_put_contents(
    $logFile,
    "[" . date("Y-m-d H:i:s") . "]\n" . $output . "\n\n",
    FILE_APPEND
);

// === RESPON ===
if (strpos($output, "Already up to date.") !== false || strpos($output, "Updating") !== false) {
    echo json_encode([
        "status"  => "success",
        "message" => "Pembaruan berhasil.",
        "from"    => $localVersion,
        "to"      => $remoteVersion,
        "output"  => $output
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Pembaruan gagal. Silakan periksa log.",
        "output"  => $output
    ]);
}

?>