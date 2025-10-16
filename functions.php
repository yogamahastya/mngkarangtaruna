<?php
/**
* Mengubah angka menjadi format Rupiah.
* @param int|float $amount Jumlah uang.
* @return string Mengembalikan string format Rupiah.
*/
function formatRupiah($amount) {
    return 'Rp' . number_format($amount, 0, ',', '.');
}

/**
* Menghitung jarak antara dua titik GPS menggunakan Rumus Haversine.
* @param float $lat1 Latitude titik 1.
* @param float $lon1 Longitude titik 1.
* @param float $lat2 Latitude titik 2.
* @param float $lon2 Longitude titik 2.
* @return float Jarak dalam meter.
*/
function haversineGreatCircleDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Jari-jari Bumi dalam meter
    
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin(abs($lonDelta) / 2), 2)));
    return $angle * $earthRadius;
}

/**
 * Mengambil data dengan paginasi dan pencarian.
 *
 * @param mysqli $conn Koneksi database.
 * @param string $tableName Nama tabel.
 * @param int $start Index awal data.
 * @param int $limit Jumlah data per halaman.
 * @param string|null $searchTerm Kata kunci pencarian.
 * @param int|null $filterYear Tahun filter (untuk keuangan/iuran).
 * @return array Data yang sudah difilter dan dipaginasi.
 */
function fetchDataWithPagination($conn, $tableName, $start, $limit, $searchTerm = null, $filterYear = null) {
    $data = [];
    $sql = "";
    $conditions = [];
    $params = [];
    $types = '';
    $orderBy = '';

    if ($tableName === 'keuangan') {
        $sql = "SELECT k.*, a.nama_lengkap AS dicatat_oleh_nama FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
        $orderBy = 'k.tanggal_transaksi DESC';
    } elseif ($tableName === 'iuran' || $tableName === 'iuran17') {
        // ## PERBAIKAN LOGIKA QUERY IURAN DIMULAI ##
        $joinTable = $tableName;
        $joinConditions = "a.id = i.anggota_id"; // Kondisi join dasar

        if ($filterYear) {
            $joinConditions .= " AND YEAR(i.tanggal_bayar) = ?"; // Tambahkan filter tahun ke join
            $params[] = $filterYear;
            $types .= 'i';
        }

        $sql = "SELECT a.id AS anggota_id, a.nama_lengkap, a.bergabung_sejak, COALESCE(SUM(i.jumlah_bayar), 0) AS total_bayar 
                FROM anggota AS a 
                LEFT JOIN {$joinTable} AS i ON {$joinConditions}";
        
        $orderBy = "FIELD(a.jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), a.nama_lengkap ASC";
        // ## PERBAIKAN LOGIKA QUERY IURAN SELESAI ##
    } elseif ($tableName === 'anggota') {
        $sql = "SELECT * FROM `anggota`";
        $orderBy = "FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), nama_lengkap ASC";
    } elseif ($tableName === 'absensi') {
        $sql = "SELECT a.*, ab.tanggal_absen, ab.id as absensi_id FROM anggota a LEFT JOIN absensi ab ON a.id = ab.anggota_id AND DATE(ab.tanggal_absen) = CURDATE()";
        $orderBy = "FIELD(a.jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), a.nama_lengkap ASC";
    } elseif ($tableName === 'kegiatan') {
        $sql = "SELECT * FROM `kegiatan`";
        $orderBy = 'tanggal_mulai DESC';
    }

    if ($filterYear && $tableName === 'keuangan') {
        $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
        // params dan types untuk tahun keuangan sudah ditangani di atas jika ada
        if (!in_array($filterYear, $params)) {
            $params[] = $filterYear;
            $types .= 'i';
        }
    }

    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota') {
            $conditions[] = "(nama_lengkap LIKE ? OR jabatan LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'ss';
        } elseif ($tableName === 'absensi') {
            $conditions[] = "(a.nama_lengkap LIKE ? OR a.jabatan LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'ss';
        } elseif ($tableName === 'kegiatan') {
            $conditions[] = "(nama_lengkap LIKE ? OR deskripsi LIKE ? OR lokasi LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'sss';
        } elseif ($tableName === 'keuangan') {
            $conditions[] = "(k.jenis_transaksi LIKE ? OR k.deskripsi LIKE ? OR a.nama_lengkap LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'sss';
        } elseif ($tableName === 'iuran' || $tableName === 'iuran17') {
            $conditions[] = "(a.nama_lengkap LIKE ?)"; // Kondisi search untuk WHERE clause
            $params[] = $searchTermLike;
            $types .= 's';
        }
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    if ($tableName === 'iuran' || $tableName === 'iuran17') {
        $sql .= " GROUP BY a.id, a.nama_lengkap, a.bergabung_sejak";
    }

    $sql .= " ORDER BY $orderBy LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $start;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
    }
    return $data;
}

