<?php
/**
 * Admin - Expired Tickets
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Expired Tickets';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

$stmt = $pdo->query(
    "SELECT t.ticket_id, t.phone, t.duration_minutes, t.status,
            s.entry_time, s.expected_exit_time,
            TIMESTAMPDIFF(MINUTE, s.expected_exit_time, NOW()) as overdue_minutes
     FROM tickets t
     JOIN sessions s ON t.ticket_id = s.ticket_id
     WHERE t.status IN ('active', 'expired')
     AND s.expected_exit_time < NOW()
     AND s.exit_time IS NULL
     ORDER BY s.expected_exit_time ASC"
);
$tickets = $stmt->fetchAll();
?>

<div class="admin-card">
    <div class="card-header">
        <h3>Expired/Overdue Tickets (<?php echo count($tickets); ?>)</h3>
        <button onclick="location.reload()" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.85rem;">Refresh</button>
    </div>
    <div class="card-body">
        <?php if (count($tickets) > 0): ?>
        <div class="alert alert-warning">
            These visitors have exceeded their session time and have not exited yet.
        </div>
        <?php endif; ?>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Phone</th>
                        <th>Duration</th>
                        <th>Entry Time</th>
                        <th>Expired At</th>
                        <th>Overdue By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td><strong><?php echo sanitize($ticket['ticket_id']); ?></strong></td>
                        <td><?php echo sanitize(substr($ticket['phone'], 0, -4) . '****'); ?></td>
                        <td><?php echo $ticket['duration_minutes']; ?> min</td>
                        <td><?php echo date('g:i A', strtotime($ticket['entry_time'])); ?></td>
                        <td><?php echo date('g:i A', strtotime($ticket['expected_exit_time'])); ?></td>
                        <td style="color: var(--danger); font-weight: 700;">
                            <?php echo $ticket['overdue_minutes']; ?> min
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tickets)): ?>
                    <tr><td colspan="6" class="text-center" style="padding:30px; color:var(--text-muted);">No expired tickets - all clear!</td></tr>
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
