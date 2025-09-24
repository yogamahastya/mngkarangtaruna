<?php
// Fungsi pembantu untuk mengirim respons JSON
function sendResponse($status, $message, $log = null, $localVersion = null, $remoteVersion = null) {
    $response = [
        "status" => $status,
        "message" => $message,
        "log" => $log,
        "current_version" => $localVersion,
        "latest_version" => $remoteVersion
    ];
    echo json_encode($response);
    exit;
}

// === LOKASI FILE & REPOSITORY ===
// Lokasi file untuk menyimpan status auto update
$settingsFile = __DIR__ . '/auto_update_status.json';
// Lokasi log
$logFile = __DIR__ . '/git_log.txt';
// Lokasi repository Git
// Path ini harus satu level di atas folder 'application'
$repoPath = realpath(__DIR__ . '/..'); 
// URL file versi di GitHub
$remoteUrl = 'https://raw.githubusercontent.com/your-username/your-repo/main/version.json';
// Lokasi file versi lokal
$localVersionFile = $repoPath . '/version.json';


// === CEK STATUS AUTO UPDATE ===
$isAutoUpdateEnabled = false;
if (file_exists($settingsFile)) {
    $settingsData = json_decode(file_get_contents($settingsFile), true);
    if ($settingsData !== null && isset($settingsData['auto_update'])) {
        $isAutoUpdateEnabled = $settingsData['auto_update'];
    }
}

// Hentikan proses jika auto update tidak diaktifkan
if (!$isAutoUpdateEnabled) {
    sendResponse("info", "Auto update dinonaktifkan. Tidak ada pembaruan yang dijalankan.");
    exit;
}

// === VALIDASI DIREKTORI PROYEK ===
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
$full_output = '';
try {
    // 1. Pindah ke direktori repository.
    chdir($repoPath);

    // 2. Lakukan git stash untuk menyimpan perubahan lokal.
    $output_stash = shell_exec("git stash 2>&1");
    $full_output .= "Git Stash:\n" . ($output_stash ?? 'null') . "\n\n";

    // 3. Lakukan git pull.
    $output_pull = shell_exec("git pull 2>&1");
    $full_output .= "Git Pull:\n" . ($output_pull ?? 'null') . "\n\n";

    // 4. Terapkan kembali perubahan yang di-stash jika ada.
    if (strpos($output_stash, 'No local changes to save') === false) {
        $output_pop = shell_exec("git stash pop 2>&1");
        $full_output .= "Git Stash Pop:\n" . ($output_pop ?? 'null');
    }

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
} catch (Exception $e) {
    http_response_code(500);
    sendResponse(
        "error",
        "Terjadi kesalahan saat menjalankan perintah Git: " . $e->getMessage(),
        $full_output
    );
}
?>