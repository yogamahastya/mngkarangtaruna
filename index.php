<?php
// === HANDLE AJAX REQUEST UNTUK UPDATE ONLINE COUNT ===
if (isset($_GET['ajax_update_online'])) {
    header('Content-Type: application/json');
    
    if (!session_id()) {
        session_start();
    }
    
    $file = "online_users.txt";
    $lockFile = "online_users.lock";
    $user_identifier = session_id() . '_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    
    $fp = fopen($lockFile, 'w');
    if (flock($fp, LOCK_EX)) {
        $online_users = [];
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $online_users = json_decode($data, true);
            if (!is_array($online_users)) {
                $online_users = [];
            }
        }
        
        $current_time = time();
        foreach ($online_users as $identifier => $last_time) {
            if ($current_time - $last_time > 30) {
                unset($online_users[$identifier]);
            }
        }
        
        $online_users[$user_identifier] = $current_time;
        file_put_contents($file, json_encode($online_users));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    
    echo json_encode([
        'count' => count($online_users),
        'status' => 'success'
    ]);
    exit;
}

require_once 'process_data.php';

// Logika tab & tahun aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'anggota';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// === LOGIKA USER ONLINE ===
if (!session_id()) {
    session_start();
}

$file = "online_users.txt";
$lockFile = "online_users.lock";

$user_identifier = session_id() . '_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

$fp = fopen($lockFile, 'w');
if (flock($fp, LOCK_EX)) {
    $online_users = [];
    if (file_exists($file)) {
        $data = file_get_contents($file);
        $online_users = json_decode($data, true);
        if (!is_array($online_users)) {
            $online_users = [];
        }
    }

    $current_time = time();
    
    foreach ($online_users as $identifier => $last_time) {
        if ($current_time - $last_time > 30) {
            unset($online_users[$identifier]);
        }
    }

    $online_users[$user_identifier] = $current_time;
    file_put_contents($file, json_encode($online_users));
    
    flock($fp, LOCK_UN);
}
fclose($fp);

$online_count = count($online_users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= ORGANIZATION_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* === GLOBAL BODY STYLES === */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f0f9ff;
    padding: 0.5rem 1rem;
}

/* Padding khusus untuk mobile agar tidak mepet */
@media (max-width: 767.98px) {
    body {
        padding: 0.75rem 0.75rem;
    }
}

@media (max-width: 575.98px) {
    body {
        padding: 1rem 0.75rem;
    }
}

/* === HEADER MODERN DESIGN === */
header {
    position: sticky;
    top: 0.5rem;
    z-index: 1030;
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(14, 165, 233, 0.12);
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(14, 165, 233, 0.1);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

/* Mobile header adjustment */
@media (max-width: 767.98px) {
    header {
        top: 0.75rem;
        padding: 1rem 1.2rem;
        border-radius: 1.2rem;
    }
}

@media (max-width: 575.98px) {
    header {
        top: 1rem;
        padding: 1rem;
        border-radius: 1rem;
        margin-bottom: 1rem;
    }
}

header:hover {
    box-shadow: 0 12px 40px rgba(14, 165, 233, 0.18);
    transform: translateY(-1px);
}

/* Logo dengan efek glassmorphism */
.logo-icon {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #10b981 100%);
    color: white;
    width: 3.5rem;
    height: 3.5rem;
    font-weight: 800;
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(14, 165, 233, 0.35),
                inset 0 -2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.logo-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, 
        transparent 30%, 
        rgba(255, 255, 255, 0.3) 50%, 
        transparent 70%);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.logo-icon:hover {
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 12px 32px rgba(14, 165, 233, 0.45);
}

/* Header Title dengan typography yang lebih baik */
.header-title h1 {
    font-size: 1.35rem;
    margin: 0;
    font-weight: 700;
    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
    line-height: 1.3;
}

.header-title p {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
    margin-top: 0.2rem;
    font-weight: 500;
    letter-spacing: 0.01em;
}

/* Online Status dengan design premium */
.online-status {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    padding: 0.65rem 1.1rem;
    border-radius: 2.5rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.95rem;
    border: 2px solid #6ee7b7;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.online-status::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.4), 
        transparent);
    transition: left 0.5s;
}

.online-status:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 24px rgba(16, 185, 129, 0.35),
                inset 0 1px 2px rgba(255, 255, 255, 0.8);
    border-color: #34d399;
}

.online-status:hover::before {
    left: 100%;
}

/* Online Icon dengan animasi yang lebih smooth */
.online-icon {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.85rem;
    animation: pulse-icon 2.5s ease-in-out infinite;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4),
                inset 0 -2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
}

