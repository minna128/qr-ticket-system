<?php
/**
 * Admin - Revenue Reports
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Revenue Reports';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Get report data
$period = $_GET['period'] ?? 'daily';

// Daily revenue for last 30 days
$stmt = $pdo->query(
    "SELECT DATE(created_at) as date, SUM(amount) as total, 
            SUM(CASE WHEN payment_type = 'initial' THEN amount ELSE 0 END) as initial_total,
            SUM(CASE WHEN payment_type = 'overstay' THEN amount ELSE 0 END) as overstay_total,
            COUNT(*) as transaction_count
     FROM payments 
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY DATE(created_at) 
     ORDER BY date DESC"
);
$dailyData = $stmt->fetchAll();

// Summary stats
$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(created_at) = CURDATE()");
$todayRev = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())");
$weekRev = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$monthRev = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments");
$totalRev = $stmt->fetchColumn();

// Prepare chart data
$chartLabels = [];
$chartInitial = [];
$chartOverstay = [];
foreach (array_reverse($dailyData) as $day) {
    $chartLabels[] = date('M d', strtotime($day['date']));
    $chartInitial[] = (float)$day['initial_total'];
    $chartOverstay[] = (float)$day['overstay_total'];
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Today</div>
        <div class="stat-value" style="color: var(--secondary); font-size:1.5rem;"><?php echo formatCurrency($todayRev); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">This Week</div>
        <div class="stat-value" style="color: var(--primary); font-size:1.5rem;"><?php echo formatCurrency($weekRev); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">This Month</div>
        <div class="stat-value" style="color: var(--success); font-size:1.5rem;"><?php echo formatCurrency($monthRev); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">All Time</div>
        <div class="stat-value" style="color: var(--warning); font-size:1.5rem;"><?php echo formatCurrency($totalRev); ?></div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3>Revenue Chart (Last 30 Days)</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="card-header">
        <h3>Daily Breakdown</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Initial Revenue</th>
                        <th>Overstay Revenue</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyData as $day): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                        <td><?php echo $day['transaction_count']; ?></td>
                        <td><?php echo formatCurrency($day['initial_total']); ?></td>
                        <td style="color: var(--warning);"><?php echo formatCurrency($day['overstay_total']); ?></td>
                        <td><strong><?php echo formatCurrency($day['total']); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($dailyData)): ?>
                    <tr><td colspan="5" class="text-center" style="padding:30px; color:var(--text-muted);">No revenue data yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [
            {
                label: 'Ticket Sales',
                data: <?php echo json_encode($chartInitial); ?>,
                backgroundColor: 'rgba(108, 99, 255, 0.7)',
                borderRadius: 4
            },
            {
                label: 'Overstay Charges',
                data: <?php echo json_encode($chartOverstay); ?>,
                backgroundColor: 'rgba(255, 165, 2, 0.7)',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#a0a0b8' } }
        },
        scales: {
            x: { 
                stacked: true,
                ticks: { color: '#a0a0b8' },
                grid: { color: 'rgba(45, 45, 80, 0.5)' }
            },
            y: { 
                stacked: true,
                ticks: { color: '#a0a0b8', callback: v => 'LKR ' + v.toLocaleString() },
                grid: { color: 'rgba(45, 45, 80, 0.5)' }
            }
        }
    }
});
</script>

</main>
</div>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>
