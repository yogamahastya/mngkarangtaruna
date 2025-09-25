<?php
// === CEK MODE EKSEKUSI: hanya boleh CLI (cron job) ===
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo json_encode([
        "status" => "forbidden",
        "message" => "AKSES DENIED."
    ], JSON_PRETTY_PRINT);
    exit;
}

// === Fungsi pembantu untuk mengirim respons JSON ===
function sendResponse($status, $message, $log = null, $localVersion = null, $remoteVersion = null) {
    $response = [
        "status" => $status,
        "message" => $message,
        "log" => $log,
        "current_version" => $localVersion,
        "latest_version" => $remoteVersion
    ];
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// === LOKASI FILE & REPOSITORY ===
$settingsFile = __DIR__ . '/auto_update_status.json';
$logFile = __DIR__ . '/git_log.txt';
$repoPath = realpath(__DIR__ . '/..'); 
$remoteUrl = 'https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json';
$localVersionFile = $repoPath . '/version.json';

// === CEK STATUS AUTO UPDATE ===
$isAutoUpdateEnabled = false;
if (file_exists($settingsFile)) {
    $settingsData = json_decode(file_get_contents($settingsFile), true);
    if ($settingsData !== null && isset($settingsData['auto_update'])) {
        $isAutoUpdateEnabled = $settingsData['auto_update'];
    }
}
if (!$isAutoUpdateEnabled) {
    sendResponse("info", "Auto update dinonaktifkan.");
}

// === VALIDASI DIREKTORI REPO ===
if (!$repoPath || !is_dir($repoPath)) {
    sendResponse("error", "Direktori proyek tidak ditemukan: $repoPath");
}
if (!is_dir($repoPath . '/.git')) {
    sendResponse("error", "Folder ini bukan repository git: $repoPath");
}

// === CEK VERSI LOKAL VS. REMOTE ===
$localVersion = null;
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

$remoteData = @file_get_contents($remoteUrl);
if ($remoteData === false) {
    sendResponse("error", "Gagal mengambil version.json dari GitHub: $remoteUrl");
}
$remoteData = json_decode($remoteData, true);
$remoteVersion = $remoteData['version'] ?? null;

if ($localVersion === $remoteVersion) {
    sendResponse("info", "Sudah versi terbaru.", null, $localVersion, $remoteVersion);
}

// === EKSEKUSI GIT UPDATE ===
$full_output = '';
try {
    chdir($repoPath);

    // Info tambahan untuk debug
    $whoami = shell_exec("whoami 2>&1");
    $pwd = shell_exec("pwd 2>&1");
    $branch = shell_exec("git rev-parse --abbrev-ref HEAD 2>&1");
    $remote = shell_exec("git remote -v 2>&1");

    $full_output .= "=== DEBUG INFO ===\n";
    $full_output .= "User: " . trim($whoami) . "\n";
    $full_output .= "Current dir: " . trim($pwd) . "\n";
    $full_output .= "Active branch: " . trim($branch) . "\n";
    $full_output .= "Remote:\n" . $remote . "\n";
    $full_output .= "Repo Path: $repoPath\n";
    $full_output .= "Local Version: $localVersion\n";
    $full_output .= "Remote Version: $remoteVersion\n";
    $full_output .= "==================\n\n";

    // Git stash
    $output_stash = shell_exec("git stash 2>&1");
    $full_output .= "Git Stash:\n" . ($output_stash ?? 'null') . "\n\n";

    // Git pull
    $output_pull = shell_exec("git pull 2>&1");
    $full_output .= "Git Pull:\n" . ($output_pull ?? 'null') . "\n\n";

    // Git stash pop (kalau ada perubahan yang di-stash)
    if (strpos($output_stash, 'No local changes to save') === false) {
        $output_pop = shell_exec("git stash pop 2>&1");
        $full_output .= "Git Stash Pop:\n" . ($output_pop ?? 'null') . "\n\n";
    }

    // Simpan log
    file_put_contents(
        $logFile,
        "[" . date("Y-m-d H:i:s") . "]\n" . $full_output . "\n\n",
        FILE_APPEND
    );

    // Cek hasil pull
    if (
        strpos($output_pull, "Already up to date.") !== false ||
        strpos($output_pull, "Updating") !== false ||
        strpos($output_pull, "Fast-forward") !== false ||
        strpos($output_pull, "Merge made by") !== false
    ) {
        sendResponse("success", "Pembaruan berhasil.", $full_output, $localVersion, $remoteVersion);
    } else {
        sendResponse("error", "Pembaruan gagal. Periksa log.", $full_output, $localVersion, $remoteVersion);
    }

} catch (Exception $e) {
    sendResponse("error", "Error saat menjalankan Git: " . $e->getMessage(), $full_output);
}
