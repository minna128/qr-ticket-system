<?php
/**
 * Admin Dashboard - Home
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Get stats
$activeSessions = getActiveSessionsCount($pdo);
$todayVisitors = getTodayVisitors($pdo);
$todayRevenue = getTodayRevenue($pdo);

// Expired/overdue
$stmt = $pdo->query(
    "SELECT COUNT(*) FROM tickets t 
     JOIN sessions s ON t.ticket_id = s.ticket_id 
     WHERE t.status IN ('active','expired') 
     AND s.expected_exit_time < NOW() 
     AND s.exit_time IS NULL"
);
$expiredCount = $stmt->fetchColumn();

// Recent activity
$stmt = $pdo->query(
    "SELECT t.ticket_id, t.status, t.duration_minutes, t.created_at, 
            s.entry_time, s.exit_time
     FROM tickets t
     LEFT JOIN sessions s ON t.ticket_id = s.ticket_id
     ORDER BY t.created_at DESC
     LIMIT 10"
);
$recentActivity = $stmt->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card stat-active">
        <div class="stat-icon">&#9989;</div>
        <div class="stat-value"><?php echo $activeSessions; ?></div>
        <div class="stat-label">Active Sessions</div>
    </div>
    <div class="stat-card stat-visitors">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-value"><?php echo $todayVisitors; ?></div>
        <div class="stat-label">Today's Visitors</div>
    </div>
    <div class="stat-card stat-revenue">
        <div class="stat-icon">&#128176;</div>
        <div class="stat-value"><?php echo formatCurrency($todayRevenue); ?></div>
        <div class="stat-label">Today's Revenue</div>
    </div>
    <div class="stat-card stat-expired">
        <div class="stat-icon">&#9888;</div>
        <div class="stat-value"><?php echo $expiredCount; ?></div>
        <div class="stat-label">Overdue Exits</div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3>Recent Activity</h3>
        <a href="<?php echo BASE_URL; ?>/admin/transactions.php" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.85rem;">View All</a>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Entry Time</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentActivity as $row): ?>
                    <tr>
                        <td><strong><?php echo sanitize($row['ticket_id']); ?></strong></td>
                        <td><?php echo $row['duration_minutes']; ?> min</td>
                        <td>
                            <span class="badge badge-<?php echo $row['status'] === 'active' ? 'active' : ($row['status'] === 'completed' ? 'completed' : ($row['status'] === 'expired' ? 'expired' : 'pending')); ?>">
                                <?php echo ucfirst(sanitize($row['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $row['entry_time'] ? date('g:i A', strtotime($row['entry_time'])) : '-'; ?></td>
                        <td><?php echo date('M d, g:i A', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentActivity)): ?>
                    <tr><td colspan="5" class="text-center" style="padding:30px; color:var(--text-muted);">No activity yet</td></tr>
                    <?php endif; ?>
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
