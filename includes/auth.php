<?php
/**
 * Auth Middleware
 * World Play QR Ticketing System
 */

/**
 * Check if admin is logged in
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}

/**
 * Check if staff is logged in
 */
function requireStaff() {
    if (!isset($_SESSION['staff_id'])) {
        return false;
    }
    return true;
}

/**
 * Get current staff ID
 */
function getCurrentStaffId() {
    return $_SESSION['staff_id'] ?? null;
}

/**
 * Get current admin info
 */
function getCurrentAdmin() {
    if (isset($_SESSION['admin_id'])) {
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'full_name' => $_SESSION['admin_full_name'],
            'role' => $_SESSION['admin_role']
        ];
    }
    return null;
}

/**
 * Staff login authentication
 */
function authenticateStaff($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE username = :username AND is_active = 1");
    $stmt->execute([':username' => $username]);
    $staff = $stmt->fetch();
    
    if ($staff && password_verify($password, $staff['password_hash'])) {
        $_SESSION['staff_id'] = $staff['id'];
        $_SESSION['staff_name'] = $staff['name'];
        $_SESSION['staff_role'] = $staff['role'];
        return true;
    }
    return false;
}

/**
 * Admin login authentication with lockout
 */
function authenticateAdmin($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        return false;
    }
    
    // Check if account is locked
    if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
        return 'locked';
    }
    
    if (password_verify($password, $admin['password_hash'])) {
        // Reset failed attempts
        $stmt = $pdo->prepare("UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id");
        $stmt->execute([':id' => $admin['id']]);
        
        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_full_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        session_regenerate_id(true);
        
        return true;
    } else {
        // Increment failed attempts
        $attempts = $admin['failed_attempts'] + 1;
        $lockUntil = null;
        
        if ($attempts >= 5) {
            $lockUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        }
        
        $stmt = $pdo->prepare("UPDATE admins SET failed_attempts = :attempts, locked_until = :lock WHERE id = :id");
        $stmt->execute([':attempts' => $attempts, ':lock' => $lockUntil, ':id' => $admin['id']]);
        
        return false;
    }
}
