<?php
header('Content-Type: application/json');

// === KONFIGURASI ===
$remoteUrl = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";
$localVersionFile = realpath("version.json");
$localVersion = null;

// === CEK VERSION LOCAL ===
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

// === CEK VERSION REMOTE ===
$remoteVersion = null;
$remoteData = @file_get_contents($remoteUrl);
if ($remoteData !== false) {
    $remoteData = json_decode($remoteData, true);
    $remoteVersion = $remoteData['version'] ?? null;
}

// === RESPON JSON ===
$isUpdateAvailable = ($localVersion && $remoteVersion && $localVersion !== $remoteVersion);

echo json_encode([
    "status" => "success",
    "local_version" => $localVersion,
    "remote_version" => $remoteVersion,
    "update_available" => $isUpdateAvailable
]);
?>