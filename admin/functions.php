<?php
function getAnggotaIdFromUserId($conn, $userId) {
    // Komentar: Fungsi ini mengambil ID anggota dari ID pengguna. Tidak ada perubahan.
    $sql = "SELECT anggota_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['anggota_id'];
    }
    return null;
}
// Fungsi baru untuk mendapatkan nama lengkap anggota
function getAnggotaNameById($conn, $anggotaId) {
    // Komentar: Fungsi ini mengambil nama lengkap anggota berdasarkan ID. Tidak ada perubahan.
    $sql = "SELECT nama_lengkap FROM anggota WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $anggotaId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['nama_lengkap'];
    }
    return 'Anggota tidak dikenal'; // Default jika ID tidak ditemukan
}
function getParamTypes($data) {
    // Komentar: Fungsi ini menentukan tipe parameter untuk bind_param. Tidak ada perubahan.
    $types = '';
    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }
    return $types;
}
/**
 * Menangani operasi tambah data dengan penanganan error duplikat.
 */
function handleAdd($conn, $tableName, $data) {
    // === Khusus iuran & iuran17 ===
    if ($tableName === 'iuran' || $tableName === 'iuran17') {
        $data['periode'] = $data['tanggal_bayar'];
        if (!isset($data['keterangan'])) {
            $data['keterangan'] = '';
        }

        // Cek apakah anggota sudah punya iuran di bulan & tahun yang sama
        if (isset($data['anggota_id'], $data['tanggal_bayar'])) {
            $anggotaId = $data['anggota_id'];
            $month = date('m', strtotime($data['tanggal_bayar']));
            $year  = date('Y', strtotime($data['tanggal_bayar']));

            $checkSql = "
                SELECT 1 
                FROM `$tableName`
                WHERE anggota_id = ? 
                  AND MONTH(tanggal_bayar) = ? 
                  AND YEAR(tanggal_bayar) = ?
                LIMIT 1
            ";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("iii", $anggotaId, $month, $year);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $checkStmt->close();
                return 'duplicate_entry'; // langsung keluar
            }
            $checkStmt->close();
        }
    } 
    // === Khusus keuangan ===
    elseif ($tableName === 'keuangan') {
        $anggotaId = getAnggotaIdFromUserId($conn, $_SESSION['user_id']);
        if ($anggotaId) {
            $data['dicatat_oleh_id'] = $anggotaId;
        } else {
            $data['dicatat_oleh_id'] = $_SESSION['user_id'];
        }
    } 
    // === Khusus users ===
    elseif ($tableName === 'users') {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
    }

    // Query insert umum
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), "?"));
    $sql = "INSERT INTO `$tableName` ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }

    $types = getParamTypes($data);
    $params = array_values($data);

    try {
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            return 'duplicate_entry';
        }
        error_log("Execution failed: (" . $e->getCode() . ") " . $e->getMessage());
        return false;
    }

    return $result;
}

function handleEdit($conn, $tableName, $id, $data) {
    // Komentar: Menambahkan logika untuk tabel 'iuran17'.
    // Jika 'tanggal_bayar' ada, salin ke 'periode'.
    if ($tableName === 'iuran' || $tableName === 'iuran17') {
        if (isset($data['tanggal_bayar'])) {
            $data['periode'] = $data['tanggal_bayar'];
        }
    } elseif ($tableName === 'users') {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
    }
    
    $setClause = implode("=?, ", array_keys($data)) . "=?";
    $sql = "UPDATE `$tableName` SET $setClause WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        return false;
    }

    $types = getParamTypes($data);
    $types .= "i";
    $params = array_values($data);
    $params[] = $id;

    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}
