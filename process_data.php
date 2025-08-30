<?php
// =================================================================
// Memuat file koneksi dan fungsi
// =================================================================
require_once 'database.php';
require_once 'functions.php';

// Penting: Baris ini memberitahu browser untuk menggunakan UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Memulai sesi
session_start();

// =================================================================
// Inisialisasi variabel dan pengambilan parameter
// =================================================================
$active_tab = $_GET['tab'] ?? 'anggota';
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$searchTerm = trim($_GET['search'] ?? '');
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;

// Inisialisasi array data
$anggota = [];
$kegiatan = [];
$keuangan = [];
$iuran = [];
$iuran17 = [];

// Inisialisasi variabel total untuk semua tab agar tidak ada warning
$total_anggota = 0;
$total_anggota_absensi = 0;
$total_kegiatan = 0;
$total_keuangan = 0;
$total_iuran = 0;
$total_iuran17 = 0;

// =================================================================
// Paginasi dan pengambilan data utama
// =================================================================
$total_rows = countRowsWithFilter($conn, $active_tab, $searchTerm, $selectedYear);
$total_pages = ceil($total_rows / $limit);

switch ($active_tab) {
    case 'anggota':
        $anggota = fetchDataWithPagination($conn, 'anggota', $start, $limit, $searchTerm);
        $total_anggota = $total_rows;
        break;
    case 'absensi':
        $anggota = fetchDataWithPagination($conn, 'absensi', $start, $limit, $searchTerm);
        $total_anggota_absensi = $total_rows;
        break;
    case 'kegiatan':
        $kegiatan = fetchDataWithPagination($conn, 'kegiatan', $start, $limit, $searchTerm);
        $total_kegiatan = $total_rows;
        break;
    case 'keuangan':
        $keuangan = fetchDataWithPagination($conn, 'keuangan', $start, $limit, $searchTerm, $selectedYear);
        $total_keuangan = $total_rows;
        break;
    case 'iuran':
        $iuran = fetchDataWithPagination($conn, 'iuran', $start, $limit, $searchTerm, $selectedYear);
        $total_iuran = $total_rows;
        break;
    case 'iuran17':
        $iuran17 = fetchDataWithPagination($conn, 'iuran17', $start, $limit, $searchTerm, $selectedYear);
        $total_iuran17 = $total_rows;
        break;
}

