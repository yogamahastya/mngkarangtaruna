<?php
header('Content-Type: application/json');

// === KONFIGURASI ===
$secretToken = "nxr232597"; // ganti kalau perlu
$logFile     = __DIR__ . "/git_log.txt";
$repoPath    = realpath(__DIR__ . "/.."); // root project

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

// === PINDAH KE ROOT PROJECT ===
chdir($repoPath);

// === JALANKAN GIT PULL ===
$command = "git pull 2>&1";
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