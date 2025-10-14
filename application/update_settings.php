<?php
// ================================================
// update_settings.php - Menyimpan status auto update
// ================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

if (!isset($data['auto_update'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing auto_update parameter']);
    exit;
}

$autoUpdateStatus = filter_var($data['auto_update'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($autoUpdateStatus === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid boolean value for auto_update']);
    exit;
}

// Gunakan path absolut ke folder 'application'
$settingsFile = __DIR__ . '/auto_update_status.json';
$logFile      = __DIR__ . '/auto_update_log.txt';

try {
    if (!is_writable(dirname($settingsFile))) {
        throw new Exception("Directory is not writable");
    }

    $jsonContent = json_encode([
        'auto_update' => $autoUpdateStatus,
        'updated_at'  => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

    if ($jsonContent === false) {
        throw new Exception("Failed to encode JSON: " . json_last_error_msg());
    }

    $bytesWritten = file_put_contents($settingsFile, $jsonContent, LOCK_EX);
    if ($bytesWritten === false) {
        throw new Exception("Failed to write to file");
    }

    file_put_contents($logFile,
        date('Y-m-d H:i:s') . " - auto_update set ke: " . ($autoUpdateStatus ? 'ON' : 'OFF') . "\n",
        FILE_APPEND
    );

    $saved = json_decode(file_get_contents($settingsFile), true);
    if (!isset($saved['auto_update'])) {
        throw new Exception("File verification failed");
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Auto update status updated successfully',
        'auto_update' => $autoUpdateStatus
    ]);
}
catch (Exception $e) {
    http_response_code(500);
    error_log("Error saving settings: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save settings: ' . $e->getMessage()
    ]);
}
?>