.online-icon::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid #ecfdf5;
    animation: blink 1.5s infinite;
}

@keyframes pulse-icon {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    50% { 
        transform: scale(1.15);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
    }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* Online Count dengan efek gradient */
.online-count {
    font-weight: 800;
    font-size: 1.2rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.online-label {
    font-size: 0.75rem;
    color: #047857;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Menu Toggle dengan efek modern */
.menu-toggle {
    border: none;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    font-size: 1.4rem;
    color: #0369a1;
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
    cursor: pointer;
}

.menu-toggle:hover {
    background: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 100%);
    transform: scale(1.08) rotate(90deg);
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.25);
}

.menu-toggle:active {
    transform: scale(0.95) rotate(90deg);
}

/* Responsive Design */
@media (max-width: 767.98px) {
    .logo-icon {
        width: 3.2rem;
        height: 3.2rem;
        font-size: 1.3rem;
        border-radius: 1.1rem;
    }
    
    .header-title h1 {
        font-size: 1.15rem;
    }
    
    .header-title p {
        font-size: 0.75rem;
    }
    
    .online-status {
        padding: 0.55rem 0.9rem;
        font-size: 0.9rem;
    }
    
    .online-icon {
        width: 26px;
        height: 26px;
        font-size: 0.8rem;
    }
    
    .online-count {
        font-size: 1.1rem;
    }
    
    .menu-toggle {
        width: 2.8rem;
        height: 2.8rem;
        font-size: 1.3rem;
    }
}

@media (max-width: 575.98px) {
    .logo-icon {
        width: 3rem;
        height: 3rem;
        font-size: 1.2rem;
        border-radius: 1rem;
    }
    
    .header-title h1 {
        font-size: 1.05rem;
    }
    
    .header-title p {
        font-size: 0.7rem;
    }
    
    .online-status {
        font-size: 0.85rem;
        padding: 0.5rem 0.85rem;
    }
    
    .online-icon {
        width: 24px;
        height: 24px;
        font-size: 0.75rem;
    }
    
    .online-icon::after {
        width: 8px;
        height: 8px;
    }
    
    .online-count {
        font-size: 1.05rem;
    }
    
    .menu-toggle {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1.2rem;
    }
}

@media (max-width: 767.98px) {
    .online-label {
        display: none;
    }
}

/* Tambahan: Efek saat scroll */
header.scrolled {
    padding: 0.9rem 1.5rem;
    box-shadow: 0 4px 24px rgba(14, 165, 233, 0.15);
}

@media (max-width: 767.98px) {
    header.scrolled {
        padding: 0.85rem 1.2rem;
    }
}

@media (max-width: 575.98px) {
    header.scrolled {
        padding: 0.8rem 1rem;
    }
}

/* === SIDEBAR DESKTOP === */
@media (max-width: 991.98px) {
    .sidebar-desktop {
        display: none;
    }
}

@media (min-width: 992px) {
    .offcanvas {
        display: none !important;
    }
}

.sidebar-desktop .bg-white {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%) !important;
    padding: 1.5rem;
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(14, 165, 233, 0.08);
    position: sticky;
    top: 6rem;
    transition: all 0.3s ease;
}

.sidebar-desktop .bg-white:hover {
    box-shadow: 0 12px 40px rgba(14, 165, 233, 0.15);
    transform: translateY(-2px);
}

/* === NAVIGATION PILLS CUSTOM === */
.nav-pills-custom {
    gap: 0.5rem;
}

.nav-pills-custom .nav-link {
    border-radius: 1rem;
    padding: 0.9rem 1.2rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    font-weight: 500;
    font-size: 0.95rem;
    color: #475569;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.nav-pills-custom .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(14, 165, 233, 0.1), transparent);
    transition: left 0.5s;
}

.nav-pills-custom .nav-link:hover::before {
    left: 100%;
}

.nav-pills-custom .nav-link i {
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.nav-pills-custom .nav-link:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    color: #0369a1;
    transform: translateX(5px);
    border-color: rgba(14, 165, 233, 0.2);
}

.nav-pills-custom .nav-link:hover i {
    transform: scale(1.15) rotate(5deg);
    color: #0ea5e9;
}

.nav-pills-custom .nav-link.active {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
    color: white !important;
    font-weight: 600;
    box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35),
                inset 0 -2px 6px rgba(0, 0, 0, 0.15);
    border-color: transparent;
    transform: translateX(0);
}

