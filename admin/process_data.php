<?php
// Memuat file koneksi dan fungsi
require_once '../database.php';
require_once 'functions.php';

// Penting: Baris ini memberitahu browser untuk menggunakan UTF-8
header('Content-Type: text/html; charset=UTF-8');
session_start();
include '../config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login/');
    exit();
}

$allowed_roles = ['sekretaris', 'bendahara', 'admin', 'superadmin'];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header('Location: akses_ditolak.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $tab = $_POST['tab'] ?? '';
    $success = false;
    $message = "";

    $data = [];
    if (isset($_POST['data']) && is_array($_POST['data'])) {
        $data = array_map('trim', $_POST['data']);
    }

    // Ambil detail objek (nama/judul) sebelum operasi, untuk digunakan di pesan
    $objectName = '';
    
    // Logika untuk mengambil nama/judul objek berdasarkan tab
    // Komentar: Menambahkan kondisi untuk tab 'iuran17'.
    if ($tab === 'anggota' && isset($data['nama_lengkap'])) {
        $objectName = $data['nama_lengkap'];
    } elseif ($tab === 'kegiatan' && isset($data['nama_kegiatan'])) {
        $objectName = $data['nama_kegiatan'];
    } elseif ($tab === 'iuran' || $tab === 'iuran17') {
        if (isset($data['anggota_id'])) {
            $objectName = getAnggotaNameById($conn, $data['anggota_id']);
        }
    } 
    
    // Logika untuk mengambil nama/judul objek saat edit atau hapus
    // Komentar: Menambahkan kondisi untuk tab 'iuran17'.
    if (($action == 'edit' || $action == 'delete') && isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "SELECT * FROM `$tab` WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultData = $stmt->get_result()->fetch_assoc();
        if ($resultData) {
            if ($tab === 'anggota') {
                $objectName = $resultData['nama_lengkap'];
            } elseif ($tab === 'kegiatan') {
                $objectName = $resultData['nama_kegiatan'];
            } elseif ($tab === 'keuangan') {
                // Perbaikan di baris ini: hilangkan jumlah
                $objectName = $resultData['jenis_transaksi'];
            } elseif ($tab === 'iuran' || $tab === 'iuran17') {
                $anggotaNama = getAnggotaNameById($conn, $resultData['anggota_id']);
                // Komentar: Menggunakan variabel tab untuk menentukan jenis iuran dalam pesan.
                $objectName = "{$tab} " . $anggotaNama;
            } elseif ($tab === 'users') {
                $objectName = $resultData['username'];
            }
        }
    }
    
    if ($action == 'add') {
        $result = handleAdd($conn, $tab, $data);
        if ($result === true) {
            $success = true;
            $message = "Operasi tambah data {$objectName} berhasil! 🎉";
        } elseif ($result === 'duplicate_entry') {
            $success = false;
            // Komentar: Menambahkan kondisi untuk tab 'iuran17' pada pesan kesalahan duplikat.
            if ($tab === 'anggota' && isset($data['nama_lengkap'])) {
                $message = "Operasi gagal: Nama anggota '{$data['nama_lengkap']}' sudah ada. Silakan gunakan nama lain. ❌";
            } elseif ($tab === 'iuran' || $tab === 'iuran17') {
                $tanggal = $data['tanggal_bayar'] ?? 'N/A';
                $message = "Operasi gagal: Data {$tab} untuk {$objectName} pada tanggal {$tanggal} sudah ada. ❗";
            } else {
                $message = "Operasi gagal: Data duplikat terdeteksi. Silakan periksa kembali entri Anda. ❗";
            }
        } else {
            $success = false;
            $message = "Operasi tambah data gagal: " . $conn->error;
        }
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $success = handleEdit($conn, $tab, $id, $data);
        $message = $success ? "Operasi edit data {$objectName} berhasil! ✅" : "Operasi edit data {$objectName} gagal: " . $conn->error;
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        $success = handleDelete($conn, $tab, $id);
        $message = $success ? "Operasi hapus data {$objectName} berhasil! 🗑️" : "Operasi hapus data {$objectName} gagal: " . $conn->error;
    } elseif ($action == 'update_location') {
        $latitude = filter_var($_POST['lokasi_latitude'], FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($_POST['lokasi_longitude'], FILTER_VALIDATE_FLOAT);
        $toleransi = filter_var($_POST['jarak_toleransi'], FILTER_VALIDATE_INT);
        $durasi_absensi = filter_var($_POST['lokasi_durasi'], FILTER_VALIDATE_INT); // Tambahkan baris ini

        if ($latitude === false || $longitude === false || $toleransi === false || $durasi_absensi === false) {
            $success = false;
            $message = "Operasi update lokasi gagal: Data tidak valid.";
        } else {
            // Ambil waktu saat ini sebagai waktu_dibuat yang baru
            $success = handleUpdateLocation($conn, $latitude, $longitude, $toleransi, $durasi_absensi); // Perbaiki parameter yang dikirim
        }
        $message = $success ? "Operasi update lokasi berhasil! 📍" : "Operasi update lokasi gagal: " . $conn->error;
        $tab = 'users';
    }
}
// =================================================================
// Logika Tampilan & Paginasi Utama
// =================================================================
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'anggota';
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$start = ($page - 1) * $limit;
$total_rows = countRowsWithFilter($conn, $active_tab, $searchTerm, $selectedYear);
$total_pages = ceil($total_rows / $limit);
$anggota = [];
$kegiatan = [];
$keuangan = [];
$iuran = [];
// Komentar: Menambahkan inisialisasi variabel untuk iuran17.
$iuran17 = [];
$users = [];
if ($active_tab === 'anggota') {
    $anggota = fetchDataWithPagination($conn, 'anggota', $start, $limit, $searchTerm, $selectedYear);
} elseif ($active_tab === 'kegiatan') {
    $kegiatan = fetchDataWithPagination($conn, 'kegiatan', $start, $limit, $searchTerm, $selectedYear);
} elseif ($active_tab === 'keuangan') {
    $keuangan = fetchDataWithPagination($conn, 'keuangan', $start, $limit, $searchTerm, $selectedYear);
} elseif ($active_tab === 'iuran') {
    $iuran = fetchDataWithPagination($conn, 'iuran', $start, $limit, $searchTerm, $selectedYear);
} elseif ($active_tab === 'iuran17') {
    // Komentar: Menambahkan pengambilan data untuk tab 'iuran17'.
    $iuran17 = fetchDataWithPagination($conn, 'iuran17', $start, $limit, $searchTerm, $selectedYear);
} elseif ($active_tab === 'users') {
    $users = fetchDataWithPagination($conn, 'users', $start, $limit, $searchTerm, $selectedYear);
}
$totalAnggota = countRowsWithFilter($conn, 'anggota');
$totalPemasukan = 0;
$totalPengeluaran = 0;
// Komentar: Memuat data keuangan untuk perhitungan total.
$allKeuangan = fetchDataWithPagination($conn, 'keuangan', 0, 10000, null, $selectedYear);
foreach ($allKeuangan as $transaksi) {
    if ($transaksi['jenis_transaksi'] == 'pemasukan') {
        $totalPemasukan += $transaksi['jumlah'];
    } else {
        $totalPengeluaran += $transaksi['jumlah'];
    }
}
$saldo = $totalPemasukan - $totalPengeluaran;
$totalIuran = 0;
// Komentar: Menambahkan perhitungan total iuran untuk iuran17.
$totalIuran17 = 0;
// Komentar: Mengambil data iuran dari tabel 'iuran' dan 'iuran17' secara terpisah untuk perhitungan total.
$allIuran = fetchDataWithPagination($conn, 'iuran', 0, 10000, null, $selectedYear);
$allIuran17 = fetchDataWithPagination($conn, 'iuran17', 0, 10000, null, $selectedYear);

foreach ($allIuran as $transaksi) {
    $totalIuran += $transaksi['jumlah_bayar'];
}
foreach ($allIuran17 as $transaksi) {
    $totalIuran17 += $transaksi['jumlah_bayar'];
}

$anggotaList = fetchDataWithPagination($conn, 'anggota', 0, 10000, null, null);
$current_latitude = -7.527444;
$current_longitude = 110.628819;
$current_tolerance = 50;
$sql_lokasi = "SELECT latitude, longitude, toleransi_jarak, waktu_dibuat, durasi_absensi FROM lokasi_absensi ORDER BY waktu_dibuat DESC LIMIT 1";
$result_lokasi = $conn->query($sql_lokasi);
if ($result_lokasi && $result_lokasi->num_rows > 0) {
    $lokasi_data = $result_lokasi->fetch_assoc();
    $current_latitude = $lokasi_data['latitude'];
    $current_longitude = $lokasi_data['longitude'];
    $current_tolerance = $lokasi_data['toleransi_jarak'];
    $waktu_dibuat = $lokasi_data['waktu_dibuat'];
    $current_duration = $lokasi_data['durasi_absensi'];
}

// === KONFIGURASI ===
// Sesuaikan path ke file version.json
$localVersionFile = __DIR__ . "/../application/version.json"; 
$remoteUrl = "https://raw.githubusercontent.com/yogamahastya/mngkarangtaruna/main/application/version.json";
$isUpdateAvailable = false;
$remoteVersion = null;

// === CEK VERSION LOCAL ===
$localVersion = null;
if (file_exists($localVersionFile)) {
    $localData = json_decode(file_get_contents($localVersionFile), true);
    $localVersion = $localData['version'] ?? null;
}

// === CEK VERSION REMOTE ===
$remoteData = @file_get_contents($remoteUrl);
if ($remoteData !== false) {
    $remoteData = json_decode($remoteData, true);
    $remoteVersion = $remoteData['version'] ?? null;
    
    // === BANDINGKAN VERSI ===
    if ($localVersion && $remoteVersion && $localVersion !== $remoteVersion) {
        $isUpdateAvailable = true;
    }
}
$profile_image = 'https://img.freepik.com/free-psd/contact-icon-illustration-isolated_23-2151903337.jpg?semt=ais_hybrid&w=740'; // Ganti dengan URL gambar default yang sesuai
?>