<?php
/**
 * Kiosk Welcome Screen
 * World Play QR Ticketing System
 */

$pageTitle = 'Kiosk';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
$extraJS = ['kiosk.js'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="kiosk-container">
    <div class="kiosk-header">
        <h1><?php echo sanitize(t('kiosk.welcome.title')); ?></h1>
        <p><?php echo sanitize(t('kiosk.welcome.subtitle')); ?></p>
    </div>
    
    <div class="text-center">
        <a href="<?php echo BASE_URL; ?>/kiosk/select-duration.php" class="btn btn-primary btn-lg">
            <?php echo sanitize(t('kiosk.welcome.start')); ?>
        </a>
    </div>
    
    <div class="text-center mt-4">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline"><?php echo sanitize(t('kiosk.welcome.home')); ?></a>
    </div>

    <?php
    // Language switcher (placed below "Back to Home").
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $parts = parse_url($uri);
    $path = $parts['path'] ?? $uri;
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $makeUrl = function ($lang) use ($path, $query) {
        $q = $query;
        $q['lang'] = $lang;
        return $path . '?' . http_build_query($q);
    };
    ?>
    <div class="kiosk-lang-switcher-wrap">
        <div class="kiosk-lang-switcher" role="navigation" aria-label="<?php echo sanitize(t('lang.label')); ?>">
            <span class="kiosk-lang-label"><?php echo sanitize(t('lang.label')); ?>:</span>
            <a class="kiosk-lang-link <?php echo currentLang() === 'en' ? 'active' : ''; ?>" href="<?php echo sanitize($makeUrl('en')); ?>"><?php echo sanitize(t('lang.en')); ?></a>
            <a class="kiosk-lang-link <?php echo currentLang() === 'si' ? 'active' : ''; ?>" href="<?php echo sanitize($makeUrl('si')); ?>"><?php echo sanitize(t('lang.si')); ?></a>
            <a class="kiosk-lang-link <?php echo currentLang() === 'ta' ? 'active' : ''; ?>" href="<?php echo sanitize($makeUrl('ta')); ?>"><?php echo sanitize(t('lang.ta')); ?></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
