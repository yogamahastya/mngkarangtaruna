<?php
header('Content-Type: application/json');

// === KONFIGURASI ===
// Token rahasia untuk otentikasi.
$secretToken = "nxr232597"; 
// Path untuk file log.
$logFile     = __DIR__ . "/git_log.txt";
// Mendeteksi path root project secara otomatis.
$repoPath    = realpath(__DIR__ . "/.."); 
// File versi lokal.
$localVersionFile = $repoPath . "/application/version.json";
// URL file versi di GitHub.
$remoteUrl   = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";

// === FUNGSI BANTUAN UNTUK RESPON ===
// Mengirimkan respons JSON yang terstruktur.
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

// === VERIFIKASI TOKEN ===
$token = $_POST['token'] ?? $_GET['token'] ?? null;
if ($token !== $secretToken) {
    http_response_code(403);
    sendResponse("error", "Akses ditolak. Token tidak valid.");
}

// === VALIDASI DIREKTORI PROYEK ===
// Memastikan path repository valid.
if (!$repoPath || !is_dir($repoPath)) {
    http_response_code(500);
    sendResponse("error", "Direktori proyek tidak ditemukan.");
}

// === CEK VERSI LOKAL VS. REMOTE ===
// Mengambil versi dari file lokal.
$localVersion = null;
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

// Mengambil versi dari GitHub.
$remoteData = @file_get_contents($remoteUrl);
if ($remoteData === false) {
    http_response_code(500);
    sendResponse("error", "Gagal mengambil version.json dari GitHub.");
}
$remoteData = json_decode($remoteData, true);
$remoteVersion = $remoteData['version'] ?? null;

// Jika versi sudah sama, tidak perlu pembaruan.
if ($localVersion === $remoteVersion) {
    sendResponse("info", "Sudah versi terbaru.", null, $localVersion);
}

// === EKSEKUSI PEMBARUAN GIT ===

// 1. Ubah izin direktori Git untuk memastikan akses.
// Perintah ini hanya akan dieksekusi jika user PHP memiliki izin sudo.
// Jika tidak, Anda harus jalankan secara manual melalui SSH.
$chownCommand = "sudo chown -R www:www " . escapeshellarg($repoPath);
shell_exec($chownCommand);

// 2. Pindah ke direktori repository.
chdir($repoPath);

// 3. Lakukan git stash untuk menyimpan perubahan lokal.
$output_stash = shell_exec("git stash 2>&1");

// 4. Lakukan git pull.
$output_pull = shell_exec("git pull 2>&1");

// 5. Terapkan kembali perubahan yang di-stash jika ada.
$output_pop = '';
if (strpos($output_stash, 'No local changes to save') === false) {
    $output_pop = shell_exec("git stash pop 2>&1");
}

// Gabungkan semua output untuk log dan respons.
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
// Memeriksa output git pull untuk menentukan keberhasilan.
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