function handleDelete($conn, $tableName, $id) {
    // Komentar: Tidak ada perubahan yang diperlukan di sini, karena logika delete
    // sudah umum dan tidak spesifik untuk tabel 'iuran' atau 'iuran17'.
    
    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Hapus data terkait di tabel keuangan terlebih dahulu
        if ($tableName === 'anggota') {
            $sql_keuangan = "DELETE FROM `keuangan` WHERE `dicatat_oleh_id` = ?";
            $stmt_keuangan = $conn->prepare($sql_keuangan);
            $stmt_keuangan->bind_param("i", $id);
            if (!$stmt_keuangan->execute()) {
                throw new Exception("Gagal menghapus catatan keuangan terkait.");
            }
        }

        // Hapus data dari tabel utama (anggota)
        $sql = "DELETE FROM `$tableName` WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Gagal menghapus anggota.");
        }

        // Jika kedua operasi berhasil, lakukan commit transaksi
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Jika ada yang gagal, lakukan rollback transaksi dan kembalikan kesalahan
        $conn->rollback();
        // Anda bisa log error atau menampilkannya untuk debugging
        error_log($e->getMessage());
        return false;
    }
}
function handleUpdateLocation($conn, $latitude, $longitude, $toleransi, $durasi) {
    $sql = "INSERT INTO lokasi_absensi (id, latitude, longitude, toleransi_jarak, durasi_absensi, waktu_dibuat)
            VALUES (1, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            toleransi_jarak = VALUES(toleransi_jarak),
            durasi_absensi = VALUES(durasi_absensi),
            waktu_dibuat = NOW()"; 
    
    $stmt = $conn->prepare($sql);
    
    // Pastikan bind_param sesuai dengan urutan dan tipe data
    // d = double, d = double, i = integer, i = integer
    $stmt->bind_param("ddii", $latitude, $longitude, $toleransi, $durasi);
    
    return $stmt->execute();
}
// =================================================================
// FUNGSI PENGAMBILAN DATA BARU DENGAN PAGINASI
// =================================================================
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
    $sql = "SELECT * FROM `$tableName`";
    $conditions = [];
    $params = [];
    $types = '';
    $orderBy = 'id ASC';

    // Logika JOIN khusus untuk tabel tertentu
    // Komentar: Menambahkan logika JOIN untuk 'iuran17' yang serupa dengan 'iuran'.
    if ($tableName === 'keuangan') {
        $sql = "SELECT k.*, a.nama_lengkap AS dicatat_oleh_nama FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
        $orderBy = 'k.tanggal_transaksi ASC';
    } elseif ($tableName === 'iuran') {
        $sql = "SELECT i.*, a.nama_lengkap AS anggota_nama FROM iuran i LEFT JOIN anggota a ON i.anggota_id = a.id";
        $orderBy = 'i.tanggal_bayar ASC';
    } elseif ($tableName === 'iuran17') {
        $sql = "SELECT i17.*, a.nama_lengkap AS anggota_nama FROM iuran17 i17 LEFT JOIN anggota a ON i17.anggota_id = a.id";
        $orderBy = 'i17.tanggal_bayar ASC';
    } elseif ($tableName === 'anggota') {
        // Logika pengurutan khusus untuk anggota
        $orderBy = "FIELD(jabatan, 'Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Humas', 'Anggota'), nama_lengkap ASC";
    } elseif ($tableName === 'kegiatan') {
        $orderBy = 'tanggal_mulai DESC';
    } elseif ($tableName === 'users') {
        $orderBy = 'id ASC';
    }

    // Kondisi filter tahun
    // Komentar: Menambahkan kondisi filter tahun untuk 'iuran17'.
    if ($filterYear) {
        if ($tableName === 'keuangan') {
            $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        } elseif ($tableName === 'iuran') {
            $conditions[] = "YEAR(i.tanggal_bayar) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        } elseif ($tableName === 'iuran17') {
            $conditions[] = "YEAR(i17.tanggal_bayar) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        }
    }

    // Kondisi pencarian
    // Komentar: Menambahkan kondisi pencarian untuk 'iuran17'.
    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota') {
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
            $conditions[] = "(a.nama_lengkap LIKE ? OR i.keterangan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'iuran17') {
            $conditions[] = "(a.nama_lengkap LIKE ? OR i17.keterangan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'users') {
            $conditions[] = "(username LIKE ? OR role LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        }
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
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
    }
    if ($stmt) {
        $stmt->close();
    }
    return $data;
}
/**
 * Menghitung total baris untuk paginasi.
 *
 * @param mysqli $conn Koneksi database.
 * @param string $tableName Nama tabel.
 * @param string|null $searchTerm Kata kunci pencarian.
 * @param int|null $filterYear Tahun filter.
 * @return int Total baris.
 */
function countRowsWithFilter($conn, $tableName, $searchTerm = null, $filterYear = null) {
    $sql = "SELECT COUNT(*) AS total FROM `$tableName`";
    $conditions = [];
    $params = [];
    $types = '';

    // Logika JOIN khusus untuk tabel tertentu
    // Komentar: Menambahkan logika JOIN untuk 'iuran17' yang serupa dengan 'iuran'.
    if ($tableName === 'keuangan') {
        $sql = "SELECT COUNT(*) AS total FROM keuangan k LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id";
    } elseif ($tableName === 'iuran') {
        $sql = "SELECT COUNT(*) AS total FROM iuran i LEFT JOIN anggota a ON i.anggota_id = a.id";
    } elseif ($tableName === 'iuran17') {
        $sql = "SELECT COUNT(*) AS total FROM iuran17 i17 LEFT JOIN anggota a ON i17.anggota_id = a.id";
    }

    // Kondisi filter tahun
    // Komentar: Menambahkan kondisi filter tahun untuk 'iuran17'.
    if ($filterYear) {
        if ($tableName === 'keuangan') {
            $conditions[] = "YEAR(k.tanggal_transaksi) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        } elseif ($tableName === 'iuran') {
            $conditions[] = "YEAR(i.tanggal_bayar) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        } elseif ($tableName === 'iuran17') {
            $conditions[] = "YEAR(i17.tanggal_bayar) = ?";
            $params[] = $filterYear;
            $types .= 'i';
        }
    }

    // Kondisi pencarian
    // Komentar: Menambahkan kondisi pencarian untuk 'iuran17'.
    if ($searchTerm) {
        $searchTermLike = '%' . $searchTerm . '%';
        if ($tableName === 'anggota') {
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
            $conditions[] = "(a.nama_lengkap LIKE ? OR i.keterangan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'iuran17') {
            $conditions[] = "(a.nama_lengkap LIKE ? OR i17.keterangan LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        } elseif ($tableName === 'users') {
            $conditions[] = "(username LIKE ? OR role LIKE ?)";
            $params[] = $searchTermLike;
            $params[] = $searchTermLike;
            $types .= 'ss';
        }
    }

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