// =================================================================
// Logika Absensi
// =================================================================
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen_submit'])) {
    $anggotaId = intval($_POST['anggota_id'] ?? 0);
    $userLat = floatval($_POST['latitude'] ?? 0);
    $userLon = floatval($_POST['longitude'] ?? 0);

    $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $clientIp = explode(',', $clientIp)[0];

    $canProceed = true;
    $stmt = $conn->prepare("SELECT last_attempt_time FROM ip_attendance_cooldown WHERE ip_address = ?");
    $stmt->bind_param("s", $clientIp);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastAttempt = $result->fetch_assoc();
    $stmt->close();

    if ($lastAttempt) {
        $lastAttemptTime = new DateTime($lastAttempt['last_attempt_time']);
        $currentTime = new DateTime();
        $interval = $currentTime->getTimestamp() - $lastAttemptTime->getTimestamp();
        if ($interval < (defined('COOLDOWN_SECONDS') ? COOLDOWN_SECONDS : 60)) {
            $message = "Anda sudah absen. Harap tunggu " . (COOLDOWN_SECONDS - $interval) . " detik sebelum mencoba lagi.";
            $messageType = "danger";
            $canProceed = false;
        }
    }

    // Ambil data lokasi absensi jika bisa melanjutkan
    if ($canProceed) {
        $stmtLoc = $conn->prepare("SELECT latitude, longitude, toleransi_jarak FROM lokasi_absensi WHERE id = 1 LIMIT 1");
        $stmtLoc->execute();
        $result = $stmtLoc->get_result();
        $lokasiDb = $result->fetch_assoc();
        $stmtLoc->close();

        $jarakToleransi = $lokasiDb['toleransi_jarak'] ?? (defined('DEFAULT_TOLERANCE') ? DEFAULT_TOLERANCE : 100);
        $lokasiPerkumpulan = [
            'latitude' => $lokasiDb['latitude'] ?? (defined('DEFAULT_LATITUDE') ? DEFAULT_LATITUDE : 0),
            'longitude' => $lokasiDb['longitude'] ?? (defined('DEFAULT_LONGITUDE') ? DEFAULT_LONGITUDE : 0)
        ];

        if ($anggotaId === 0 || empty($userLat) || empty($userLon)) {
            $message = "Gagal absen. Data absen tidak lengkap (ID anggota, lokasi tidak valid).";
            $messageType = "danger";
        } else {
            $stmtCheck = $conn->prepare("SELECT COUNT(*) as cnt FROM absensi WHERE anggota_id = ? AND DATE(tanggal_absen) = CURDATE()");
            $stmtCheck->bind_param("i", $anggotaId);
            $stmtCheck->execute();
            $stmtCheck->bind_result($absenCount);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($absenCount > 0) {
                $message = "Anda sudah melakukan absensi untuk hari ini.";
                $messageType = "danger";
                $stmtCooldown = $conn->prepare("INSERT INTO ip_attendance_cooldown (ip_address, last_attempt_time) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_attempt_time = NOW()");
                $stmtCooldown->bind_param("s", $clientIp);
                $stmtCooldown->execute();
                $stmtCooldown->close();
            } else {
                $distance = haversineGreatCircleDistance(
                    $lokasiPerkumpulan['latitude'],
                    $lokasiPerkumpulan['longitude'],
                    $userLat,
                    $userLon
                );

                if ($distance <= $jarakToleransi) {
                    $stmt = $conn->prepare("INSERT INTO absensi (anggota_id, tanggal_absen, latitude, longitude) VALUES (?, NOW(), ?, ?)");
                    $stmt->bind_param("idd", $anggotaId, $userLat, $userLon);
                    
                    if ($stmt->execute()) {
                        $message = "Absensi berhasil! Selamat datang di perkumpulan.";
                        $messageType = "success";
                        
                        // ## PERBAIKAN DI SINI ##
                        // Gunakan nama variabel baru ($stmt_cooldown) agar tidak menimpa $stmt
                        $stmt_cooldown = $conn->prepare("INSERT INTO ip_attendance_cooldown (ip_address, last_attempt_time) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_attempt_time = NOW()");
                        $stmt_cooldown->bind_param("s", $clientIp);
                        $stmt_cooldown->execute();
                        $stmt_cooldown->close(); // Tutup statement yang baru dibuat
                    } else {
                        $message = "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
                        $messageType = "danger";
                    }
                    $stmt->close(); // Tutup statement insert absensi dengan aman
                } else {
                    $message = "Gagal absen. Anda berada terlalu jauh dari lokasi perkumpulan. Jarak Anda: " . round($distance, 2) . " meter";
                    $messageType = "danger";
                }
            }
        }
    }
    
    // Jika ini adalah request AJAX, kirim response JSON
    if (!empty($_POST['is_ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['message' => $message, 'type' => $messageType]);
        exit();
    }
}


// =================================================================
// Riwayat absensi
// =================================================================
$attendanceMemberId = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;
$attendanceMemberBreakdown = null;
$selectedAttendanceYear = isset($_GET['year_absensi']) ? intval($_GET['year_absensi']) : date('Y');

if ($attendanceMemberId) {
    $attendanceMemberBreakdown = fetchMemberAttendanceBreakdownWithYear($conn, $attendanceMemberId, $selectedAttendanceYear);
}

// =================================================================
// Riwayat iuran (Untuk Detail Anggota)
// =================================================================
$memberId = $attendanceMemberId;
$memberDuesBreakdown = null;

if (($active_tab == 'iuran' || $active_tab == 'iuran17') && $memberId) {
    if ($active_tab == 'iuran17') {
        $memberDuesBreakdown = fetchMemberDuesBreakdownWithYear($conn, $memberId, $selectedYear, 'iuran17', (defined('DUES_MONTHLY_FEE17') ? DUES_MONTHLY_FEE17 : 0));
    } else {
        $memberDuesBreakdown = fetchMemberDuesBreakdownWithYear($conn, $memberId, $selectedYear, 'iuran', (defined('DUES_MONTHLY_FEE') ? DUES_MONTHLY_FEE : 0));
    }
}

// =================================================================
// Gambar profil default
// =================================================================
$profile_image = 'https://img.freepik.com/free-psd/contact-icon-illustration-isolated_23-2151903337.jpg?semt=ais_hybrid&w=740';
?>