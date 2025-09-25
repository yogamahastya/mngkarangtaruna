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

// (Opsional) Batasi hanya user tertentu yang bisa eksekusi script
$allowedUsers = ['root', 'www-data' , 'www', 'desktop-ordme65\\devnet']; // sesuaikan dengan user cron/daemon kamu
$currentUser = trim(shell_exec("whoami"));
if (!in_array($currentUser, $allowedUsers)) {
    echo json_encode([
        "status" => "forbidden",
        "message" => "User '$currentUser' tidak diizinkan menjalankan script update ini."
    ], JSON_PRETTY_PRINT);
    exit;
}

include_once __DIR__ . '/../database.php';
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../dotenv_loader.php';

loadEnv(__DIR__ . '/../.env');

// --- PENGATURAN WAHA ---
$waha_endpoint      = 'https://waha.nuxera.my.id/api/sendText';
$waha_session_name  = 'default';
$waha_api_key       = $_ENV['API_TOKEN'];

// Set header JSON agar output rapi
header('Content-Type: application/json');

try {
    $monthly_fee = DUES_MONTHLY_FEE;

    // Ambil semua data iuran dari prosedur
    $stmt = $conn->prepare("CALL cek_iuran_global_for_wa(NULL)");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status'  => 'ok',
            'message' => 'Tidak ada iuran yang perlu dikirim notifikasi.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $stmt->close();
        $conn->close();
        exit;
    }

    $anggota_messages = [];
    while ($row = $result->fetch_assoc()) {
        $anggota_id = $row['anggota_id'];
        $nama       = $row['nama_lengkap'];
        $no_hp      = $row['no_hp'];
        $tahun      = $row['tahun'];

        // Hitung kekurangan
        $kurang = $monthly_fee - $row['total_bayar'];
        if ($kurang <= 0) {
            continue;
        }

        if (!isset($anggota_messages[$anggota_id])) {
            $anggota_messages[$anggota_id] = [
                'nama'               => $nama,
                'no_hp'              => $no_hp,
                'pesan_per_tahun'    => [],
                'total_kurang_global'=> 0
            ];
        }

        if (!isset($anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun])) {
            $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun] = [
                'list_bulan'         => [],
                'total_kurang_tahun' => 0
            ];
        }

        $status = $row['total_bayar'] == 0 ? 'Belum Bayar' : 'Kurang';

        if ($status === 'Belum Bayar') {
            $text_status = "➡️ " . date('F', strtotime($row['periode'])) . ": *$status*";
        } else {
            $text_status = "➡️ " . date('F', strtotime($row['periode'])) . ": *$status* (Kurang Rp " .
                           number_format($kurang, 0, ',', '.') . ")";
        }

        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['list_bulan'][] = $text_status;
        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['total_kurang_tahun'] += $kurang;
        $anggota_messages[$anggota_id]['total_kurang_global'] += $kurang;
    }

    $results = [];

    // Kirim pesan
    foreach ($anggota_messages as $anggota_id => $anggota) {
        $no_hp = $anggota['no_hp'];
        $nama  = $anggota['nama'];

        // Base URL
        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host      = $_SERVER['HTTP_HOST'];
        $base_url  = $protocol . "://" . $host;

        // Tahun terakhir
        $tahun_terakhir = max(array_keys($anggota['pesan_per_tahun']));

        // Link detail
        $link_web = $base_url . "/?tab=iuran&member_id=" . $anggota_id . "&year=" . $tahun_terakhir;

        // Format pesan
        $message_body  = "🏠 *" . ORGANIZATION_NAME . "* 🏠\n";
        $message_body .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message_body .= "👋 Halo *$nama*,\n";
        $message_body .= "Berikut detail iuran Anda yang masih belum lunas:\n\n";

        foreach ($anggota['pesan_per_tahun'] as $tahun => $data) {
            $message_body .= "📅 *Tahun $tahun*\n";
            $message_body .= "──────────────────\n";
            $message_body .= implode("\n", $data['list_bulan']) . "\n\n";
            $message_body .= "💰 Total Kurang Tahun $tahun: *Rp " .
                             number_format($data['total_kurang_tahun'], 0, ',', '.') . "*\n\n";
        }

        $message_body .= "📊 Total Kekurangan Keseluruhan: *Rp " .
                         number_format($anggota['total_kurang_global'], 0, ',', '.') . "*\n";
        $message_body .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message_body .= "🌐 Lihat detail lengkap di web:\n" . $link_web . "\n\n";
        $message_body .= "🙏 Mohon segera diselesaikan.\n";
        $message_body .= "Terima kasih ✨";

        // Format nomor WAHA
        if (substr($no_hp, 0, 1) === '0') {
            $no_hp = '62' . substr($no_hp, 1);
        }
        $waha_chat_id = $no_hp . '@c.us';

        // Payload
        $waha_payload = [
            'session' => $waha_session_name,
            'chatId'  => $waha_chat_id,
            'text'    => $message_body
        ];

        // Headers
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $waha_api_key
        ];

        // Kirim ke WAHA
        $ch = curl_init($waha_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($waha_payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        // Simpan hasil sederhana: hanya nama, body, timestamp
        $results[] = [
            'nama'      => $nama,
            'body'      => $message_body,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Jeda random 10 - 15 detik sebelum kirim pesan berikutnya
        sleep(rand(10, 20));
    }

    echo json_encode([
        'status'  => 'done',
        'results' => $results
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (mysqli_sql_exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan database',
        'error'   => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

if (isset($conn) && $conn) {
    $conn->close();
}
