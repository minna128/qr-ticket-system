<?php
/**
 * Entry Gate - QR Scanner Interface
 * World Play QR Ticketing System
 */

$pageTitle = 'Entry Scanner';
$bodyClass = 'scanner-page';
$extraCSS = ['scanner.css'];
$extraJS = ['scanner.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

if (!requireStaff()) {
    header("Location: " . BASE_URL . "/entry/");
    exit;
}
?>

<meta name="base-url" content="<?php echo BASE_URL; ?>">

<div class="scanner-container">
    <div class="scanner-header">
        <h2>Entry Gate - Scan Ticket</h2>
        <p>Scan the visitor's QR code or enter ticket ID manually</p>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Staff: <?php echo sanitize($_SESSION['staff_name']); ?></p>
    </div>
    
    <?php echo csrfField(); ?>
    
    <div class="scanner-viewport">
        <div id="qr-reader"></div>
    </div>
    
    <div class="scanner-divider">
        <span>OR ENTER MANUALLY</span>
    </div>
    
    <div class="manual-entry">
        <input type="text" 
               id="manual-ticket-id" 
               placeholder="WP-XXXXXX" 
               maxlength="9"
               autocomplete="off">
        <button id="btn-manual-submit" class="btn btn-primary">Check</button>
    </div>
    
    <div id="scan-result" class="scan-result"></div>
    
    <div class="text-center mt-4">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline">Back to Home</a>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initEntryScanner();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
