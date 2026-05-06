<?php
/**
 * Kiosk - Select Duration
 * World Play QR Ticketing System
 */

$pageTitle = 'Select Duration';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';

$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM pricing WHERE is_active = 1 ORDER BY duration_minutes ASC");
$plans = $stmt->fetchAll();
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h2><?php echo sanitize(t('kiosk.duration.title')); ?></h2>
        <p><?php echo sanitize(t('kiosk.duration.subtitle')); ?></p>
    </div>
    
    <div class="kiosk-step-indicator">
        <span class="step-dot active"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
        <span class="step-dot"></span>
    </div>

    <form action="<?php echo BASE_URL; ?>/kiosk/select-games.php" method="POST">
        <?php echo csrfField(); ?>
        <input type="hidden" name="pricing_id" id="selected-pricing-id" value="">
        
        <div class="duration-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="duration-card" data-pricing-id="<?php echo $plan['id']; ?>">
                    <div class="duration-time"><?php echo $plan['duration_minutes']; ?> min</div>
                    <div class="duration-label"><?php echo sanitize($plan['label']); ?></div>
                    <div class="duration-price"><?php echo formatCurrency($plan['price']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="kiosk-nav">
            <a href="<?php echo BASE_URL; ?>/kiosk/" class="btn btn-outline"><?php echo sanitize(t('kiosk.back')); ?></a>
            <button type="submit" id="btn-continue" class="btn btn-primary btn-lg" disabled><?php echo sanitize(t('kiosk.continue')); ?></button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
