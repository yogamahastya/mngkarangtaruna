<?php
// Pastikan skrip hanya bisa diakses via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

// Log untuk debugging (opsional, bisa dihapus di production)
error_log('POST received: ' . file_get_contents('php://input'));

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validasi data
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

// Konversi ke boolean - terima berbagai format
$autoUpdateStatus = filter_var($data['auto_update'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

if ($autoUpdateStatus === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid boolean value for auto_update']);
    exit;
}

// Lokasi file untuk menyimpan status auto update
$settingsFile = __DIR__ . '/auto_update_status.json';

try {
    // Pastikan direktori bisa ditulis
    if (!is_writable(dirname($settingsFile))) {
        throw new Exception("Directory is not writable");
    }
    
    // Simpan dengan pretty print untuk debugging
    $jsonContent = json_encode(['auto_update' => $autoUpdateStatus], JSON_PRETTY_PRINT);
    
    if ($jsonContent === false) {
        throw new Exception("Failed to encode JSON: " . json_last_error_msg());
    }
    
    $bytesWritten = file_put_contents($settingsFile, $jsonContent, LOCK_EX);
    
    if ($bytesWritten === false) {
        throw new Exception("Failed to write to file");
    }
    
    // Log untuk debugging (opsional)
    error_log("Auto update status saved: " . ($autoUpdateStatus ? 'true' : 'false'));
    
    // Verifikasi file tersimpan dengan benar
    $savedContent = file_get_contents($settingsFile);
    $savedData = json_decode($savedContent, true);
    
    if (!isset($savedData['auto_update'])) {
        throw new Exception("File saved but verification failed");
    }
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Auto update status updated successfully',
        'auto_update' => $autoUpdateStatus
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error saving settings: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to save settings: ' . $e->getMessage()
    ]);
}
?>