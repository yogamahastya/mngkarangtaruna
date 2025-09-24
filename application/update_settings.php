<?php
// Pastikan skrip hanya bisa diakses via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['auto_update']) || !is_bool($data['auto_update'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data received.']);
    exit;
}

$autoUpdateStatus = $data['auto_update'];

// Lokasi file untuk menyimpan status auto update
// Path-nya adalah di direktori yang sama dengan file ini
$settingsFile = __DIR__ . '/auto_update_status.json';

try {
    if (file_put_contents($settingsFile, json_encode(['auto_update' => $autoUpdateStatus]))) {
        echo json_encode(['status' => 'success', 'message' => 'Auto update status updated.']);
    } else {
        throw new Exception("Failed to write to file.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save settings file: ' . $e->getMessage()]);
}
?>