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