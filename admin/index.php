<?php
require_once 'process_data.php';

// Logika tab & tahun aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'anggota';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - <?= htmlspecialchars(ORGANIZATION_NAME) ?></title>
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

/* === LOGOUT BUTTON === */
.btn-logout {
    width: 100%;
    padding: 0.9rem 1.2rem;
    border-radius: 1rem;
    border: 2px solid transparent;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
    text-decoration: none;
}

.btn-logout::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.5s;
}

.btn-logout:hover::before {
    left: 100%;
}

.btn-logout i {
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
    border-color: transparent;
}

.btn-logout:hover i {
    transform: rotate(180deg) scale(1.15);
}

.btn-logout:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
}

/* Logout button dengan icon animasi shake */
.btn-logout:hover i.fa-right-from-bracket {
    animation: shakeOut 0.6s ease-in-out;
}

@keyframes shakeOut {
    0%, 100% { transform: translateX(0) rotate(0deg); }
    25% { transform: translateX(-3px) rotate(-5deg); }
    50% { transform: translateX(3px) rotate(5deg); }
    75% { transform: translateX(-3px) rotate(-5deg); }
}

/* Alternative logout button style (danger outline) */
.btn-logout-outline {
    width: 100%;
    padding: 0.9rem 1.2rem;
    border-radius: 1rem;
    border: 2px solid #fecaca;
    background: white;
    color: #dc2626;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    text-decoration: none;
}

.btn-logout-outline::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translate(-50%, -50%);
    transition: width 0.4s ease, height 0.4s ease;
}

.btn-logout-outline span,
.btn-logout-outline i {
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

.btn-logout-outline:hover::before {
    width: 120%;
    height: 300%;
}

.btn-logout-outline:hover {
    color: white;
    border-color: #dc2626;
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
    transform: translateY(-3px);
}

.btn-logout-outline:hover i {
    transform: rotate(180deg);
}

/* Logout section container */
.logout-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid rgba(14, 165, 233, 0.15);
}

/* Mobile specific */
@media (max-width: 575.98px) {
    .btn-logout,
    .btn-logout-outline {
        padding: 0.85rem 1rem;
        font-size: 0.9rem;
    }
    
    .btn-logout i,
    .btn-logout-outline i {
        font-size: 1rem;
    }
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
        <div class="logo-icon">AD</div>
        <div class="header-title">
            <h1>Admin Dashboard - <?= htmlspecialchars(ORGANIZATION_NAME) ?></h1>
            <p class="d-none d-sm-block">Kelola data organisasi Anda dengan mudah</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="admin-badge d-none d-md-flex">
            <i class="fa-solid fa-user-shield"></i>
            <span><strong>Admin Mode</strong></span>
        </div>
        <button class="menu-toggle d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<!-- NOTIFIKASI UPDATE -->
<?php if ($isUpdateAvailable): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 rounded-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
            Versi terbaru tersedia! (<?= htmlspecialchars($remoteVersion) ?>)  
            <button id="update-button" class="btn btn-primary btn-sm ms-2">Perbarui</button>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- NOTIFIKASI MESSAGE -->
<?php if (isset($message)): ?>
<div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show rounded-4" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- SIDEBAR DESKTOP -->
    <aside class="col-lg-3 sidebar-desktop">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <nav>
                <ul class="nav flex-column gap-2 nav-pills-custom">
                    <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'users') ? 'active' : '' ?>" href="?tab=users<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><i class="fa-solid fa-user-circle me-2"></i> Users & Lokasi</a></li>
                    <div class="logout-section">
                        <a href="../logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </ul>
            </nav>
            <div class="mt-4 pt-4 border-top">
                <div class="stat-card text-center">
                    <h3><?= $total_anggota ?></h3>
                    <p>Total Anggota Aktif <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN -->
    <section class="col-lg-9">
        <div class="content-card">
            <?php
            // Define the tab file mapping
            $tab_files = [
                'anggota' => 'anggota.php',
                'kegiatan' => 'kegiatan.php',
                'keuangan' => 'keuangan.php',
                'iuran' => 'iuran.php',
                'iuran17' => 'iuran17.php',
                'users' => 'userlokasi.php'
            ];

            // Determine which file to include
            $include_file = isset($tab_files[$active_tab]) ? $tab_files[$active_tab] : $tab_files['anggota'];

            // Include the corresponding tab file
            if (file_exists($include_file)) {
                include $include_file;
            } else {
                echo "<p>Error: Content file not found.</p>";
            }
            ?>
             
        </div>
        <?php require_once '../footer.php'; ?>
    </section>
    
