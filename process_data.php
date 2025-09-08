<?php
// =================================================================
// Memuat file koneksi dan fungsi
// =================================================================
require_once 'database.php';
require_once 'functions.php';
date_default_timezone_set('Asia/Jakarta');

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
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($page)) : 1;
$start = ($page - 1) * $limit;

// Inisialisasi array data
$anggota = [];
$kegiatan = [];
$keuangan = [];
$iuran = [];
$iuran17 = [];

// Inisialisasi variabel total untuk semua tab
$total_anggota = 0;
$total_anggota_absensi = 0;
$total_kegiatan = 0;
$total_keuangan = 0;
$total_iuran = 0;
$total_iuran17 = 0;

// =================================================================
// Inisialisasi variabel dan pengambilan parameter
// =================================================================
$active_tab = $_GET['tab'] ?? 'anggota';
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$searchTerm = trim($_GET['search'] ?? '');
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($page)) : 1;
$start = ($page - 1) * $limit;

// Inisialisasi array data
$anggota = [];
$kegiatan = [];
$keuangan = [];
$iuran = [];
$iuran17 = [];

// Inisialisasi variabel total untuk semua tab
$total_anggota = 0;
$total_anggota_absensi = 0;
$total_kegiatan = 0;
$total_keuangan = 0;
$total_iuran = 0;
$total_iuran17 = 0;

// Logika Absensi
// =================================================================
$message = '';
$messageType = '';
$remaining_time = 0;
$lokasi_absensi = null; // Inisialisasi variabel di awal

// Hapus baris di bawah ini karena kita akan mengambil durasi dari DB
// $session_duration = 30; 

$is_absensi_active = false;
$absent_members = []; // Inisialisasi array untuk anggota yang sudah absen

// Ambil data lokasi absensi terakhir dari database, termasuk durasi
$stmt_loc = $conn->prepare("SELECT latitude, longitude, toleransi_jarak, waktu_dibuat, durasi_absensi FROM lokasi_absensi ORDER BY waktu_dibuat DESC LIMIT 1");
if ($stmt_loc) {
    $stmt_loc->execute();
    $result_loc = $stmt_loc->get_result();
    if ($lokasi_absensi = $result_loc->fetch_assoc()) {
        $waktu_dibuat = new DateTime($lokasi_absensi['waktu_dibuat']);
        $waktu_sekarang = new DateTime('now');
        $interval = $waktu_sekarang->getTimestamp() - $waktu_dibuat->getTimestamp();

        // **Perbaikan Kritis**: Gunakan durasi dari database
        // dan konversi ke detik (jika kamu menyimpannya dalam menit)
        $session_duration = intval($lokasi_absensi['durasi_absensi']) * 60;

        // VALIDASI KRITIS: HANYA AKTIFKAN SESI JIKA MASIH DALAM DURASI
        if ($interval < $session_duration) {
            $is_absensi_active = true;
            $remaining_time = $session_duration - $interval;
        } else {
            // Sesi sudah berakhir, atur sisa waktu menjadi 0
            $is_absensi_active = false;
            $remaining_time = 0;
        }
    }
    $stmt_loc->close();
}

// Ambil semua ID anggota yang sudah absen dalam sesi aktif ini
if ($is_absensi_active) {
    $stmt_absen_view = $conn->prepare("SELECT anggota_id FROM absensi WHERE tanggal_absen >= ?");
    if ($stmt_absen_view) {
        $waktu_mulai_absen = $lokasi_absensi['waktu_dibuat'];
        $stmt_absen_view->bind_param("s", $waktu_mulai_absen);
        $stmt_absen_view->execute();
        $result_absen = $stmt_absen_view->get_result();
        while ($row_absen = $result_absen->fetch_assoc()) {
            $absent_members[] = $row_absen['anggota_id'];
        }
        $stmt_absen_view->close();
    }
}