/**
 * // ... (Fungsi fetchMemberDuesBreakdownWithYear tetap sama, tidak perlu diubah)
 */
function fetchMemberDuesBreakdownWithYear($conn, $anggotaId, $year = null, $duesTableName = 'iuran', $monthlyFee = 0) {
    // ... Tidak ada perubahan di sini
    $memberData = null;
    $duesData = [];
    $sqlMember = "SELECT nama_lengkap, bergabung_sejak FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sqlMember);
    $stmt->bind_param("i", $anggotaId);
    $stmt->execute();
    $resultMember = $stmt->get_result();
    if ($resultMember->num_rows > 0) {
        $memberData = $resultMember->fetch_assoc();
    } else {
        return null;
    }
    $stmt->close();
    
    $sqlDues = "SELECT jumlah_bayar, tanggal_bayar FROM {$duesTableName} WHERE anggota_id = ?";
    $params = [$anggotaId];
    $types = "i";
    
    if ($year !== null && is_numeric($year)) {
        $sqlDues .= " AND YEAR(tanggal_bayar) = ?";
        $params[] = $year;
        $types .= "i";
    }
    $sqlDues .= " ORDER BY tanggal_bayar ASC";

    $stmt = $conn->prepare($sqlDues);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultDues = $stmt->get_result();
    $payments = [];
    while ($row = $resultDues->fetch_assoc()) {
        $payments[] = $row;
    }
    $stmt->close();

    $joinDate = new DateTime($memberData['bergabung_sejak']);
    $today = new DateTime();
    $startYear = ($year !== null) ? $year : intval($joinDate->format('Y'));
    $currentMonth = new DateTime("{$startYear}-01-01");
    if ($currentMonth < $joinDate) {
        $currentMonth = $joinDate;
    }

    $endOfMonthLoop = ($year !== null) ? new DateTime("{$year}-12-31") : $today;
    
    $totalPaid = 0;
    $totalExpected = 0;
    
    while ($currentMonth <= $endOfMonthLoop) {
        if ($year === null && $currentMonth->format('Y-m') > $today->format('Y-m')) {
            break;
        }

        $month = $currentMonth->format('F Y');
        $monthKey = $currentMonth->format('Y-m');
        $paymentForThisMonth = 0;
        $notes = '';

        foreach ($payments as $payment) {
            $paymentDate = new DateTime($payment['tanggal_bayar']);
            if ($paymentDate->format('Y-m') === $monthKey) {
                $paymentForThisMonth += $payment['jumlah_bayar'];
            }
        }

        $status = 'Belum Bayar';
        if ($paymentForThisMonth >= $monthlyFee) {
            $status = 'Lunas';
        } elseif ($paymentForThisMonth > 0) {
            $status = 'Kurang';
            $kekurangan = $monthlyFee - $paymentForThisMonth;
            $notes = 'Kurang ' . formatRupiah($kekurangan);
        }

        $totalPaid += $paymentForThisMonth;
        $totalExpected += $monthlyFee;
        $duesData[] = [
            'month' => $month,
            'paid' => $paymentForThisMonth,
            'status' => $status,
            'notes' => $notes
        ];
        $currentMonth->modify('+1 month');
    }

    $kekurangan = $totalExpected - $totalPaid;
    return [
        'member' => $memberData,
        'breakdown' => $duesData,
        'summary' => [
            'total_paid' => $totalPaid,
            'total_expected' => $totalExpected,
            'shortfall' => $kekurangan
        ]
    ];
}

/**
 * // ... (Fungsi getPaymentStatus tetap sama, tidak perlu diubah)
 */
function getPaymentStatus($totalPaid, $totalExpected) {
    if ($totalExpected <= 0 && $totalPaid <= 0) {
        return ['status' => 'Tidak Ada Tagihan', 'class' => 'bg-secondary'];
    }
    if ($totalPaid >= $totalExpected) {
        return ['status' => 'Lunas', 'class' => 'bg-success'];
    } elseif ($totalPaid > 0) {
        return ['status' => 'Kurang', 'class' => 'bg-warning'];
    } else {
        return ['status' => 'Belum Bayar', 'class' => 'bg-danger'];
    }
}


