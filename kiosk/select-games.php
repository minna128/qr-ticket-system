<?php
/**
 * Kiosk - Select Games
 * World Play QR Ticketing System
 */

$pageTitle = 'Select Games';
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

// Get active games
$stmt = $pdo->query("SELECT * FROM games WHERE is_active = 1 ORDER BY display_order ASC");
$games = $stmt->fetchAll();
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.games.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.games.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot completed"></span>
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
    </div>

    <form action="<?php echo BASE_URL; ?>/kiosk/enter-phone.php" method="POST">
        <?php echo csrfField(); ?>
        <input type="hidden" name="pricing_id" value="<?php echo $pricing['id']; ?>">
        <input type="hidden" name="game_ids" id="selected-game-ids" value="">
        
        <div class="games-grid">
            <?php foreach ($games as $game): ?>
                <div class="game-card" data-game-id="<?php echo $game['id']; ?>" data-price="<?php echo $game['price']; ?>">
                    <div class="game-icon"><?php echo $game['icon']; ?></div>
                    <div class="game-name"><?php echo sanitize($game['name']); ?></div>
                    <div class="game-category"><?php echo sanitize($game['category']); ?></div>
                    <div class="game-price"><?php echo formatCurrency($game['price']); ?></div>
                    <div class="game-check">✓</div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="games-summary">
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.games.selected')); ?></span>
                <span id="games-count">0</span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.games.total')); ?></span>
                <span id="games-total">LKR 0.00</span>
            </div>
            <div class="summary-row">
                <span><?php echo sanitize(t('kiosk.games.duration_package')); ?></span>
                <span><?php echo formatCurrency($pricing['price']); ?></span>
            </div>
            <div class="summary-row total-row">
                <span><?php echo sanitize(t('kiosk.games.grand_total')); ?></span>
                <span id="grand-total" data-base-price="<?php echo $pricing['price']; ?>"><?php echo formatCurrency($pricing['price']); ?></span>
            </div>
        </div>
        
        <div class="kiosk-nav">
            <a href="<?php echo BASE_URL; ?>/kiosk/select-duration.php" class="btn btn-outline"><?php echo sanitize(t('kiosk.back')); ?></a>
            <button type="submit" id="btn-continue" class="btn btn-primary btn-lg"><?php echo sanitize(t('kiosk.continue')); ?></button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
