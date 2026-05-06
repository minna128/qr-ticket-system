<?php
/**
 * Landing Page
 * World Play QR-Based Smart Kiosk Ticketing System
 */

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="landing-hero">
    <h1>World Play</h1>
    <p>QR-Based Smart Kiosk Ticketing System - Digital ticketing, automated session tracking, and real-time monitoring for Kandy City Centre.</p>
</div>

<div class="nav-cards">
    <a href="<?php echo BASE_URL; ?>/kiosk/" class="nav-card">
        <div class="icon">&#127915;</div>
        <h3>Kiosk</h3>
        <p>Self-service ticket purchase with QR code generation</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/entry/" class="nav-card">
        <div class="icon">&#128275;</div>
        <h3>Entry Gate</h3>
        <p>Scan QR codes and activate play sessions</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/exit/" class="nav-card">
        <div class="icon">&#128682;</div>
        <h3>Exit Gate</h3>
        <p>Process exits and handle overstay payments</p>
    </a>
    
    <a href="<?php echo BASE_URL; ?>/admin/" class="nav-card">
        <div class="icon">&#128202;</div>
        <h3>Admin Dashboard</h3>
        <p>Monitor sessions, revenue reports, and system settings</p>
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