// Perbaikan Logika POST Absensi
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen_submit'])) {
    $anggotaId = intval($_POST['anggota_id'] ?? 0);
    $userLat = floatval($_POST['latitude'] ?? 0);
    $userLon = floatval($_POST['longitude'] ?? 0);

    // Perbaikan: Tambahkan logika untuk mendapatkan IP publik yang benar
    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    $userIp = get_client_ip();

    // LANGKAH 1: Validasi waktu utama. Jika absensi tidak aktif, segera tolak.
    if (!$is_absensi_active) {
        $message = "Sesi absensi sudah berakhir. Anda terlambat untuk absen.";
        $messageType = "danger";
    } 
    // LANGKAH 2: Validasi data wajib
    elseif ($anggotaId === 0 || empty($userLat) || empty($userLon) || empty($lokasi_absensi['latitude'])) {
        $message = "Gagal absen. Data tidak lengkap atau lokasi perkumpulan belum disetel.";
        $messageType = "danger";
    } 
    // LANGKAH 3: Jalankan logika absensi jika semua validasi awal lolos
    else {
        // Cek apakah anggota sudah absen dalam sesi aktif ini
        $stmt_check_absen = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE anggota_id = ? AND tanggal_absen >= ?");
        $waktu_mulai_absen = $lokasi_absensi['waktu_dibuat'];
        $stmt_check_absen->bind_param("is", $anggotaId, $waktu_mulai_absen);
        $stmt_check_absen->execute();
        $stmt_check_absen->bind_result($isAlreadyAbsent);
        $stmt_check_absen->fetch();
        $stmt_check_absen->close();

        // Cek apakah IP sudah absen dalam sesi aktif ini
        $stmt_check_ip = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE ip_address = ? AND tanggal_absen >= ?");
        $stmt_check_ip->bind_param("ss", $userIp, $waktu_mulai_absen);
        $stmt_check_ip->execute();
        $stmt_check_ip->bind_result($isIpAlreadyAbsent);
        $stmt_check_ip->fetch();
        $stmt_check_ip->close();

        if ($isAlreadyAbsent > 0) {
            $message = "Anda sudah melakukan absensi untuk sesi ini.";
            $messageType = "danger";
        } elseif ($isIpAlreadyAbsent > 0) {
            $message = "Perangkat ini sudah digunakan untuk absen pada sesi ini. Tidak dapat absen ganda.";
            $messageType = "danger";
        } else {
            // Periksa jarak menggunakan fungsi Haversine
            $distance = haversineGreatCircleDistance(
                $lokasi_absensi['latitude'],
                $lokasi_absensi['longitude'],
                $userLat,
                $userLon
            );
            
            if ($distance <= $lokasi_absensi['toleransi_jarak']) {
                $stmt_insert = $conn->prepare("INSERT INTO absensi (anggota_id, tanggal_absen, latitude, longitude, ip_address) VALUES (?, NOW(), ?, ?, ?)");
                $stmt_insert->bind_param("idds", $anggotaId, $userLat, $userLon, $userIp);
                
                if ($stmt_insert->execute()) {
                    $message = "Absensi berhasil! Selamat datang di perkumpulan.";
                    $messageType = "success";
                } else {
                    $message = "Terjadi kesalahan saat menyimpan data: " . $stmt_insert->error;
                    $messageType = "danger";
                }
                $stmt_insert->close();
            } else {
                $message = "Gagal absen. Anda berada terlalu jauh dari lokasi perkumpulan. Jarak Anda: " . round($distance, 2) . " meter";
                $messageType = "danger";
            }
        }
    }
}

// 3. Ambil semua ID anggota yang sudah absen dalam sesi aktif ini (untuk tampilan)
if ($is_absensi_active) {
    $stmt_absen_view = $conn->prepare("SELECT anggota_id FROM absensi WHERE tanggal_absen >= ?");
    if ($stmt_absen_view) {
        $waktu_mulai_absen = $lokasi_absensi['waktu_dibuat'];
        $stmt_absen_view->bind_param("s", $waktu_mulai_absen);
        $stmt_absen_view->execute();
        $result_absen = $stmt_absen_view->get_result();
        while ($row_absen = $result_absen->fetch_assoc()) {
            $absent_members[] = $row_absen['anggota_id'];
        }
        $stmt_absen_view->close();
    }
}

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
// Riwayat absensi (Untuk Detail Anggota)
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