</div>

<!-- OFFCANVAS MENU MOBILE -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i>Menu Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column gap-2 nav-pills-custom mb-4">
            <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
            <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
            <li><a class="nav-link <?= ($active_tab == 'users') ? 'active' : '' ?>" href="?tab=users<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><i class="fa-solid fa-user-circle me-2"></i> Users & Lokasi</a></li>
            <div class="logout-section">
                <a href="../logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </ul>
        <div class="stat-card text-center">
            <h3><?= $total_anggota ?></h3>
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
                        <a href="?tab=iuran" class="card card-hover h-100 border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="card-body">
                                <div class="icon-circle bg-primary-subtle text-primary mb-3">
                                    <i class="fa-solid fa-coins fa-2x"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Iuran Kas</h6>
                                <p class="small text-muted mb-0">Kelola iuran rutin kas bulanan.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="?tab=iuran17" class="card card-hover h-100 border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="card-body">
                                <div class="icon-circle bg-danger-subtle text-danger mb-3">
                                    <i class="fa-solid fa-star fa-2x"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Iuran 17-an</h6>
                                <p class="small text-muted mb-0">Lihat kontribusi acara 17 Agustus.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

<div class="modal fade" id="addAnggotaModal" tabindex="-1" aria-labelledby="addAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnggotaModalLabel">Tambah Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="anggota">
                    <div class="mb-3">
                        <label for="add-nama-lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="add-nama-lengkap" name="data[nama_lengkap]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jabatan" class="form-label">Jabatan</label>
                        <select class="form-control" id="add-jabatan" name="data[jabatan]" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach (JABATAN_OPTIONS as $jabatan): ?>
                                <option value="<?= htmlspecialchars($jabatan) ?>">
                                    <?= htmlspecialchars($jabatan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-no-hp" class="form-label">No hp</label>
                        <input type="text" class="form-control" id="add-no-hp" name="data[no_hp]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-bergabung-sejak" class="form-label">Bergabung Sejak</label>
                        <input type="date" class="form-control" id="add-bergabung-sejak" name="data[bergabung_sejak]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAnggotaModal" tabindex="-1" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnggotaModalLabel">Edit Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="anggota">
                    <input type="hidden" name="id" id="edit-anggota-id">

                    <div class="mb-3">
                        <label for="edit-nama-lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit-nama-lengkap" name="data[nama_lengkap]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-jabatan" class="form-label">Jabatan</label>
                        <select class="form-control" id="edit-jabatan" name="data[jabatan]" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach (JABATAN_OPTIONS as $jabatan): ?>
                                <option value="<?= htmlspecialchars($jabatan) ?>"><?= htmlspecialchars($jabatan) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit-nohp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="edit-nohp" name="data[no_hp]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-bergabung-sejak" class="form-label">Bergabung Sejak</label>
                        <input type="date" class="form-control" id="edit-bergabung-sejak" name="data[bergabung_sejak]" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addKegiatanModal" tabindex="-1" aria-labelledby="addKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKegiatanModalLabel">Tambah Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="kegiatan">
                    <div class="mb-3">
                        <label for="add-nama-kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="add-nama-kegiatan" name="data[nama_kegiatan]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="add-lokasi" name="data[lokasi]">
                    </div>
                    <div class="mb-3">
                        <label for="add-deskripsi" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="add-deskripsi" name="data[deskripsi]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-notulen" class="form-label">Notulen</label>
                        <textarea class="form-control" id="add-notulen" name="data[notulen]" rows="3"></textarea>
                    </div>                  
                    <div class="mb-3">
                        <label for="add-tanggal-mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="add-tanggal-mulai" name="data[tanggal_mulai]" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editKegiatanModal" tabindex="-1" aria-labelledby="editKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKegiatanModalLabel">Edit Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="kegiatan">
                    <input type="hidden" name="id" id="edit-kegiatan-id">

                    <div class="mb-3">
                        <label for="edit-nama-kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="edit-nama-kegiatan" name="data[nama_kegiatan]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="edit-lokasi" name="data[lokasi]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-deskripsi" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="edit-deskripsi" name="data[deskripsi]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-notulen" class="form-label">Notulen</label>
                        <textarea class="form-control" id="edit-notulen" name="data[notulen]" rows="3"></textarea>
                    </div> 

                    <div class="mb-3">
                        <label for="edit-tanggal-mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="edit-tanggal-mulai" name="data[tanggal_mulai]" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addKeuanganModal" tabindex="-1" aria-labelledby="addKeuanganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKeuanganModalLabel">Tambah Transaksi Keuangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="keuangan">
                    <div class="mb-3">
                        <label for="add-jenis-transaksi" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="add-jenis-transaksi" name="data[jenis_transaksi]" required>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-keuangan" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-keuangan" name="data[jumlah]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-deskripsi-keuangan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="add-deskripsi-keuangan" name="data[deskripsi]" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-transaksi" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="add-tanggal-transaksi" name="data[tanggal_transaksi]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editKeuanganModal" tabindex="-1" aria-labelledby="editKeuanganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKeuanganModalLabel">Edit Transaksi Keuangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="keuangan">
                    <input type="hidden" name="id" id="edit-keuangan-id">
                    <div class="mb-3">
                        <label for="edit-jenis-transaksi" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="edit-jenis-transaksi" name="data[jenis_transaksi]" required>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-keuangan" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-keuangan" name="data[jumlah]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-deskripsi-keuangan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit-deskripsi-keuangan" name="data[deskripsi]" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-transaksi" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="edit-tanggal-transaksi" name="data[tanggal_transaksi]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addIuranModal" tabindex="-1" aria-labelledby="addIuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIuranModalLabel">Tambah Pembayaran Iuran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="addIuranForm">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="iuran">

                    <div class="mb-3">
                        <label for="search-anggota-iuran" class="form-label">Nama Anggota</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-iuran" placeholder="Cari anggota...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-iuran" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-iuran" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-bayar" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="add-tanggal-bayar" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-iuran" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-iuran" name="data[jumlah_bayar]" required min="0" placeholder="Isi jika kurang <?= number_format(DUES_MONTHLY_FEE, 0, ',', '.') ?>">
                            <button class="btn btn-outline-secondary" type="button" id="autoFillBtn">Isi Otomatis</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="add-keterangan" name="data[keterangan]" rows="2" placeholder="Tidak peralu di isi"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editIuranModal" tabindex="-1" aria-labelledby="editIuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIuranModalLabel">Edit Pembayaran Iuran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="iuran">
                    <input type="hidden" name="id" id="edit-iuran-id">
                    <div class="mb-3">
                        <label for="edit-anggota-id" class="form-label">Nama Anggota</label>
                        <select class="form-select" id="edit-anggota-id" name="data[anggota_id]" required>
                            <option value="">Pilih Anggota</option>
                            <?php foreach ($anggotaList as $anggota): ?>
                                <option value="<?= $anggota['id'] ?>"><?= htmlspecialchars($anggota['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-bayar" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="edit-tanggal-bayar" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-iuran" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-iuran" name="data[jumlah_bayar]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit-keterangan" name="data[keterangan]" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addIuran17Modal" tabindex="-1" aria-labelledby="addIuran17ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIuran17ModalLabel">Tambah Pembayaran Iuran 17</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="addIuran17Form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="iuran17">

                    <div class="mb-3">
                        <label for="search-anggota-iuran17" class="form-label">Nama Anggota</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-iuran17" placeholder="Cari anggota...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-iuran17" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-iuran17" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-bayar17" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="add-tanggal-bayar17" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-iuran17" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-iuran17" name="data[jumlah_bayar]" required min="0" placeholder="Isi jika kurang dari <?= number_format(DUES_MONTHLY_FEE17, 0, ',', '.') ?>">
                            <button class="btn btn-outline-secondary" type="button" id="autoFillBtn17">Isi Otomatis</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-keterangan17" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="add-keterangan17" name="data[keterangan]" rows="2" placeholder="Isi jika perlu"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editIuran17Modal" tabindex="-1" aria-labelledby="editIuran17ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIuran17ModalLabel">Edit Pembayaran Iuran 17</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="iuran17">
                    <input type="hidden" name="id" id="edit-iuran17-id">
                    <div class="mb-3">
                        <label for="edit-anggota-id17" class="form-label">Nama Anggota</label>
                        <select class="form-select" id="edit-anggota-id17" name="data[anggota_id]" required>
                            <option value="">Pilih Anggota</option>
                            <?php foreach ($anggotaList as $anggota): ?>
                                <option value="<?= $anggota['id'] ?>"><?= htmlspecialchars($anggota['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-bayar17" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="edit-tanggal-bayar17" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-iuran17" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-iuran17" name="data[jumlah_bayar]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-keterangan17" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit-keterangan17" name="data[keterangan]" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUsersModal" tabindex="-1" aria-labelledby="addUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUsersModalLabel">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="users">
                    <div class="mb-3">
                        <label for="add-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="add-username" name="data[username]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="add-password" name="data[password]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-role" class="form-label">Role</label>
                        <select class="form-select" id="add-role" name="data[role]" required>                          
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="search-anggota-user" class="form-label">Anggota Terkait<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-user" placeholder="Cari anggota sesuai dengan jabatan...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-user" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-user" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUsersModal" tabindex="-1" aria-labelledby="editUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsersModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="users">
                    <input type="hidden" name="id" id="edit-users-id">
                    <div class="mb-3">
                        <label for="edit-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="data[username]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-password" class="form-label">Password (kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" id="edit-password" name="data[password]">
                    </div>
                    <div class="mb-3">
                        <label for="edit-role" class="form-label">Role</label>
                        <select class="form-select" id="edit-role" name="data[role]" required>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-search-anggota-user" class="form-label">Anggota Terkait <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit-search-anggota-user" placeholder="Cari anggota sesuai dengan jabatan...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="edit-search-results-user" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="edit-anggota-id-user" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addLokasiModal" tabindex="-1" aria-labelledby="addLokasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLokasiModalLabel"><i class="fa-solid fa-map-marker-alt me-2"></i>Atur Lokasi Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_location">
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="detect-device-location-btn">
                            <i class="fas fa-crosshairs me-2"></i>Gunakan Lokasi Perangkat Sekarang
                        </button>
                    </div>

                    <div id="map-container" style="height: 250px; width: 100%; margin-bottom: 15px;">
                        <iframe id="gmaps-iframe" width="100%" height="100%" frameborder="0" style="border:0"
                            src="" allowfullscreen>
                        </iframe>
                    </div>

                    <div class="mb-3">
                        <label for="lokasi_latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="lokasi_latitude" name="lokasi_latitude" required value="<?= htmlspecialchars($current_latitude) ?>" readonly>
                        <div class="form-text">Latitude akan otomatis terisi setelah deteksi lokasi perangkat.</div>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi_longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="lokasi_longitude" name="lokasi_longitude" required value="<?= htmlspecialchars($current_longitude) ?>" readonly>
                        <div class="form-text">Longitude akan otomatis terisi setelah deteksi lokasi perangkat.</div>
                    </div>
                    <div class="mb-3">
                        <label for="jarak_toleransi" class="form-label">Jarak Toleransi (meter)</label>
                        <input type="number" class="form-control" id="jarak_toleransi" name="jarak_toleransi" required value="<?= htmlspecialchars($current_tolerance) ?>">
                        <div class="form-text">Jarak maksimal dari lokasi yang diizinkan untuk absensi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="durasi_absensi" class="form-label">Durasi Absensi (menit)</label>
                        <input type="number" class="form-control" id="lokasi_durasi" name="lokasi_durasi" required value="<?= htmlspecialchars($current_duration) ?>">
                        <div class="form-text">Contoh: 60 menit = 1 jam.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script src="../assets/js/maps.js"></script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Ambil daftar anggota dari PHP dan konversi ke JavaScript
    const anggotaList = <?= json_encode($anggotaList) ?>;

    const searchInput = document.getElementById('search-anggota-iuran');
    const searchResultsDiv = document.getElementById('search-results-iuran');
    const anggotaIdInput = document.getElementById('add-anggota-id-iuran');
    const form = document.getElementById('addIuranForm');

    // Fungsi untuk menampilkan hasil pencarian
    const displayResults = (results) => {
        searchResultsDiv.innerHTML = ''; // Hapus hasil sebelumnya
        if (results.length > 0) {
            results.forEach(anggota => {
                const resultItem = document.createElement('a');
                resultItem.href = '#';
                resultItem.classList.add('list-group-item', 'list-group-item-action');
                resultItem.textContent = anggota.nama_lengkap;
                resultItem.setAttribute('data-id', anggota.id);
                searchResultsDiv.appendChild(resultItem);
            });
            searchResultsDiv.style.display = 'block';
        } else {
            searchResultsDiv.style.display = 'none';
        }
    };

    // Event listener saat pengguna mengetik
    searchInput.addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        if (query.length > 0) {
            const filteredResults = anggotaList.filter(anggota =>
                anggota.nama_lengkap.toLowerCase().includes(query)
            );
            displayResults(filteredResults);
        } else {
            searchResultsDiv.innerHTML = '';
            searchResultsDiv.style.display = 'none';
        }
    });

    // Event listener saat hasil pencarian diklik
    searchResultsDiv.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const selectedId = e.target.getAttribute('data-id');
            const selectedNama = e.target.textContent;

            // Isi input pencarian dan input tersembunyi
            anggotaIdInput.value = selectedId;
            searchInput.value = selectedNama;

            // Sembunyikan hasil pencarian
            searchResultsDiv.innerHTML = '';
            searchResultsDiv.style.display = 'none';
        }
    });
    
    // Sembunyikan hasil pencarian jika klik di luar area input
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
            searchResultsDiv.style.display = 'none';
        }
    });
});

