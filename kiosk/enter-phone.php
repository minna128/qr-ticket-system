<?php
/**
 * Kiosk - Enter Phone Number
 * World Play QR Ticketing System
 */

$pageTitle = 'Enter Phone Number';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';

// Validate previous step
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['pricing_id'])) {
    redirect('/kiosk/select-duration.php');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/kiosk/select-duration.php');
}

$pricingId = (int)$_POST['pricing_id'];
$pdo = getDBConnection();

// Verify pricing exists
$stmt = $pdo->prepare("SELECT * FROM pricing WHERE id = :id AND is_active = 1");
$stmt->execute([':id' => $pricingId]);
$pricing = $stmt->fetch();

if (!$pricing) {
    redirect('/kiosk/select-duration.php');
}

// Store in session for next step
$_SESSION['kiosk_pricing_id'] = $pricing['id'];
$_SESSION['kiosk_duration'] = $pricing['duration_minutes'];
$_SESSION['kiosk_price'] = $pricing['price'];
$_SESSION['kiosk_label'] = $pricing['label'];
$_SESSION['kiosk_extra_per_min'] = $pricing['extra_per_minute'];

// Store game selections if provided
if (isset($_POST['game_ids']) && !empty($_POST['game_ids'])) {
    $_SESSION['kiosk_game_ids'] = $_POST['game_ids'];
} else {
    $_SESSION['kiosk_game_ids'] = '';
}

// Calculate totals (duration + selected games) for display
$gamesTotal = 0;
$selectedGamesCount = 0;
if (!empty($_SESSION['kiosk_game_ids'])) {
    $gameIds = explode(',', $_SESSION['kiosk_game_ids']);
    $gameIds = array_map('intval', $gameIds);
    $gameIds = array_filter($gameIds);

    if (!empty($gameIds)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(price) as total FROM games WHERE id IN (" . implode(',', $gameIds) . ")");
        $stmt->execute();
        $gameData = $stmt->fetch();
        $gamesTotal = (float)($gameData['total'] ?? 0);
        $selectedGamesCount = (int)($gameData['count'] ?? 0);
    }
}
$grandTotal = (float)$pricing['price'] + (float)$gamesTotal;
$_SESSION['kiosk_games_total'] = $gamesTotal;
$_SESSION['kiosk_total_amount'] = $grandTotal;
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.enter_phone.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.enter_phone.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot completed"></span>
        <span class="step-dot completed"></span>
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
    </div>

    <form action="<?php echo BASE_URL; ?>/kiosk/payment.php" method="POST">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
                <label class="form-label"><?php echo sanitize(t('kiosk.enter_phone.label')); ?></label>
            <div class="phone-input-wrapper">
                <input type="tel" 
                       id="phone-input" 
                       name="phone" 
                       class="phone-input" 
                       placeholder="7X XXX XXXX" 
                       maxlength="12"
                       required>
            </div>
            <small style="color: var(--text-muted);"><?php echo sanitize(t('kiosk.enter_phone.hint')); ?></small>
        </div>
        
        <div class="summary-box">
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.enter_phone.selected_plan')); ?></span>
                <span><?php echo sanitize($pricing['label']); ?></span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.duration')); ?></span>
                <span><?php echo $pricing['duration_minutes']; ?> minutes</span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.enter_phone.price')); ?></span>
                <span><?php echo formatCurrency($pricing['price']); ?></span>
            </div>
            <?php if ($selectedGamesCount > 0): ?>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.payment.games', ['count' => $selectedGamesCount])); ?></span>
                <span><?php echo formatCurrency($gamesTotal); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.games.grand_total')); ?></span>
                <span><?php echo formatCurrency($grandTotal); ?></span>
            </div>
        </div>
        
        <div class="kiosk-nav">
            <a href="<?php echo BASE_URL; ?>/kiosk/select-duration.php" class="btn btn-outline"><?php echo sanitize(t('kiosk.back')); ?></a>
            <button type="submit" id="btn-continue" class="btn btn-primary btn-lg" disabled><?php echo sanitize(t('kiosk.continue')); ?></button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
