<?php
session_start();

// Redirect pengguna jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ../admin/');
    exit();
}

// =================================================================
// Include Konfigurasi & Koneksi Database
// =================================================================
require_once '../database.php'; // di sini $conn sudah tersedia dari config.php

$login_error = '';

// Memeriksa apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Biometric Login
    if (isset($_POST['biometric_login']) && $_POST['biometric_login'] == '1') {
        $username = trim($_POST['username']);
        
        if (empty($username)) {
            $login_error = "Username tidak boleh kosong.";
        } else {
            // Query untuk cek username
            $sql = "SELECT id, role FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                $login_error = "Error dalam menyiapkan query: " . $conn->error;
            } else {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Set session untuk biometric login
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['biometric_auth'] = true;
                    
                    // Return success (akan dihandle oleh JavaScript)
                    $stmt->close();
                    echo json_encode(['success' => true]);
                    exit();
                } else {
                    $stmt->close();
                    echo json_encode(['success' => false, 'error' => 'Username tidak ditemukan']);
                    exit();
                }
            }
        }
    }
    // Normal Login dengan Username & Password
    else {
        $input_username = trim($_POST['username']);
        $input_password = trim($_POST['password']);

        if (empty($input_username) || empty($input_password)) {
            $login_error = "Username dan password tidak boleh kosong.";
        } else {
            // Query hanya cek username, password diverifikasi di PHP
            $sql = "SELECT id, password, role FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $login_error = "Error dalam menyiapkan query: " . $conn->error;
            } else {
                $stmt->bind_param("s", $input_username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();

                    // 🔒 Verifikasi password
                    // Jika password sudah di-hash gunakan password_verify()
                    // Jika masih plaintext, cek langsung
                    if (password_verify($input_password, $user['password']) || $input_password === $user['password']) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_role'] = $user['role'];

                        header('Location: ../admin/');
                        exit();
                    } else {
                        $login_error = "Password salah.";
                    }
                } else {
                    $login_error = "Username tidak ditemukan.";
                }
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -200px;
            left: -200px;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-circle i {
            font-size: 2rem;
            color: white;
        }

        .login-header h2 {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .login-header p {
            color: #718096;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: block;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            z-index: 10;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 0.9rem 1rem 0.9rem 3.2rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f7fafc;
            width: 100%;
        }

        .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            z-index: 10;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .alert-danger {
            background: #fee;
            color: #c53030;
        }

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #764ba2;
        }

        /* Biometric Authentication */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
            color: #718096;
            font-size: 0.85rem;
        }

        .biometric-options {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .biometric-btn {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .biometric-btn i {
            font-size: 1.5rem;
            color: #667eea;
        }

        .biometric-btn:hover {
            background: white;
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .biometric-btn:active {
            transform: translateY(-1px);
        }

        .biometric-btn.scanning {
            animation: scanning 1.5s ease-in-out infinite;
            border-color: #667eea;
        }

        @keyframes scanning {
            0%, 100% { 
                box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.4);
            }
            50% { 
                box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
            }
        }

        .biometric-status {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #718096;
            min-height: 20px;
        }

        .biometric-status.success {
            color: #48bb78;
        }

        .biometric-status.error {
            color: #f56565;
        }

        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #2d3748;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            margin-bottom: 5px;
        }

        .biometric-btn:hover .tooltip {
            opacity: 1;
        }

        /* Responsive - Tablet */
        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
                background: #667eea;
                background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
                align-items: flex-start;
                min-height: 100vh;
                height: auto;
            }

            body::before,
            body::after {
                display: none;
            }

            .login-container {
                max-width: 100%;
                padding: 10px;
                margin: auto 0;
            }

            .login-card {
                padding: 2rem 1.8rem;
                border-radius: 25px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
                margin-bottom: 20px;
            }

            .login-header {
                margin-bottom: 2rem;
            }

            .login-header h2 {
                font-size: 1.6rem;
            }

            .login-header p {
                font-size: 0.9rem;
            }

            .logo-circle {
                width: 70px;
                height: 70px;
            }

            .logo-circle i {
                font-size: 1.7rem;
            }

            .form-group {
                margin-bottom: 1.3rem;
            }

            .form-control {
                padding: 0.85rem 1rem 0.85rem 3rem;
                font-size: 0.9rem;
            }

            .input-icon {
                left: 15px;
                font-size: 1rem;
            }

            .password-toggle {
                right: 15px;
            }

            .btn-login {
                padding: 0.9rem;
                font-size: 0.95rem;
            }

            .forgot-password {
                margin-top: 1.2rem;
            }

            .forgot-password a {
                font-size: 0.85rem;
            }

            body::before {
                width: 350px;
                height: 350px;
                top: -175px;
                right: -175px;
            }

            body::after {
                width: 300px;
                height: 300px;
                bottom: -150px;
                left: -150px;
            }
        }

        /* Responsive - Mobile */
        @media (max-width: 480px) {
            body {
                padding: 15px 10px;
                align-items: flex-start;
                min-height: 100vh;
                height: auto;
            }

            .login-container {
                padding: 0;
                width: 100%;
                margin: 0;
            }

            .login-card {
                padding: 1.8rem 1.5rem;
                border-radius: 20px;
                width: 100%;
                margin-bottom: 15px;
            }

            .login-header {
                margin-bottom: 1.8rem;
            }

            .login-header h2 {
                font-size: 1.4rem;
            }

            .login-header p {
                font-size: 0.85rem;
            }

            .logo-circle {
                width: 65px;
                height: 65px;
                margin-bottom: 1.2rem;
            }

            .logo-circle i {
                font-size: 1.5rem;
            }

            .form-label {
                font-size: 0.85rem;
            }

            .form-control {
                padding: 0.8rem 1rem 0.8rem 2.8rem;
                font-size: 0.875rem;
                border-radius: 12px;
            }

            .input-icon {
                left: 12px;
                font-size: 0.95rem;
            }

            .password-toggle {
                right: 12px;
                font-size: 0.95rem;
            }

            .btn-login {
                padding: 0.85rem;
                font-size: 0.9rem;
                border-radius: 12px;
            }

            .divider {
                margin: 1.2rem 0;
            }

            .biometric-options {
                gap: 0.8rem;
            }

            .biometric-btn {
                width: 55px;
                height: 55px;
            }

            .biometric-btn i {
                font-size: 1.3rem;
            }

            body::before,
            body::after {
                display: none;
            }

            .alert {
                padding: 0.9rem 1rem;
                font-size: 0.85rem;
            }
        }

        /* Responsive - Extra Small Mobile */
        @media (max-width: 360px) {
            body {
                padding: 10px;
                padding-top: 20px;
            }

            .login-card {
                padding: 1.5rem 1.2rem;
            }

            .login-header h2 {
                font-size: 1.3rem;
            }

            .logo-circle {
                width: 60px;
                height: 60px;
            }

            .logo-circle i {
                font-size: 1.4rem;
            }
        }

        /* Landscape mode untuk mobile */
        @media (max-height: 600px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: flex-start;
            }

            .login-card {
                padding: 1.5rem 2rem;
                max-height: none;
                overflow-y: visible;
                margin-bottom: 10px;
            }

            .login-header {
                margin-bottom: 1rem;
            }

            .logo-circle {
                width: 50px;
                height: 50px;
                margin-bottom: 0.8rem;
            }

            .logo-circle i {
                font-size: 1.2rem;
            }

            .login-header h2 {
                font-size: 1.2rem;
                margin-bottom: 0.2rem;
            }

            .login-header p {
                font-size: 0.75rem;
            }

            .form-group {
                margin-bottom: 0.8rem;
            }

            .form-control {
                padding: 0.7rem 1rem 0.7rem 2.8rem;
            }

            .btn-login {
                padding: 0.7rem;
                margin-top: 0.5rem;
            }

            .divider {
                margin: 1rem 0;
            }

            .biometric-btn {
                width: 45px;
                height: 45px;
            }

            .biometric-btn i {
                font-size: 1.1rem;
            }

            .forgot-password {
                margin-top: 0.8rem;
            }

            .biometric-status {
                margin-top: 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* Touch device optimization */
        @media (hover: none) and (pointer: coarse) {
            .form-control,
            .btn-login,
            .password-toggle {
                -webkit-tap-highlight-color: transparent;
            }

            .btn-login:active {
                transform: scale(0.98);
            }

            .password-toggle {
                padding: 10px;
                margin-right: -10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2>Selamat Datang</h2>
                <p>Silakan login untuk melanjutkan</p>
            </div>
            
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?= htmlspecialchars($login_error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        <i class="fa-solid fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Login
                </button>
            </form>

            <div class="forgot-password">
                <a href="#"><i class="fa-solid fa-question-circle me-1"></i>Lupa Password?</a>
            </div>

            <div class="divider">
                <span>atau login dengan</span>
            </div>

            <div class="biometric-options">
                <button type="button" class="biometric-btn" id="faceIdBtn" title="Face ID">
                    <i class="fa-solid fa-face-smile"></i>
                    <span class="tooltip">Face ID</span>
                </button>
                <button type="button" class="biometric-btn" id="fingerprintBtn" title="Fingerprint">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span class="tooltip">Fingerprint</span>
                </button>
            </div>

            <div class="biometric-status" id="biometricStatus"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Add smooth focus animation
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Biometric Authentication
        const faceIdBtn = document.getElementById('faceIdBtn');
        const fingerprintBtn = document.getElementById('fingerprintBtn');
        const biometricStatus = document.getElementById('biometricStatus');

        // Check if WebAuthn is supported and available
        let isBiometricSupported = false;
        
        async function checkBiometricSupport() {
            if (window.PublicKeyCredential) {
                try {
                    // Check if platform authenticator is available
                    const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
                    isBiometricSupported = available;
                    
                    if (!available) {
                        biometricStatus.textContent = 'Perangkat tidak memiliki biometric (Face ID/Fingerprint)';
                        biometricStatus.className = 'biometric-status error';
                        disableBiometricButtons();
                    } else {
                        // Check if running on HTTPS or localhost
                        const isSecure = window.location.protocol === 'https:' || 
                                       window.location.hostname === 'localhost' ||
                                       window.location.hostname === '127.0.0.1';
                        
                        if (!isSecure) {
                            biometricStatus.textContent = '⚠️ Biometric hanya bekerja di HTTPS';
                            biometricStatus.className = 'biometric-status error';
                            disableBiometricButtons();
                        } else {
                            biometricStatus.textContent = '';
                        }
                    }
                } catch (error) {
                    console.error('Error checking biometric support:', error);
                    biometricStatus.textContent = 'Tidak dapat memeriksa dukungan biometric';
                    biometricStatus.className = 'biometric-status error';
                    disableBiometricButtons();
                }
            } else {
                biometricStatus.textContent = 'Browser tidak mendukung WebAuthn (gunakan Chrome/Safari terbaru)';
                biometricStatus.className = 'biometric-status error';
                disableBiometricButtons();
            }
        }

        function disableBiometricButtons() {
            faceIdBtn.disabled = true;
            fingerprintBtn.disabled = true;
            faceIdBtn.style.opacity = '0.5';
            fingerprintBtn.style.opacity = '0.5';
            faceIdBtn.style.cursor = 'not-allowed';
            fingerprintBtn.style.cursor = 'not-allowed';
        }

        // Initialize biometric check
        checkBiometricSupport();

        // Face ID Authentication
        faceIdBtn.addEventListener('click', async function() {
            if (!isBiometricSupported) {
                biometricStatus.textContent = '❌ Biometric tidak tersedia di perangkat ini';
                biometricStatus.className = 'biometric-status error';
                return;
            }
            await authenticateWithBiometric('face');
        });

        // Fingerprint Authentication
        fingerprintBtn.addEventListener('click', async function() {
            if (!isBiometricSupported) {
                biometricStatus.textContent = '❌ Biometric tidak tersedia di perangkat ini';
                biometricStatus.className = 'biometric-status error';
                return;
            }
            await authenticateWithBiometric('fingerprint');
        });

        // WebAuthn Biometric Authentication
        async function authenticateWithBiometric(type) {
            const button = type === 'face' ? faceIdBtn : fingerprintBtn;
            const statusText = type === 'face' ? '🔍 Memindai wajah...' : '👆 Letakkan jari Anda...';

            try {
                button.classList.add('scanning');
                biometricStatus.textContent = statusText;
                biometricStatus.className = 'biometric-status';

                // Check if user already has credentials registered
                const storedCredentials = localStorage.getItem('biometric_credentials');
                
                if (!storedCredentials) {
                    // First time - Register biometric
                    await registerBiometric(type);
                } else {
                    // Authenticate with existing credentials
                    await authenticateExisting();
                }

            } catch (error) {
                button.classList.remove('scanning');
                console.error('Biometric authentication error:', error);
                
                if (error.name === 'NotAllowedError') {
                    biometricStatus.textContent = '❌ Autentikasi dibatalkan atau ditolak';
                } else if (error.name === 'NotSupportedError') {
                    biometricStatus.textContent = '❌ Perangkat tidak mendukung biometric';
                } else {
                    biometricStatus.textContent = '❌ Autentikasi gagal: ' + error.message;
                }
                biometricStatus.className = 'biometric-status error';
            }
        }

        // Register new biometric credential
        async function registerBiometric(type) {
            try {
                // Generate challenge (dalam production, ini harus dari server)
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                // Get username from form or prompt
                const username = document.getElementById('username').value || prompt('Masukkan username untuk registrasi biometric:');
                if (!username) {
                    throw new Error('Username diperlukan untuk registrasi biometric');
                }

                // Request credential creation
                const credential = await navigator.credentials.create({
                    publicKey: {
                        challenge: challenge,
                        rp: {
                            name: "Dashboard Admin",
                            id: window.location.hostname
                        },
                        user: {
                            id: new Uint8Array(16),
                            name: username,
                            displayName: username
                        },
                        pubKeyCredParams: [
                            { alg: -7, type: "public-key" },  // ES256
                            { alg: -257, type: "public-key" } // RS256
                        ],
                        authenticatorSelection: {
                            authenticatorAttachment: "platform", // Use platform authenticator (biometric)
                            userVerification: "required",
                            requireResidentKey: false
                        },
                        timeout: 60000,
                        attestation: "none"
                    }
                });

                if (credential) {
                    // Save credential with username
                    const credentialData = {
                        id: credential.id,
                        rawId: arrayBufferToBase64(credential.rawId),
                        type: credential.type,
                        username: username,
                        registered: new Date().toISOString()
                    };
                    
                    localStorage.setItem('biometric_credentials', JSON.stringify(credentialData));

                    faceIdBtn.classList.remove('scanning');
                    fingerprintBtn.classList.remove('scanning');
                    biometricStatus.textContent = '✅ Biometric berhasil didaftarkan!';
                    biometricStatus.className = 'biometric-status success';

                    // Login with biometric after registration
                    setTimeout(async () => {
                        await loginWithBiometric(username);
                    }, 1000);
                }
            } catch (error) {
                throw error;
            }
        }

        // Authenticate with existing credential
        async function authenticateExisting() {
            try {
                const storedCreds = JSON.parse(localStorage.getItem('biometric_credentials'));
                
                // Generate challenge
                const challenge = new Uint8Array(32);
                window.crypto.getRandomValues(challenge);

                // Request authentication
                const assertion = await navigator.credentials.get({
                    publicKey: {
                        challenge: challenge,
                        allowCredentials: [{
                            id: base64ToArrayBuffer(storedCreds.rawId),
                            type: 'public-key',
                            transports: ['internal']
                        }],
                        timeout: 60000,
                        userVerification: "required"
                    }
                });

                if (assertion) {
                    faceIdBtn.classList.remove('scanning');
                    fingerprintBtn.classList.remove('scanning');
                    biometricStatus.textContent = '✅ Verifikasi berhasil!';
                    biometricStatus.className = 'biometric-status success';

                    // Login with stored username
                    setTimeout(async () => {
                        await loginWithBiometric(storedCreds.username);
                    }, 500);
                }
            } catch (error) {
                throw error;
            }
        }

        // Login to backend with biometric authentication
        async function loginWithBiometric(username) {
            try {
                biometricStatus.textContent = '🔄 Menghubungi server...';
                biometricStatus.className = 'biometric-status';

                // Create form data
                const formData = new FormData();
                formData.append('biometric_login', '1');
                formData.append('username', username);

                // Send to PHP backend
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    
                    if (result.success) {
                        biometricStatus.textContent = '🎉 Login berhasil!';
                        biometricStatus.className = 'biometric-status success';
                        setTimeout(() => {
                            window.location.href = '../admin/';
                        }, 500);
                    } else {
                        biometricStatus.textContent = '❌ ' + (result.error || 'Login gagal');
                        biometricStatus.className = 'biometric-status error';
                    }
                } else {
                    // If not JSON, login successful and page will redirect
                    biometricStatus.textContent = '🎉 Login berhasil!';
                    biometricStatus.className = 'biometric-status success';
                    setTimeout(() => {
                        window.location.href = '../admin/';
                    }, 500);
                }
            } catch (error) {
                console.error('Login error:', error);
                biometricStatus.textContent = '❌ Gagal login: ' + error.message;
                biometricStatus.className = 'biometric-status error';
            }
        }

        // Helper functions
        function arrayBufferToBase64(buffer) {
            const binary = String.fromCharCode.apply(null, new Uint8Array(buffer));
            return window.btoa(binary);
        }

        function base64ToArrayBuffer(base64) {
            const binary = window.atob(base64);
            const bytes = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                bytes[i] = binary.charCodeAt(i);
            }
            return bytes.buffer;
        }

        // Check if biometric is already registered
        window.addEventListener('load', function() {
            const storedCreds = localStorage.getItem('biometric_credentials');
            if (storedCreds && isBiometricSupported) {
                biometricStatus.textContent = '✓ Biometric sudah terdaftar';
                biometricStatus.className = 'biometric-status success';
            }
        });
    </script>
</body>
</html>