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
    
    // Mengubah koordinat dari derajat ke radian
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

    // Logika JOIN dan ORDER BY khusus untuk tabel tertentu
    if ($tableName === 'keuangan') {
        $sql = "SELECT k.*, a.nama_lengkap AS dicatat_oleh_nama FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
        $orderBy = 'k.tanggal_transaksi DESC';
    } elseif ($tableName === 'iuran') {
        $sql = "SELECT a.id AS anggota_id, a.nama_lengkap, a.bergabung_sejak, COALESCE(SUM(i.jumlah_bayar), 0) AS total_bayar FROM anggota AS a LEFT JOIN iuran AS i ON a.id = i.anggota_id";
        $orderBy = "FIELD(a.jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), a.nama_lengkap ASC";
    } elseif ($tableName === 'anggota') {
        $sql = "SELECT * FROM `anggota`";
        $orderBy = "FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), nama_lengkap ASC";
    } elseif ($tableName === 'absensi') {
        // Gunakan JOIN untuk absensi agar dapat mencari berdasarkan nama anggota
        $sql = "SELECT a.*, ab.tanggal_absen, ab.id as absensi_id FROM anggota a LEFT JOIN absensi ab ON a.id = ab.anggota_id AND DATE(ab.tanggal_absen) = CURDATE()";
        $orderBy = "FIELD(a.jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), a.nama_lengkap ASC";
    } elseif ($tableName === 'kegiatan') {
        $sql = "SELECT * FROM `kegiatan`";
        $orderBy = 'tanggal_mulai DESC';
    }

    // Kondisi filter tahun
    if ($filterYear) {
        if ($tableName === 'keuangan') {
            $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        } elseif ($tableName === 'iuran') {
            // Re-define SQL untuk iuran dengan filter tahun di LEFT JOIN
            $sql = "SELECT a.id AS anggota_id, a.nama_lengkap, a.bergabung_sejak, COALESCE(SUM(i.jumlah_bayar), 0) AS total_bayar FROM anggota AS a LEFT JOIN iuran AS i ON a.id = i.anggota_id AND YEAR(i.tanggal_bayar) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        }
    }

    // Kondisi pencarian
    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota') {
            $conditions[] = "(nama_lengkap LIKE ? OR jabatan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'absensi') {
            $conditions[] = "(a.nama_lengkap LIKE ? OR a.jabatan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'kegiatan') {
            $conditions[] = "(nama_kegiatan LIKE ? OR deskripsi LIKE ? OR lokasi LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'sss';
        } elseif ($tableName === 'keuangan') {
            $conditions[] = "(k.jenis_transaksi LIKE ? OR k.deskripsi LIKE ? OR a.nama_lengkap LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'sss';
        } elseif ($tableName === 'iuran') {
            $conditions[] = "(a.nama_lengkap LIKE ?)";
            $params[] = $searchTermLike;
            $types .= 's';
        }
    }

    if (!empty($conditions)) {
        if ($tableName === 'iuran' && strpos($sql, 'AND YEAR(i.tanggal_bayar)') !== false) {
            $sql .= " AND " . implode(" AND ", $conditions);
        } elseif (strpos($sql, 'WHERE') === false) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        } else {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
    }
    
    if ($tableName === 'iuran') {
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
 * Mengambil rekapitulasi iuran per anggota secara rinci, dengan filter tahun.
 * @param mysqli $conn Objek koneksi database.
 * @param int $anggotaId ID anggota.
 * @param int|null $year Tahun yang akan difilter.
 * @return array|null Mengembalikan array data rekapitulasi atau null.
 */
function fetchMemberDuesBreakdownWithYear($conn, $anggotaId, $year = null) {
    $monthlyFee = DUES_MONTHLY_FEE; // Menggunakan konstanta dari config.php
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
    
    $sqlDues = "SELECT jumlah_bayar, tanggal_bayar FROM iuran WHERE anggota_id = ?";
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
    $endYear = ($year !== null) ? $year : intval($today->format('Y'));
    $currentMonth = new DateTime("{$startYear}-01-01");
    if ($currentMonth < $joinDate) {
        $currentMonth = $joinDate;
    }

    $endOfMonthLoop = ($year !== null) ? new DateTime("{$year}-12-31") : $today;
    
    $totalPaid = 0;
    $totalExpected = 0;
    
    // Loop hingga akhir tahun yang dipilih atau bulan saat ini (jika tahun saat ini)
    while ($currentMonth <= $endOfMonthLoop) {
        // Menghentikan loop jika bulan saat ini melebihi bulan sekarang pada tahun saat ini
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
 * Fungsi untuk menentukan status pembayaran dan kelas badge.
 * @param int $totalPaid Jumlah yang telah dibayar.
 * @param int $totalExpected Jumlah yang seharusnya dibayar.
 * @return array Mengembalikan array berisi string status dan kelas CSS.
 */
function getPaymentStatus($totalPaid, $totalExpected) {
    if ($totalPaid >= $totalExpected) {
        return ['status' => 'Lunas', 'class' => 'bg-success'];
    } else {
        return ['status' => 'Kurang', 'class' => 'bg-danger'];
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

    if ($tableName === 'keuangan') {
        $sql = "SELECT COUNT(*) AS total FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
    } elseif ($tableName === 'iuran' || $tableName === 'absensi') {
        $sql = "SELECT COUNT(*) AS total FROM anggota";
    } else {
        $sql = "SELECT COUNT(*) AS total FROM `$tableName`";
    }

    if ($filterYear) {
        if ($tableName === 'keuangan') {
            $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        }
    }
    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota' || $tableName === 'absensi') {
            $conditions[] = "(nama_lengkap LIKE ? OR jabatan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'kegiatan') {
            $conditions[] = "(nama_kegiatan LIKE ? OR deskripsi LIKE ? OR lokasi LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'sss';
        } elseif ($tableName === 'keuangan') {
            $conditions[] = "(k.jenis_transaksi LIKE ? OR k.deskripsi LIKE ? OR a.nama_lengkap LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'sss';
        } elseif ($tableName === 'iuran') {
            // Perlu modifikasi khusus untuk COUNT di iuran
            $sql = "SELECT COUNT(*) AS total FROM anggota a WHERE nama_lengkap LIKE ?";
            $params[] = $searchTermLike;
            $types .= 's';
        }
    }
    if (!empty($conditions)) {
        if (strpos($sql, 'WHERE') === false) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        } else {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
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
 * Mengambil riwayat absensi tahunan untuk seorang anggota.
 * @param mysqli $conn Koneksi database.
 * @param int $memberId ID anggota.
 * @param int $year Tahun yang dipilih.
 * @return array|null Mengembalikan array data absensi atau null jika tidak ditemukan.
 */
function fetchMemberAttendanceBreakdownWithYear($conn, $memberId, $year) {
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
?>