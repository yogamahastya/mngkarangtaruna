<?php
include_once __DIR__ . '/../database.php';
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../dotenv_loader.php';

loadEnv(__DIR__ . '/../.env');

// --- PENGATURAN WAHA ---
$waha_endpoint = 'https://waha.nuxera.my.id/api/sendText'; 
$waha_session_name = 'default'; 
$waha_api_key = $_ENV['API_TOKEN'];

try {
    $monthly_fee = DUES_MONTHLY_FEE;

    // Ambil semua data iuran dari prosedur
    $stmt = $conn->prepare("CALL cek_iuran_global_for_wa(NULL)");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Tidak ada iuran yang perlu dikirim notifikasi.";
        $stmt->close();
        $conn->close();
        exit;
    }

    $anggota_messages = [];
    while ($row = $result->fetch_assoc()) {
        $anggota_id = $row['anggota_id'];
        $nama = $row['nama_lengkap'];
        $no_hp = $row['no_hp'];
        $tahun = $row['tahun'];
        
        // Hitung kekurangan
        $kurang = $monthly_fee - $row['total_bayar'];

        if ($kurang <= 0) {
            continue;
        }

        if (!isset($anggota_messages[$anggota_id])) {
            $anggota_messages[$anggota_id] = [
                'nama' => $nama,
                'no_hp' => $no_hp,
                'pesan_per_tahun' => [],
                'total_kurang_global' => 0
            ];
        }

        if (!isset($anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun])) {
            $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun] = [
                'list_bulan' => [],
                'total_kurang_tahun' => 0
            ];
        }

        $status = $row['total_bayar'] == 0 ? 'Belum Bayar' : 'Kurang';

        // Bedakan tampilan Belum Bayar & Kurang
        if ($status === 'Belum Bayar') {
            $text_status = "➡️ " . date('F', strtotime($row['periode'])) . ": *$status*";
        } else {
            $text_status = "➡️ " . date('F', strtotime($row['periode'])) . ": *$status* (Kurang Rp " . number_format($kurang, 0, ',', '.') . ")";
        }

        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['list_bulan'][] = $text_status;
        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['total_kurang_tahun'] += $kurang;
        $anggota_messages[$anggota_id]['total_kurang_global'] += $kurang;
    }

    // Kirim pesan
    foreach ($anggota_messages as $anggota_id => $anggota) {
        $no_hp = $anggota['no_hp'];
        $nama = $anggota['nama'];

        // --- Base URL otomatis ---
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        $base_url = $protocol . "://" . $host . $script_dir;

        // Buat link detail iuran per member
        $link_web = $base_url . "/iuran.php?tab=iuran&member_id=" . $anggota_id;

        // --- Format pesan ---
        $message_body = "🏠 *" . ORGANIZATION_NAME . "* 🏠\n";
        $message_body .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message_body .= "👋 Halo *$nama*,\n";
        $message_body .= "Berikut detail iuran Anda yang masih tertunggak:\n\n";

        foreach ($anggota['pesan_per_tahun'] as $tahun => $data) {
            $message_body .= "📅 *Tahun $tahun*\n";
            $message_body .= "──────────────────\n";
            $message_body .= implode("\n", $data['list_bulan']) . "\n\n";
            $message_body .= "💰 Total Kurang Tahun $tahun: *Rp " . number_format($data['total_kurang_tahun'], 0, ',', '.') . "*\n\n";
        }

        $message_body .= "📊 Total Kekurangan Keseluruhan: *Rp " . number_format($anggota['total_kurang_global'], 0, ',', '.') . "*\n";
        $message_body .= "━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $message_body .= "🌐 Lihat detail lengkap di web:\n";
        $message_body .= $link_web . "\n\n";
        $message_body .= "🙏 Mohon segera diselesaikan.\n";
        $message_body .= "Terima kasih ✨";

        // --- Format nomor WAHA ---
        if (substr($no_hp, 0, 1) === '0') {
            $no_hp = '62' . substr($no_hp, 1);
        }
        $waha_chat_id = $no_hp . '@c.us';

        // Payload untuk WAHA
        $waha_payload = [
            'session' => $waha_session_name,
            'chatId' => $waha_chat_id,
            'text' => $message_body
        ];

        // Header WAHA
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
        sleep(1);

        $api_response = json_decode($response, true);
        if (isset($api_response['result']) && $api_response['result'] === 'success') {
            echo "✅ Pesan berhasil dikirim ke $nama ($no_hp)\n";
        } else {
            echo "❌ Pesan gagal dikirim ke $nama. Respon API: $response\n";
        }
    }

} catch (mysqli_sql_exception $e) {
    echo "⚠️ Terjadi kesalahan database: " . $e->getMessage();
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
