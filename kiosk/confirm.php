<?php
/**
 * Kiosk - Payment Confirmation (Staff Confirms)
 * World Play QR Ticketing System
 */

$pageTitle = 'Confirm Payment';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';

/**
 * Validate previous step.
 *
 * Card flow reaches here via POST (from `card-payment.php`) and must pass CSRF.
 * Cash flow reaches here via redirect (GET) from `card-payment.php`, so we accept
 * the already-stored session value.
 */
$paymentMethod = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['payment_method'])) {
        redirect('/kiosk/select-duration.php');
    }
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect('/kiosk/select-duration.php');
    }

    $paymentMethod = $_POST['payment_method'];
} else {
    $paymentMethod = $_SESSION['kiosk_payment_method'] ?? null;
}

if (!in_array($paymentMethod, ['Cash', 'Card'], true)) {
    redirect('/kiosk/payment.php');
}

// Verify session data
if (!isset($_SESSION['kiosk_pricing_id']) || !isset($_SESSION['kiosk_phone'])) {
    redirect('/kiosk/select-duration.php');
}

$_SESSION['kiosk_payment_method'] = $paymentMethod;

// Calculate total amount
$durationPrice = $_SESSION['kiosk_price'];
$gamesTotal = $_SESSION['kiosk_games_total'] ?? 0;
$totalAmount = $_SESSION['kiosk_total_amount'] ?? $durationPrice;

// Get selected games info
$selectedGames = [];
if (isset($_SESSION['kiosk_game_ids']) && !empty($_SESSION['kiosk_game_ids'])) {
    $pdo = getDBConnection();
    $gameIds = explode(',', $_SESSION['kiosk_game_ids']);
    $gameIds = array_map('intval', $gameIds);
    $gameIds = array_filter($gameIds);
    
    if (!empty($gameIds)) {
        $stmt = $pdo->prepare("SELECT name, price FROM games WHERE id IN (" . implode(',', $gameIds) . ")");
        $stmt->execute();
        $selectedGames = $stmt->fetchAll();
    }
}
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.confirm.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.confirm.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot active"></span>
    </div>

    <div class="waiting-screen">
        <div class="pulse-icon">&#128176;</div>
        <h3 class="mt-3"><?php echo sanitize(t('kiosk.confirm.waiting')); ?></h3>
        <p><?php echo sanitize(t('kiosk.confirm.pay_to_staff', ['amount' => formatCurrency($totalAmount), 'method' => $paymentMethod])); ?></p>
        
        <div class="summary-box">
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.plan')); ?></span>
                <span><?php echo sanitize($_SESSION['kiosk_label']); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.duration')); ?></span>
                <span><?php echo $_SESSION['kiosk_duration']; ?> minutes</span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.confirm.duration_price')); ?></span>
                <span><?php echo formatCurrency($durationPrice); ?></span>
            </div>
            <?php if (!empty($selectedGames)): ?>
                <?php foreach ($selectedGames as $game): ?>
                <div class="summary-row">
                    <span><?php echo sanitize($game['name']); ?></span>
                    <span><?php echo formatCurrency($game['price']); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.phone')); ?></span>
                <span><?php echo sanitize($_SESSION['kiosk_phone']); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.confirm.payment_method')); ?></span>
                <span><?php echo sanitize($paymentMethod); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.confirm.total_amount')); ?></span>
                <span><?php echo formatCurrency($totalAmount); ?></span>
            </div>
        </div>
        
        <form action="<?php echo BASE_URL; ?>/kiosk/generate-ticket.php" method="POST">
            <?php echo csrfField(); ?>
            <button type="submit" class="btn btn-secondary btn-lg btn-block mt-3">
                <?php echo sanitize(t('kiosk.confirm.received')); ?>
            </button>
        </form>
        
        <div class="mt-3">
            <a href="<?php echo BASE_URL; ?>/kiosk/" class="btn btn-outline"><?php echo sanitize(t('kiosk.cancel')); ?></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
