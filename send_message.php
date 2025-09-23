<?php
include_once 'database.php';
include_once 'config.php';
include_once 'dotenv_loader.php';
loadEnv(__DIR__ . '/.env');

$endpoint = 'https://mywifi.weagate.com/api/send-message';
$token = $_ENV['API_TOKEN'];

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
        $periode = date('F Y', strtotime($row['periode']));
        
        // Hitung kekurangan
        $kurang = $monthly_fee - $row['total_bayar'];

        // Hanya simpan yang Belum Bayar atau Kurang
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

        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['list_bulan'][] =
            "- " . date('F', strtotime($row['periode'])) . ": *$status* (Kurang Rp " . number_format($kurang, 0, ',', '.') . ")";
        $anggota_messages[$anggota_id]['pesan_per_tahun'][$tahun]['total_kurang_tahun'] += $kurang;
        $anggota_messages[$anggota_id]['total_kurang_global'] += $kurang;
    }

    // Kirim pesan
    foreach ($anggota_messages as $anggota) {
        $no_hp = $anggota['no_hp'];
        $nama = $anggota['nama'];
        $message_body = "*" . ORGANIZATION_NAME . "*\n\nHalo *$nama*, berikut iuran Anda yang belum lunas:\n\n";

        foreach ($anggota['pesan_per_tahun'] as $tahun => $data) {
            $message_body .= "*Tahun $tahun*\n";
            $message_body .= implode("\n", $data['list_bulan']) . "\n";
            $message_body .= "Total Kurang Tahun $tahun: *Rp " . number_format($data['total_kurang_tahun'], 0, ',', '.') . "*\n\n";
        }

        $message_body .= "Total keseluruhan kekurangan: *Rp " . number_format($anggota['total_kurang_global'], 0, ',', '.') . "*\n\n";
        $message_body .= "Mohon segera diselesaikan. Terima kasih.";

        if (substr($no_hp, 0, 1) === '0') {
            $no_hp = '62' . substr($no_hp, 1);
        }

        $payload = [
            'token' => $token,
            'to' => $no_hp,
            'type' => 'text',
            'message' => $message_body
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        curl_close($ch);
        sleep(1);

        $api_response = json_decode($response, true);
        if (isset($api_response['status']) && $api_response['status'] == 'success') {
            echo "Pesan berhasil dikirim ke $nama ($no_hp)\n";
        } else {
            echo "Pesan gagal dikirim ke $nama. Respon API: $response\n";
        }
    }

} catch (mysqli_sql_exception $e) {
    echo "Terjadi kesalahan database: " . $e->getMessage();
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>
