<?php
/**
 * Admin - SMS Logs
 * World Play QR Ticketing System
 */

$adminPageTitle = 'SMS Logs';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Filters
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

$where = "1=1";
$params = [];

if ($typeFilter) {
    $where .= " AND type = :type";
    $params[':type'] = $typeFilter;
}
if ($statusFilter) {
    $where .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sms_logs WHERE {$where}");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare(
    "SELECT * FROM sms_logs WHERE {$where} ORDER BY sent_at DESC LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <h3>SMS History (<?php echo $total; ?> total)</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <select name="type">
                <option value="">All Types</option>
                <option value="purchase" <?php echo $typeFilter === 'purchase' ? 'selected' : ''; ?>>Purchase</option>
                <option value="activation" <?php echo $typeFilter === 'activation' ? 'selected' : ''; ?>>Activation</option>
                <option value="reminder_10min" <?php echo $typeFilter === 'reminder_10min' ? 'selected' : ''; ?>>10-min Reminder</option>
                <option value="reminder_5min" <?php echo $typeFilter === 'reminder_5min' ? 'selected' : ''; ?>>5-min Reminder</option>
                <option value="expiry" <?php echo $typeFilter === 'expiry' ? 'selected' : ''; ?>>Expiry</option>
                <option value="exit" <?php echo $typeFilter === 'exit' ? 'selected' : ''; ?>>Exit</option>
            </select>
            <select name="status">
                <option value="">All Statuses</option>
                <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Sent</option>
                <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                <option value="queued" <?php echo $statusFilter === 'queued' ? 'selected' : ''; ?>>Queued</option>
            </select>
            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Filter</button>
            <a href="<?php echo BASE_URL; ?>/admin/sms-logs.php" class="btn btn-outline" style="padding: 8px 16px;">Reset</a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Error</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><strong><?php echo sanitize($log['ticket_id']); ?></strong></td>
                        <td><?php echo sanitize($log['phone']); ?></td>
                        <td><span class="badge badge-completed"><?php echo sanitize($log['type']); ?></span></td>
                        <td>
                            <span class="badge badge-<?php echo $log['status'] === 'sent' ? 'active' : ($log['status'] === 'failed' ? 'expired' : 'pending'); ?>">
                                <?php echo ucfirst($log['status']); ?>
                            </span>
                        </td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                            title="<?php echo sanitize($log['message']); ?>">
                            <?php echo sanitize(substr($log['message'], 0, 50)); ?>...
                        </td>
                        <td style="max-width: 200px; color: <?php echo $log['error_message'] ? '#e74c3c' : '#95a5a6'; ?>;" 
                            title="<?php echo sanitize($log['error_message'] ?? ''); ?>">
                            <?php echo $log['error_message'] ? sanitize(substr($log['error_message'], 0, 40)) . '...' : '-'; ?>
                        </td>
                        <td><?php echo date('M d, g:i A', strtotime($log['sent_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center" style="padding:30px; color:var(--text-muted);">No SMS logs</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): 
                $queryParams = $_GET;
                $queryParams['page'] = $i;
            ?>
                <a href="?<?php echo http_build_query($queryParams); ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