.nav-pills-custom .nav-link.active i {
    transform: scale(1.1);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.nav-pills-custom .nav-link.active::after {
    content: '';
    position: absolute;
    right: 1rem;
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.3); }
}

/* === STAT CARD SIDEBAR === */
.sidebar-desktop .stat-card,
.offcanvas-body .stat-card {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #10b981 100%);
    color: white;
    padding: 1.8rem 1.5rem;
    border-radius: 1.5rem;
    box-shadow: 0 8px 24px rgba(14, 165, 233, 0.35),
                inset 0 -2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.sidebar-desktop .stat-card::before,
.offcanvas-body .stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    animation: rotate-gradient 10s linear infinite;
}

@keyframes rotate-gradient {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.sidebar-desktop .stat-card:hover,
.offcanvas-body .stat-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 32px rgba(14, 165, 233, 0.45);
}

.sidebar-desktop .stat-card h3,
.offcanvas-body .stat-card h3 {
    font-size: 2.5rem;
    margin: 0;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
}

.sidebar-desktop .stat-card p,
.offcanvas-body .stat-card p {
    margin: 0.5rem 0 0 0;
    font-size: 0.9rem;
    opacity: 0.95;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

.sidebar-desktop .stat-card .icon-wrapper {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* === OFFCANVAS MOBILE === */
.offcanvas {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: none;
    box-shadow: -4px 0 24px rgba(0, 0, 0, 0.1);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.offcanvas.offcanvas-start {
    transform: translateX(-100%);
}

.offcanvas.show {
    transform: translateX(0) !important;
}

.offcanvas-header {
    padding: 1.5rem;
    border-bottom: 2px solid #e0f2fe;
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
}

.offcanvas.show .offcanvas-header {
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.offcanvas-title {
    font-size: 1.3rem;
    font-weight: 700;
    background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.offcanvas-title i {
    background: linear-gradient(135deg, #0ea5e9 0%, #10b981 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.offcanvas-header .btn-close {
    background-color: white;
    border-radius: 50%;
    width: 2.5rem;
    height: 2.5rem;
    opacity: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.offcanvas-header .btn-close:hover {
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.offcanvas-body {
    padding: 1.5rem;
}

/* Mobile Navigation Styling */
.offcanvas-body .nav-pills-custom .nav-link {
    padding: 1rem 1.2rem;
    font-size: 1rem;
}

.offcanvas-body .stat-card {
    margin-top: 1.5rem;
}

/* === DIVIDER === */
.sidebar-desktop .border-top,
.offcanvas-body .border-top {
    border-color: rgba(14, 165, 233, 0.15) !important;
    margin-top: 1.5rem !important;
    padding-top: 1.5rem !important;
}

/* === RESPONSIVE ADJUSTMENTS === */
@media (max-width: 575.98px) {
    .offcanvas {
        width: 85% !important;
    }
    
    .offcanvas-header {
        padding: 1.2rem;
    }
    
    .offcanvas-title {
        font-size: 1.1rem;
    }
    
    .offcanvas-body {
        padding: 1.2rem;
    }
    
    .offcanvas-body .nav-pills-custom .nav-link {
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
    }
    
    .offcanvas-body .stat-card h3 {
        font-size: 2rem;
    }
    
    .offcanvas-body .stat-card p {
        font-size: 0.85rem;
    }
}

/* === ANIMATION ENTER === */
/* Smooth animation dengan cubic-bezier */
.offcanvas.showing {
    transform: translateX(-100%);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.offcanvas.show {
    transform: translateX(0) !important;
}

.offcanvas.hiding {
    transform: translateX(-100%) !important;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.6, 1);
}

/* Animasi konten sidebar saat muncul */
.offcanvas.show .nav-pills-custom .nav-link {
    animation: fadeInUp 0.5s ease-out backwards;
}

.offcanvas.show .nav-pills-custom .nav-link:nth-child(1) { animation-delay: 0.1s; }
.offcanvas.show .nav-pills-custom .nav-link:nth-child(2) { animation-delay: 0.15s; }
.offcanvas.show .nav-pills-custom .nav-link:nth-child(3) { animation-delay: 0.2s; }
.offcanvas.show .nav-pills-custom .nav-link:nth-child(4) { animation-delay: 0.25s; }
.offcanvas.show .nav-pills-custom .nav-link:nth-child(5) { animation-delay: 0.3s; }

.offcanvas.show .stat-card {
    animation: fadeInUp 0.6s ease-out 0.35s backwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* === BACKDROP CUSTOM === */
.offcanvas-backdrop {
    backdrop-filter: blur(8px);
    background-color: rgba(15, 23, 42, 0.6);
    transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.offcanvas-backdrop.show {
    opacity: 1 !important;
}

.offcanvas-backdrop.fade {
    opacity: 0;
}

/* === SCROLLBAR STYLING === */
.sidebar-desktop .bg-white::-webkit-scrollbar,
.offcanvas-body::-webkit-scrollbar {
    width: 6px;
}

.sidebar-desktop .bg-white::-webkit-scrollbar-track,
.offcanvas-body::-webkit-scrollbar-track {
    background: #e0f2fe;
    border-radius: 10px;
}

.sidebar-desktop .bg-white::-webkit-scrollbar-thumb,
.offcanvas-body::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #0ea5e9, #10b981);
    border-radius: 10px;
}

.sidebar-desktop .bg-white::-webkit-scrollbar-thumb:hover,
.offcanvas-body::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #0284c7, #059669);
}
</style>
</head>
<body class="p-3">

<!-- HEADER -->
<header class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="logo-icon">KT</div>
        <div class="header-title">
            <h1><?= ORGANIZATION_NAME ?></h1>
            <p class="d-none d-sm-block">Satu visi, satu aksi, untuk kemajuan bersama</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="online-status">
            <div class="online-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <span class="online-count" id="onlineCount"><?= $online_count ?></span>
        </div>
        <button class="menu-toggle d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<div class="row g-4">
    <!-- SIDEBAR DESKTOP -->
    <aside class="col-lg-3 sidebar-desktop">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <nav>
                <ul class="nav flex-column gap-2 nav-pills-custom">
                    <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'absensi') ? 'active' : '' ?>" href="?tab=absensi"><i class="fa-solid fa-calendar-check me-2"></i> Absensi</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
                </ul>
            </nav>
            <div class="mt-4 pt-4 border-top">
                <div class="stat-card text-center">
                    <h3 id="totalAnggotaDesktop"><?= $total_anggota ?></h3>
                    <p>Total Anggota Aktif <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN -->
    <section class="col-lg-9">
        <div class="content-card">
            <?php
            switch ($active_tab) {
                case 'anggota': include 'anggota.php'; break;
                case 'absensi': include 'absensi.php'; break;
                case 'kegiatan': include 'kegiatan.php'; break;
                case 'keuangan': include 'keuangan.php'; break;
                case 'iuran': include 'iuran.php'; break;
                case 'iuran17': include 'iuran17.php'; break;
            }
            ?>
             
        </div>
        <?php require_once 'footer.php'; ?>
    </section>
    
</div>

<!-- OFFCANVAS MENU MOBILE -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i>Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column gap-2 nav-pills-custom mb-4">
            <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
            <li><a class="nav-link <?= ($active_tab == 'absensi') ? 'active' : '' ?>" href="?tab=absensi"><i class="fa-solid fa-calendar-check me-2"></i> Absensi</a></li>
            <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
        </ul>
        <div class="stat-card text-center">
            <h3 id="totalAnggotaMobile"><?= $total_anggota ?></h3>
            <p>Total Anggota Aktif <?= date('Y') ?></p>
        </div>
    </div>
</div>

<!-- MODAL IURAN -->
<div class="modal fade" id="iuranModal" tabindex="-1" aria-labelledby="iuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h4 class="modal-title fw-bolder text-dark" id="iuranModalLabel">Pilih Jenis Iuran Anda</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Silakan pilih kategori iuran yang ingin Anda kelola atau lihat.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="?tab=iuran" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="icon-box text-primary mb-3"><i class="fa-solid fa-coins fa-2x"></i></div>
                            <h6 class="fw-bold mb-1">Iuran Kas</h6>
                            <p class="small text-muted mb-0">Kelola iuran rutin kas bulanan.</p>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="?tab=iuran17" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="icon-box text-danger mb-3"><i class="fa-solid fa-star fa-2x"></i></div>
                            <h6 class="fw-bold mb-1">Iuran 17-an</h6>
                            <p class="small text-muted mb-0">Lihat kontribusi acara 17 Agustus.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh online count tanpa reload halaman -->
<script>
function updateOnlineCount() {
    fetch('?ajax_update_online=1')
        .then(response => response.json())
        .then(data => {
            // Update counter di header
            document.getElementById('onlineCount').textContent = data.count;
        })
        .catch(error => console.log('Error updating online count:', error));
}

// Update setiap 15 detik
setInterval(updateOnlineCount, 15000);

// Update saat halaman kembali aktif
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateOnlineCount();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>