/**
 * Menghitung total baris untuk paginasi.
 * @param mysqli $conn Koneksi database.
 * @param string $tableName Nama tabel.
 * @param string|null $searchTerm Kata kunci pencarian.
 * @param int|null $filterYear Tahun filter.
 * @return int Total baris.
 */
function countRowsWithFilter($conn, $tableName, $searchTerm = null, $filterYear = null) {
    $sql = "";
    $conditions = [];
    $params = [];
    $types = '';

    // Tentukan query dasar
    if ($tableName === 'keuangan') {
        $sql = "SELECT COUNT(*) AS total FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
    } elseif ($tableName === 'iuran' || $tableName === 'iuran17' || $tableName === 'absensi' || $tableName === 'anggota') {
        $sql = "SELECT COUNT(*) AS total FROM `anggota` a"; // Hitung dari tabel anggota
    } else {
        $sql = "SELECT COUNT(*) AS total FROM `$tableName`";
    }

    // Tambahkan kondisi filter tahun
    if ($filterYear && $tableName === 'keuangan') {
        $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
        $params[] = $filterYear;
        $types .= 'i';
    }

    // ## PERBAIKAN LOGIKA PENCARIAN DIMULAI ##
    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota' || $tableName === 'absensi' || $tableName === 'iuran' || $tableName === 'iuran17') {
            // Logika pencarian nama untuk semua tab yang berbasis tabel anggota
            $conditions[] = "a.nama_lengkap LIKE ?";
            $params[] = $searchTermLike;
            $types .= 's';
        } elseif ($tableName === 'kegiatan') {
            $conditions[] = "(nama_kegiatan LIKE ? OR deskripsi LIKE ? OR lokasi LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'sss';
        } elseif ($tableName === 'keuangan') {
            $conditions[] = "(k.jenis_transaksi LIKE ? OR k.deskripsi LIKE ? OR a.nama_lengkap LIKE ?)";
            $params[] = $searchTermLike; $params[] = $searchTermLike; $params[] = $searchTermLike; $types .= 'sss';
        }
    }
    // ## PERBAIKAN LOGIKA PENCARIAN SELESAI ##

    // Gabungkan semua kondisi ke query
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
    return 0;
}


/**
 * // ... (Fungsi fetchMemberAttendanceBreakdownWithYear tetap sama, tidak perlu diubah)
 */
function fetchMemberAttendanceBreakdownWithYear($conn, $memberId, $year) {
    // ... Tidak ada perubahan di sini
    $data = null;
    $stmtMember = $conn->prepare("SELECT nama_lengkap, bergabung_sejak FROM anggota WHERE id = ?");
    if ($stmtMember) {
        $stmtMember->bind_param("i", $memberId);
        $stmtMember->execute();
        $resultMember = $stmtMember->get_result();
        if ($member = $resultMember->fetch_assoc()) {
            $data['member'] = $member;
            
            $stmtAttendance = $conn->prepare("SELECT tanggal_absen FROM absensi WHERE anggota_id = ? AND YEAR(tanggal_absen) = ? ORDER BY tanggal_absen DESC");
            if ($stmtAttendance) {
                $stmtAttendance->bind_param("ii", $memberId, $year);
                $stmtAttendance->execute();
                $resultAttendance = $stmtAttendance->get_result();
                $attendanceList = [];
                while ($row = $resultAttendance->fetch_assoc()) {
                    $attendanceList[] = $row;
                }
                $data['breakdown'] = $attendanceList;
                $stmtAttendance->close();
            }
        }
        $stmtMember->close();
    }
    return $data;
}
// UNUTK FORMAT TANGGAL

function formatTanggalIndo($tanggal) {
    // Cek jika tanggal kosong
    if (empty($tanggal) || $tanggal == '0000-00-00') {
        return '-';
    }
    
    $bulan = array (
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    
    // Validasi format tanggal
    if (count($pecahkan) == 3) {
        return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
    
    return $tanggal;
}

// Fungsi untuk format bulan tahun saja (opsional)
function formatBulanTahun($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00') {
        return '-';
    }
    
    $bulan = array (
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', $tanggal);
    
    if (count($pecahkan) == 3) {
        return $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
    
    return $tanggal;
}
?>