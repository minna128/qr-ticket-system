<?php
/**
 * Admin Sidebar Navigation
 * World Play QR Ticketing System
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h3>World Play</h3>
        <small>Admin Dashboard</small>
    </div>
    
    <nav>
        <div class="nav-section">Overview</div>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128200;</span> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/active-sessions.php" class="nav-item <?php echo $currentPage === 'active-sessions' ? 'active' : ''; ?>">
            <span class="nav-icon">&#9989;</span> Active Sessions
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/expired-tickets.php" class="nav-item <?php echo $currentPage === 'expired-tickets' ? 'active' : ''; ?>">
            <span class="nav-icon">&#9888;</span> Expired Tickets
        </a>
        
        <div class="nav-section">Finance</div>
        <a href="<?php echo BASE_URL; ?>/admin/transactions.php" class="nav-item <?php echo $currentPage === 'transactions' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128176;</span> Transactions
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128202;</span> Reports
        </a>
        
        <div class="nav-section">Management</div>
        <a href="<?php echo BASE_URL; ?>/admin/pricing.php" class="nav-item <?php echo $currentPage === 'pricing' ? 'active' : ''; ?>">
            <span class="nav-icon">&#127991;</span> Pricing
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/staff.php" class="nav-item <?php echo $currentPage === 'staff' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128101;</span> Staff
        </a>
        
        <div class="nav-section">System</div>
        <a href="<?php echo BASE_URL; ?>/admin/sms-logs.php" class="nav-item <?php echo $currentPage === 'sms-logs' ? 'active' : ''; ?>">
            <span class="nav-icon">&#128172;</span> SMS Logs
        </a>
        <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="nav-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">&#9881;</span> Settings
        </a>
        
        <div class="nav-section"></div>
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="nav-item">
            <span class="nav-icon">&#128682;</span> Logout
        </a>
    </nav>
</aside>
