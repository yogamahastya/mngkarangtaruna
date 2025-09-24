<?php
include_once __DIR__ . '/../dotenv_loader.php';

// Pastikan file .env dimuat.
loadEnv(__DIR__ . '/../.env');

$UPDATE_TOKEN = getenv('UPDATE_TOKEN');

// Pastikan hanya bisa diakses via POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

// Ambil header Authorization.
$headers = getallheaders();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== "Bearer " . $UPDATE_TOKEN) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['auto_update']) || !is_bool($data['auto_update'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data received.']);
    exit;
}

$autoUpdateStatus = $data['auto_update'];

// Gunakan metode penulisan file yang lebih aman (atomic).
$settingsFile = __DIR__ . '/auto_update_status.json';
$tempFile = tempnam(sys_get_temp_dir(), 'update_status_');

try {
    // Tulis ke file sementara
    if (!file_put_contents($tempFile, json_encode(['auto_update' => $autoUpdateStatus]))) {
        throw new Exception("Failed to write to temporary file.");
    }

    // Pindahkan file sementara ke lokasi akhir. Ini adalah operasi atomic.
    if (!rename($tempFile, $settingsFile)) {
        throw new Exception("Failed to rename temporary file.");
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Auto update status updated.']);
} catch (Exception $e) {
    // Pastikan file sementara dihapus jika ada kesalahan.
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save settings: ' . $e->getMessage()]);
}
?>