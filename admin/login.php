<?php
/**
 * Admin Login
 * World Play QR Ticketing System
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "/admin/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $pdo = getDBConnection();
        $result = authenticateAdmin($pdo, $username, $password);
        
        if ($result === true) {
            header("Location: " . BASE_URL . "/admin/index.php");
            exit;
        } elseif ($result === 'locked') {
            $error = 'Account is locked due to too many failed attempts. Please try again in 15 minutes.';
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/scanner.css">
</head>
<body>
<div class="staff-login">
    <div class="card">
        <h2 style="text-align:center; color: var(--primary);">Admin Login</h2>
        <p class="text-center" style="color: var(--text-muted);">World Play Dashboard</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
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
</body>
</html>
