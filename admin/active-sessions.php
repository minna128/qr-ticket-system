<?php
/**
 * Admin - Active Sessions
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Active Sessions';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

$stmt = $pdo->query(
    "SELECT t.ticket_id, t.phone, t.duration_minutes, t.status,
            s.entry_time, s.expected_exit_time
     FROM tickets t
     JOIN sessions s ON t.ticket_id = s.ticket_id
     WHERE t.status IN ('active', 'expired')
     AND s.exit_time IS NULL
     ORDER BY s.expected_exit_time ASC"
);
$sessions = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <h3>Active Sessions (<?php echo count($sessions); ?>)</h3>
        <button onclick="location.reload()" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.85rem;">Refresh</button>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Phone</th>
                        <th>Duration</th>
                        <th>Entry Time</th>
                        <th>Expected Exit</th>
                        <th>Time Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): 
                        $remaining = strtotime($session['expected_exit_time']) - time();
                        $isOverdue = $remaining < 0;
                    ?>
                    <tr>
                        <td><strong><?php echo sanitize($session['ticket_id']); ?></strong></td>
                        <td><?php echo sanitize(substr($session['phone'], 0, -4) . '****'); ?></td>
                        <td><?php echo $session['duration_minutes']; ?> min</td>
                        <td><?php echo date('g:i A', strtotime($session['entry_time'])); ?></td>
                        <td><?php echo date('g:i A', strtotime($session['expected_exit_time'])); ?></td>
                        <td>
                            <?php if ($isOverdue): ?>
                                <span style="color: var(--danger); font-weight: 700;">
                                    OVERDUE (<?php echo abs(intval($remaining / 60)); ?> min)
                                </span>
                            <?php elseif ($remaining <= 300): ?>
                                <span style="color: var(--danger);"><?php echo intval($remaining / 60); ?> min</span>
                            <?php elseif ($remaining <= 600): ?>
                                <span style="color: var(--warning);"><?php echo intval($remaining / 60); ?> min</span>
                            <?php else: ?>
                                <span style="color: var(--success);"><?php echo intval($remaining / 60); ?> min</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $isOverdue ? 'expired' : 'active'; ?>">
                                <?php echo $isOverdue ? 'Overdue' : 'Active'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($sessions)): ?>
                    <tr><td colspan="7" class="text-center" style="padding:30px; color:var(--text-muted);">No active sessions</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
setTimeout(function() { location.reload(); }, 30000);
</script>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
