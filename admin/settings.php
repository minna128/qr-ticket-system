<?php
/**
 * Admin - System Settings
 * World Play QR Ticketing System
 */

$adminPageTitle = 'System Settings';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $settings = $_POST['settings'] ?? [];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
        $stmt->execute([':value' => trim($value), ':key' => $key]);
    }
    
    setFlash('success', 'Settings saved successfully.');
    header("Location: " . BASE_URL . "/admin/settings.php");
    exit;
}

// Fetch all settings
$stmt = $pdo->query("SELECT * FROM settings ORDER BY id ASC");
$settings = $stmt->fetchAll();
?>

<?php displayFlash(); ?>

<div class="admin-card">
    <div class="card-header">
        <h3>System Configuration</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <?php echo csrfField(); ?>
            
            <?php foreach ($settings as $setting): ?>
            <div class="form-group">
                <label class="form-label">
                    <?php echo sanitize($setting['setting_key']); ?>
                    <?php if ($setting['description']): ?>
                        <small style="color: var(--text-muted); font-weight: normal; display: block;">
                            <?php echo sanitize($setting['description']); ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="text" 
                       name="settings[<?php echo sanitize($setting['setting_key']); ?>]" 
                       class="form-control" 
                       value="<?php echo sanitize($setting['setting_value']); ?>">
            </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
