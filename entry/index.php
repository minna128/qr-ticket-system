<?php
/**
 * Entry Gate - Staff Login / Landing
 * World Play QR Ticketing System
 */

$pageTitle = 'Entry Gate';
$bodyClass = 'scanner-page';
$extraCSS = ['scanner.css'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDBConnection();

// Handle staff login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request. Please try again.');
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (authenticateStaff($pdo, $username, $password)) {
            header("Location: " . BASE_URL . "/entry/scan.php");
            exit;
        } else {
            setFlash('danger', 'Invalid username or password.');
        }
    }
}

// If already logged in, go to scanner
if (requireStaff()) {
    header("Location: " . BASE_URL . "/entry/scan.php");
    exit;
}
?>

<div class="staff-login">
    <div class="card">
        <h2>Entry Gate Login</h2>
        <p class="text-center" style="color: var(--text-muted);">Staff authentication required</p>
        
        <?php displayFlash(); ?>
        
        <form method="POST">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="<?php echo BASE_URL; ?>/">Back to Home</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
