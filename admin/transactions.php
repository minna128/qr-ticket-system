<?php
/**
 * Admin - Transactions
 * World Play QR Ticketing System
 */

$adminPageTitle = 'Transactions';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = getDBConnection();

// Filters
$search = trim($_GET['search'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = "1=1";
$params = [];

if ($search) {
    $where .= " AND (p.ticket_id LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($typeFilter && in_array($typeFilter, ['initial', 'overstay'])) {
    $where .= " AND p.payment_type = :type";
    $params[':type'] = $typeFilter;
}
if ($dateFrom) {
    $where .= " AND DATE(p.created_at) >= :date_from";
    $params[':date_from'] = $dateFrom;
}
if ($dateTo) {
    $where .= " AND DATE(p.created_at) <= :date_to";
    $params[':date_to'] = $dateTo;
}

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM payments p WHERE {$where}");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Fetch records
$stmt = $pdo->prepare(
    "SELECT p.*, t.phone, t.duration_minutes 
     FROM payments p
     JOIN tickets t ON p.ticket_id = t.ticket_id
     WHERE {$where}
     ORDER BY p.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Total revenue
$totalStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments p WHERE {$where}");
$totalStmt->execute($params);
$filteredRevenue = $totalStmt->fetchColumn();
?>

<div class="admin-card">
    <div class="card-header">
        <h3>Payment History</h3>
        <span style="color: var(--secondary); font-weight: 600;">Total: <?php echo formatCurrency($filteredRevenue); ?></span>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" placeholder="Search ticket ID..." value="<?php echo sanitize($search); ?>">
            <select name="type">
                <option value="">All Types</option>
                <option value="initial" <?php echo $typeFilter === 'initial' ? 'selected' : ''; ?>>Initial</option>
                <option value="overstay" <?php echo $typeFilter === 'overstay' ? 'selected' : ''; ?>>Overstay</option>
            </select>
            <input type="date" name="date_from" value="<?php echo sanitize($dateFrom); ?>" placeholder="From">
            <input type="date" name="date_to" value="<?php echo sanitize($dateTo); ?>" placeholder="To">
            <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Filter</button>
            <a href="<?php echo BASE_URL; ?>/admin/transactions.php" class="btn btn-outline" style="padding: 8px 16px;">Reset</a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ticket</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>#<?php echo $t['id']; ?></td>
                        <td><strong><?php echo sanitize($t['ticket_id']); ?></strong></td>
                        <td><?php echo formatCurrency($t['amount']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $t['payment_type'] === 'overstay' ? 'expired' : 'completed'; ?>">
                                <?php echo ucfirst($t['payment_type']); ?>
                            </span>
                        </td>
                        <td><?php echo sanitize($t['payment_method']); ?></td>
                        <td><?php echo date('M d, Y g:i A', strtotime($t['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" class="text-center" style="padding:30px; color:var(--text-muted);">No transactions found</td></tr>
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
