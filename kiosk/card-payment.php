<?php
/**
 * Kiosk - Card Payment Simulation
 * World Play QR Ticketing System
 */

$pageTitle = 'Card Payment';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';

// Validate previous step
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['payment_method'])) {
    redirect('/kiosk/select-duration.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/kiosk/select-duration.php');
}

$paymentMethod = $_POST['payment_method'];
if (!in_array($paymentMethod, ['Cash', 'Card'])) {
    redirect('/kiosk/payment.php');
}

// Verify session data
if (!isset($_SESSION['kiosk_pricing_id']) || !isset($_SESSION['kiosk_phone'])) {
    redirect('/kiosk/select-duration.php');
}

// If cash, go directly to confirm
if ($paymentMethod === 'Cash') {
    $_SESSION['kiosk_payment_method'] = 'Cash';
    redirect('/kiosk/confirm.php');
}

$_SESSION['kiosk_payment_method'] = 'Card';

// Calculate total
$durationPrice = $_SESSION['kiosk_price'];
$pdo = getDBConnection();
$gamesSummary = function_exists('getSelectedGamesSummary') ? getSelectedGamesSummary($pdo, $_SESSION['kiosk_game_ids'] ?? '') : ['total' => 0, 'games' => []];
$gamesTotal   = $gamesSummary['total'];
$selectedGames = $gamesSummary['games'];
$totalAmount = $durationPrice + $gamesTotal;
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.card_payment.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.card_payment.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot active"></span>
    </div>

    <form id="card-payment-form" action="<?php echo BASE_URL; ?>/kiosk/confirm.php" method="POST">
        <?php echo csrfField(); ?>
        <input type="hidden" name="payment_method" value="Card">
        
        <!-- Card Preview -->
        <div class="card-preview">
            <div class="card-chip"></div>
            <div class="card-number-display">•••• •••• •••• ••••</div>
            <div class="card-details">
                <div>
                    <div class="card-label">Card Holder</div>
                    <div class="card-name-display">CARDHOLDER NAME</div>
                </div>
                <div>
                    <div class="card-label">Expires</div>
                    <div class="card-expiry-display">MM/YY</div>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <div class="card-payment-form">
            <div class="form-group">
                <label for="card-number">Card Number</label>
                <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" autocomplete="off">
                <div class="error-message">Please enter a valid 16-digit card number</div>
            </div>
            
            <div class="form-group">
                <label for="card-name">Cardholder Name</label>
                <input type="text" id="card-name" placeholder="John Doe" autocomplete="off">
                <div class="error-message">Please enter the cardholder name</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="card-expiry">Expiry Date</label>
                    <input type="text" id="card-expiry" placeholder="MM/YY" maxlength="5" autocomplete="off">
                    <div class="error-message">Invalid expiry date</div>
                </div>
                
                <div class="form-group">
                    <label for="card-cvv">CVV</label>
                    <input type="password" id="card-cvv" placeholder="•••" maxlength="4" autocomplete="off">
                    <div class="error-message">Invalid CVV</div>
                </div>
            </div>
        </div>
        
        <!-- Payment Summary -->
        <div class="summary-box">
            <div class="summary-row">
                <span>Duration Package</span>
                <span><?php echo formatCurrency($durationPrice); ?></span>
            </div>
            <?php if (!empty($selectedGames)): ?>
                <div class="summary-row">
                    <span>Games (<?php echo count($selectedGames); ?>)</span>
                    <span><?php echo formatCurrency($gamesTotal); ?></span>
                </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>Total Amount</span>
                <span><?php echo formatCurrency($totalAmount); ?></span>
            </div>
        </div>
        
        <div class="kiosk-nav">
            <a href="<?php echo BASE_URL; ?>/kiosk/payment.php" class="btn btn-outline"><?php echo sanitize(t('kiosk.back')); ?></a>
            <button type="submit" class="btn btn-primary btn-lg"><?php echo sanitize(t('kiosk.card_payment.pay', ['amount' => formatCurrency($totalAmount)])); ?></button>
        </div>
    </form>
</div>

<!-- Processing Overlay -->
<div id="processing-overlay" class="processing-overlay">
    <div class="processing-box">
        <div class="spinner"></div>
        <h3><?php echo sanitize(t('kiosk.card_payment.processing_title')); ?></h3>
        <p><?php echo sanitize(t('kiosk.card_payment.processing_subtitle')); ?></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
