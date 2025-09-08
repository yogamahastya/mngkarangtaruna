    // =========================================================
// Fungsionalitas untuk modal Iuran (yang sudah ada)
// =========================================================

document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk mendapatkan tanggal hari ini dalam format YYYY-MM-DD
    function getTodayDate() {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    // Set nilai input tanggal pada modal iuran saat modal dibuka
    const addIuranModal = document.getElementById('addIuranModal');
    if (addIuranModal) {
        addIuranModal.addEventListener('show.bs.modal', function () {
            document.getElementById('add-tanggal-bayar').value = getTodayDate();
        });
    }

    // =========================================================
    // Fungsionalitas untuk modal Iuran 17 (kode yang ditambahkan)
    // =========================================================

    // Set nilai input tanggal pada modal iuran17 saat modal dibuka
    const addIuran17Modal = document.getElementById('addIuran17Modal');
    if (addIuran17Modal) {
        addIuran17Modal.addEventListener('show.bs.modal', function () {
            document.getElementById('add-tanggal-bayar17').value = getTodayDate();
        });
    }
});

//UPDATE APP
document.addEventListener('DOMContentLoaded', function() {
    const updateButton = document.getElementById('update-button');
    const updateToken = 'nxr232597';
    
    // Cari elemen alert yang sudah ada di halaman
    const notificationAlert = document.querySelector('.alert');

    if (updateButton && notificationAlert) {
        updateButton.addEventListener('click', function(event) {
            event.preventDefault();

            // Ubah tampilan notifikasi yang ada menjadi "memproses"
            notificationAlert.classList.remove('alert-warning');
            notificationAlert.classList.add('alert-info');
            notificationAlert.innerHTML = `
                <i class="fas fa-spinner fa-spin me-2"></i>
                <div class="me-auto">Sedang memperbarui... Mohon tunggu.</div>
            `;
            
            // Hapus tombol tutup
            const closeButton = notificationAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.remove();
            }

            // Kirim permintaan pembaruan ke server
            // Jalur yang benar adalah '../application/update_action.php'
            fetch(`../application/update_action.php?token=${updateToken}`)
                .then(response => response.json())
                .then(data => {
                    let alertType = '';
                    let alertIcon = '';
                    let alertMessage = '';
                    
                    if (data.status === 'success') {
                        alertType = 'alert-success';
                        alertIcon = 'fas fa-check-circle';
                        alertMessage = `Pembaruan berhasil! Sudah versi ${data.to}.`;
                    } else if (data.status === 'info') {
                        alertType = 'alert-info';
                        alertIcon = 'fas fa-info-circle';
                        alertMessage = `Sudah versi terbaru!`;
                    } else {
                        alertType = 'alert-danger';
                        alertIcon = 'fas fa-times-circle';
                        alertMessage = `Pembaruan gagal. Silakan periksa log.`;
                    }
                    
                    // Ubah kelas dan isi notifikasi yang ada
                    notificationAlert.classList.remove('alert-info', 'alert-warning', 'alert-success', 'alert-danger');
                    notificationAlert.classList.add(alertType);
                    notificationAlert.innerHTML = `
                        <i class="${alertIcon} me-2"></i>
                        <div class="me-auto">${alertMessage}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                })
                .catch(error => {
                    console.error('Ada masalah dengan permintaan fetch:', error);
                    // Tampilkan notifikasi error
                    notificationAlert.classList.remove('alert-info', 'alert-warning', 'alert-success');
                    notificationAlert.classList.add('alert-danger');
                    notificationAlert.innerHTML = `
                        <i class="fas fa-times-circle me-2"></i>
                        <div class="me-auto">Gagal menghubungi server pembaruan.</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                });
        });
    }
});

// BAGIAN EDIT KEGIATAN

document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editKegiatanModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Tombol yang diklik

        // Ambil data dari tombol
        var id        = button.getAttribute('data-id');
        var nama      = button.getAttribute('data-nama');
        var lokasi    = button.getAttribute('data-lokasi');
        var deskripsi = button.getAttribute('data-deskripsi');
        var notulen = button.getAttribute('data-notulen');
        var tanggal   = button.getAttribute('data-tanggal');

        // Isi ke dalam form di modal
        editModal.querySelector('#edit-kegiatan-id').value   = id;
        editModal.querySelector('#edit-nama-kegiatan').value = nama;
        editModal.querySelector('#edit-lokasi').value        = lokasi;
        editModal.querySelector('#edit-deskripsi').value     = deskripsi;
        editModal.querySelector('#edit-notulen').value       = notulen;
        editModal.querySelector('#edit-tanggal-mulai').value = tanggal;
    });
});




//BAGIAN IURAN
document.addEventListener('DOMContentLoaded', function() {
    // Dapatkan modal dan elemen form
    const editIuran17Modal = document.getElementById('editIuran17Modal');
    const form = editIuran17Modal.querySelector('form');

    // Tambahkan event listener saat modal muncul
    editIuran17Modal.addEventListener('show.bs.modal', function (event) {
        // Dapatkan tombol yang memicu modal
        const button = event.relatedTarget; 

        // Ambil data dari atribut data-*
        const id = button.getAttribute('data-id');
        const anggotaId = button.getAttribute('data-anggota-id');
        const tanggal = button.getAttribute('data-tanggal');
        const jumlah = button.getAttribute('data-jumlah');
        const keterangan = button.getAttribute('data-keterangan');

        // Isi form di dalam modal dengan data yang diambil
        document.getElementById('edit-iuran17-id').value = id;
        document.getElementById('edit-anggota-id17').value = anggotaId;
        document.getElementById('edit-tanggal-bayar17').value = tanggal;
        document.getElementById('edit-jumlah-iuran17').value = jumlah;
        document.getElementById('edit-keterangan17').value = keterangan;
    });
});
