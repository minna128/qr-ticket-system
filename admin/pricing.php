<?php
/**
 * Admin - Pricing Management
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Pricing Management';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $duration = (int)$_POST['duration_minutes'];
        $price = (float)$_POST['price'];
        $extra = (float)$_POST['extra_per_minute'];
        $label = trim($_POST['label']);
        
        if ($duration > 0 && $price > 0 && $extra > 0 && $label) {
            $stmt = $pdo->prepare(
                "INSERT INTO pricing (duration_minutes, price, extra_per_minute, label) 
                 VALUES (:duration, :price, :extra, :label)"
            );
            $stmt->execute([':duration' => $duration, ':price' => $price, ':extra' => $extra, ':label' => $label]);
            setFlash('success', 'Pricing tier added successfully.');
        } else {
            setFlash('danger', 'All fields are required with valid values.');
        }
    }
    
    if ($action === 'update') {
        $id = (int)$_POST['pricing_id'];
        $duration = (int)$_POST['duration_minutes'];
        $price = (float)$_POST['price'];
        $extra = (float)$_POST['extra_per_minute'];
        $label = trim($_POST['label']);
        
        if ($id > 0 && $duration > 0 && $price > 0 && $extra > 0 && $label) {
            $stmt = $pdo->prepare(
                "UPDATE pricing SET duration_minutes = :duration, price = :price, 
                 extra_per_minute = :extra, label = :label WHERE id = :id"
            );
            $stmt->execute([':duration' => $duration, ':price' => $price, ':extra' => $extra, ':label' => $label, ':id' => $id]);
            setFlash('success', 'Pricing tier updated.');
        }
    }
    
    if ($action === 'toggle') {
        $id = (int)$_POST['pricing_id'];
        $stmt = $pdo->prepare("UPDATE pricing SET is_active = NOT is_active WHERE id = :id");
        $stmt->execute([':id' => $id]);
        setFlash('success', 'Pricing tier status updated.');
    }
    
    header("Location: " . BASE_URL . "/admin/pricing.php");
    exit;
}

// Fetch all pricing
$stmt = $pdo->query("SELECT * FROM pricing ORDER BY duration_minutes ASC");
$pricingList = $stmt->fetchAll();
?>

<?php displayFlash(); ?>

<div class="admin-card">
    <div class="card-header">
        <h3>Add New Pricing Tier</h3>
    </div>
    <div class="card-body">
        <form method="POST" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; align-items: end;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add">
            
            <div class="form-group" style="margin:0;">
                <label class="form-label">Duration (min)</label>
                <input type="number" name="duration_minutes" class="form-control" placeholder="60" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Price (LKR)</label>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="1500" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Overstay Rate/min</label>
                <input type="number" step="0.01" name="extra_per_minute" class="form-control" placeholder="25" value="25" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control" placeholder="60 Minutes - Regular" required>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary btn-block">Add</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3>Current Pricing Tiers</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Overstay Rate</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricingList as $p): ?>
                    <tr>
                        <td><?php echo $p['duration_minutes']; ?> min</td>
                        <td><?php echo formatCurrency($p['price']); ?></td>
                        <td><?php echo formatCurrency($p['extra_per_minute']); ?>/min</td>
                        <td><?php echo sanitize($p['label']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $p['is_active'] ? 'active' : 'expired'; ?>">
                                <?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="pricing_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8rem;">
                                    <?php echo $p['is_active'] ? 'Disable' : 'Enable'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
