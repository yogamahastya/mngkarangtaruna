<?php
header('Content-Type: application/json');

// === KONFIGURASI ===
$secretToken = "nxr232597"; // Ganti dengan token rahasia Anda
$logFile     = __DIR__ . "/git_log.txt";
$repoPath    = realpath(__DIR__ . "/.."); // Path ke root project
$localVersionFile = $repoPath . "/application/version.json";
$remoteUrl   = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";

// === FUNGSI BANTUAN UNTUK RESPON ===
function sendResponse($status, $message, $output = null, $from = null, $to = null) {
    echo json_encode([
        "status"  => $status,
        "message" => $message,
        "from"    => $from,
        "to"      => $to,
        "output"  => $output
    ]);
    exit;
}

// === CEK TOKEN ===
$token = $_POST['token'] ?? $_GET['token'] ?? null;
if ($token !== $secretToken) {
    http_response_code(403);
    sendResponse("error", "Akses ditolak. Token tidak valid.");
}

// === VALIDASI DIREKTORI ===
if (!$repoPath || !is_dir($repoPath)) {
    http_response_code(500);
    sendResponse("error", "Direktori proyek tidak ditemukan.");
}

// === CEK VERSI LOKAL VS. REMOTE ===
$localVersion = null;
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

$remoteData = @file_get_contents($remoteUrl);
if ($remoteData === false) {
    http_response_code(500);
    sendResponse("error", "Gagal mengambil version.json dari GitHub.");
}
$remoteData = json_decode($remoteData, true);
$remoteVersion = $remoteData['version'] ?? null;

if ($localVersion === $remoteVersion) {
    sendResponse("info", "Sudah versi terbaru.", null, $localVersion);
}

// === EKSEKUSI PEMBARUAN GIT ===
chdir($repoPath);

// 1. Lakukan git stash untuk menyimpan perubahan lokal
$output_stash = shell_exec("git stash 2>&1");

// 2. Lakukan git pull
$output_pull = shell_exec("git pull 2>&1");

// 3. Terapkan kembali perubahan yang di-stash jika ada
$output_pop = '';
if (strpos($output_stash, 'No local changes to save') === false) {
    $output_pop = shell_exec("git stash pop 2>&1");
}

// Gabungkan semua output
$full_output = "Git Stash:\n" . ($output_stash ?? 'null') . "\n\n"
            . "Git Pull:\n" . ($output_pull ?? 'null') . "\n\n"
            . "Git Stash Pop:\n" . ($output_pop ?? 'null');

// === SIMPAN LOG KE FILE ===
file_put_contents(
    $logFile,
    "[" . date("Y-m-d H:i:s") . "]\n" . $full_output . "\n\n",
    FILE_APPEND
);

// === RESPON AKHIR BERDASARKAN HASIL PULL ===
if (strpos($output_pull, "Already up to date.") !== false || strpos($output_pull, "Updating") !== false) {
    sendResponse(
        "success",
        "Pembaruan berhasil.",
        $full_output,
        $localVersion,
        $remoteVersion
    );
} else {
    http_response_code(500);
    sendResponse(
        "error",
        "Pembaruan gagal. Silakan periksa log.",
        $full_output
    );
}
?>