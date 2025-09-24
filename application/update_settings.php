<?php
include_once __DIR__ . '/../dotenv_loader.php';

loadEnv(__DIR__ . '/../.env');

$UPDATE_TOKEN = getenv('UPDATE_TOKEN'); // Ambil dari .env

// Pastikan skrip hanya bisa diakses via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

header('Content-Type: application/json');

// Ambil header Authorization
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

// Lokasi file untuk menyimpan status auto update
$settingsFile = __DIR__ . '/auto_update_status.json';

try {
    if (file_put_contents($settingsFile, json_encode(['auto_update' => $autoUpdateStatus]))) {

        // ==== Tambah / hapus cron job otomatis ====
        if (function_exists('shell_exec')) {
            $phpPath = trim(shell_exec('which php')) ?: "/usr/bin/php"; 
            $cronCmd = "* * * * * $phpPath " . __DIR__ . "/updater.php >/dev/null 2>&1";
            $currentCron = shell_exec('crontab -l 2>/dev/null');

            if ($autoUpdateStatus) {
                if (strpos($currentCron, $cronCmd) === false) {
                    $newCron = trim($currentCron) . PHP_EOL . $cronCmd . PHP_EOL;
                    file_put_contents("/tmp/mycron", $newCron);
                    shell_exec("crontab /tmp/mycron");
                    unlink("/tmp/mycron");
                }
            } else {
                if (strpos($currentCron, $cronCmd) !== false) {
                    $newCron = str_replace($cronCmd, '', $currentCron);
                    file_put_contents("/tmp/mycron", $newCron);
                    shell_exec("crontab /tmp/mycron");
                    unlink("/tmp/mycron");
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Auto update status updated.']);
    } else {
        throw new Exception("Failed to write to file.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save settings file: ' . $e->getMessage()]);
}