// --- Fungsionalitas untuk iuran17 ---
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil daftar anggota dari PHP dan konversi ke JavaScript
        const anggotaList = <?= json_encode($anggotaList) ?>;

        const searchInput17 = document.getElementById('search-anggota-iuran17');
        const searchResultsDiv17 = document.getElementById('search-results-iuran17');
        const anggotaIdInput17 = document.getElementById('add-anggota-id-iuran17');
        const form17 = document.getElementById('addIuran17Form');

        // Fungsi untuk menampilkan hasil pencarian
        const displayResults17 = (results) => {
            searchResultsDiv17.innerHTML = ''; // Hapus hasil sebelumnya
            if (results.length > 0) {
                results.forEach(anggota => {
                    const resultItem = document.createElement('a');
                    resultItem.href = '#';
                    resultItem.classList.add('list-group-item', 'list-group-item-action');
                    resultItem.textContent = anggota.nama_lengkap;
                    resultItem.setAttribute('data-id', anggota.id);
                    searchResultsDiv17.appendChild(resultItem);
                });
                searchResultsDiv17.style.display = 'block';
            } else {
                searchResultsDiv17.style.display = 'none';
            }
        };
        
        // Event listener saat pengguna mengetik
        searchInput17.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            if (query.length > 0) {
                const filteredResults = anggotaList.filter(anggota =>
                    anggota.nama_lengkap.toLowerCase().includes(query)
                );
                displayResults17(filteredResults);
            } else {
                searchResultsDiv17.innerHTML = '';
                searchResultsDiv17.style.display = 'none';
            }
        });

        // Event listener saat hasil pencarian diklik
        searchResultsDiv17.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                const selectedId = e.target.getAttribute('data-id');
                const selectedNama = e.target.textContent;

                // Isi input pencarian dan input tersembunyi
                anggotaIdInput17.value = selectedId;
                searchInput17.value = selectedNama;

                // Sembunyikan hasil pencarian
                searchResultsDiv17.innerHTML = '';
                searchResultsDiv17.style.display = 'none';
            }
        });
        
        // Sembunyikan hasil pencarian jika klik di luar area input
        document.addEventListener('click', function(e) {
            if (!searchInput17.contains(e.target) && !searchResultsDiv17.contains(e.target)) {
                searchResultsDiv17.style.display = 'none';
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan data anggota tersedia (misal dari PHP)
    const anggotaList = [
        <?php foreach ($anggotaList as $anggota): ?>
            { id: <?= $anggota['id'] ?>, nama: '<?= htmlspecialchars($anggota['nama_lengkap']) ?>' },
        <?php endforeach; ?>
    ];

    // --- Skrip untuk Modal TAMBAH User (dari perbaikan sebelumnya) ---
    const searchInputAdd = document.getElementById('search-anggota-user');
    const searchResultsAdd = document.getElementById('search-results-user');
    const selectedAnggotaIdAdd = document.getElementById('add-anggota-id-user');

    if (searchInputAdd) {
        searchInputAdd.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            searchResultsAdd.innerHTML = '';
            if (query.length > 0) {
                const filteredAnggota = anggotaList.filter(anggota =>
                    anggota.nama.toLowerCase().includes(query)
                );
                filteredAnggota.forEach(anggota => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = anggota.nama;
                    item.setAttribute('data-id', anggota.id);
                    item.setAttribute('data-nama', anggota.nama);
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        searchInputAdd.value = this.getAttribute('data-nama');
                        selectedAnggotaIdAdd.value = this.getAttribute('data-id');
                        searchResultsAdd.innerHTML = '';
                    });
                    searchResultsAdd.appendChild(item);
                });
            }
        });
    }   
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const DUES_MONTHLY_FEE = <?php echo DUES_MONTHLY_FEE; ?>;
        
        const autoFillBtn = document.getElementById('autoFillBtn');
        const jumlahIuranInput = document.getElementById('add-jumlah-iuran');

        // Pastikan kedua elemen ditemukan sebelum menambahkan event listener
        if (autoFillBtn && jumlahIuranInput) {
            autoFillBtn.addEventListener('click', function() {
                jumlahIuranInput.value = DUES_MONTHLY_FEE;
            });
        } else {
            console.error("Elemen tombol atau input tidak ditemukan.");
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const DUES_MONTHLY_FEE17 = <?php echo DUES_MONTHLY_FEE17; ?>;

        const autoFillBtn17 = document.getElementById('autoFillBtn17');
        const jumlahIuran17Input = document.getElementById('add-jumlah-iuran17');

        // Pastikan kedua elemen ditemukan sebelum menambahkan event listener
        if (autoFillBtn17 && jumlahIuran17Input) {
            autoFillBtn17.addEventListener('click', function() {
                jumlahIuran17Input.value = DUES_MONTHLY_FEE17;
            });
        } else {
            console.error("Elemen tombol atau input iuran17 tidak ditemukan.");
        }
    });
</script> 
<script> // Script deteksi scroll
let lastScrollTop = 0;
window.addEventListener("scroll", function() {
  let st = window.pageYOffset || document.documentElement.scrollTop;
  const nav = document.querySelector(".nav-pills-custom");

  if (st > lastScrollTop) {
    // scroll ke bawah → sembunyikan
    nav.classList.add("hide");
  } else {
    // scroll ke atas → tampilkan
    nav.classList.remove("hide");
  }
  lastScrollTop = st <= 0 ? 0 : st; // biar gak negatif
}, false);
</script>
</body>
</html>