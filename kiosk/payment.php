<?php
/**
 * Kiosk - Payment Method Selection
 * World Play QR Ticketing System
 */

$pageTitle = 'Payment Method';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';

// Validate previous step
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['phone'])) {
    redirect('/kiosk/select-duration.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/kiosk/select-duration.php');
}

// Validate phone
$phone = trim($_POST['phone']);
if (!validatePhone($phone)) {
    setFlash('danger', 'Please enter a valid Sri Lankan phone number.');
    redirect('/kiosk/select-duration.php');
}

// Check session data from previous step
if (!isset($_SESSION['kiosk_pricing_id'])) {
    redirect('/kiosk/select-duration.php');
}

// Check rate limit
$pdo = getDBConnection();
$formattedPhone = formatPhone($phone);
$maxPerHour = (int)getSetting($pdo, 'max_tickets_per_phone_per_hour') ?: 5;

if (!checkPhoneRateLimit($pdo, $formattedPhone, $maxPerHour)) {
    setFlash('danger', 'Too many tickets purchased with this number. Please try again later.');
    redirect('/kiosk/select-duration.php');
}

// Store phone in session
$_SESSION['kiosk_phone'] = $formattedPhone;

// Calculate total with games
$durationPrice = $_SESSION['kiosk_price'];
$gamesTotal = 0;
$selectedGamesCount = 0;

if (isset($_SESSION['kiosk_game_ids']) && !empty($_SESSION['kiosk_game_ids'])) {
    $gameIds = explode(',', $_SESSION['kiosk_game_ids']);
    $gameIds = array_map('intval', $gameIds);
    $gameIds = array_filter($gameIds);
    
    if (!empty($gameIds)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(price) as total FROM games WHERE id IN (" . implode(',', $gameIds) . ")");
        $stmt->execute();
        $gameData = $stmt->fetch();
        $gamesTotal = $gameData['total'] ?? 0;
        $selectedGamesCount = $gameData['count'] ?? 0;
    }
}

$_SESSION['kiosk_games_total'] = $gamesTotal;
$_SESSION['kiosk_total_amount'] = $durationPrice + $gamesTotal;
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.payment.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.payment.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
    </div>

    <form action="<?php echo BASE_URL; ?>/kiosk/card-payment.php" method="POST">
        <?php echo csrfField(); ?>
        <input type="hidden" name="payment_method" id="selected-payment-method" value="">
        
        <div class="payment-methods">
            <div class="payment-card" data-method="Cash">
                <div class="payment-icon">&#128181;</div>
                <div class="payment-label"><?php echo sanitize(t('kiosk.payment.cash')); ?></div>
            </div>
            <div class="payment-card" data-method="Card">
                <div class="payment-icon">&#128179;</div>
                <div class="payment-label"><?php echo sanitize(t('kiosk.payment.card')); ?></div>
            </div>
        </div>
        
        <div class="summary-box">
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.plan')); ?></span>
                <span><?php echo sanitize($_SESSION['kiosk_label']); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.duration')); ?></span>
                <span><?php echo $_SESSION['kiosk_duration']; ?> minutes</span>
            </div>
            <?php if ($selectedGamesCount > 0): ?>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.games', ['count' => $selectedGamesCount])); ?></span>
                <span><?php echo formatCurrency($gamesTotal); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.phone')); ?></span>
                <span><?php echo sanitize($formattedPhone); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.total')); ?></span>
                <span><?php echo formatCurrency($_SESSION['kiosk_total_amount']); ?></span>
            </div>
        </div>
        
        <div class="kiosk-nav">
            <a href="<?php echo BASE_URL; ?>/kiosk/select-duration.php" class="btn btn-outline"><?php echo sanitize(t('kiosk.back')); ?></a>
            <button type="submit" id="btn-continue" class="btn btn-primary btn-lg" disabled><?php echo sanitize(t('kiosk.payment.continue_to_payment')); ?></button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
