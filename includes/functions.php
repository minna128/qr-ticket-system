<?php
/**
 * Utility Functions
 * World Play QR Ticketing System
 */

/**
 * Generate a unique ticket ID in format WP-XXXXXX
 */
function generateTicketId($pdo) {
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; // Exclude ambiguous: O,0,I,1,L
    
    do {
        $id = 'WP-';
        for ($i = 0; $i < 6; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_id = :id");
        $stmt->execute([':id' => $id]);
    } while ($stmt->fetchColumn() > 0);
    
    return $id;
}

/**
 * Format currency amount
 */
function formatCurrency($amount) {
    return 'LKR ' . number_format((float)$amount, 2);
}

/**
 * Normalize Sri Lankan phone number to +94 format
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    if (strpos($phone, '+94') === 0) {
        return $phone;
    }
    if (strpos($phone, '94') === 0 && strlen($phone) === 11) {
        return '+' . $phone;
    }
    if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
        return '+94' . substr($phone, 1);
    }
    
    return '+94' . $phone;
}

/**
 * Normalize Sri Lankan phone number to text.lk format (94XXXXXXXXX without +)
 */
function formatPhoneForTextLK($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Remove leading + if present
    $phone = ltrim($phone, '+');
    
    // If starts with 94 and is 11 digits, return as is
    if (strpos($phone, '94') === 0 && strlen($phone) === 11) {
        return $phone;
    }
    
    // If starts with 0 (local format like 0712345678), replace with 94
    if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
        return '94' . substr($phone, 1);
    }
    
    // If just 9 digits (local number without leading 0), add 94
    if (strlen($phone) === 9) {
        return '94' . $phone;
    }
    
    return $phone;
}

/**
 * Validate Sri Lankan phone number
 */
function validatePhone($phone) {
    $normalized = formatPhone($phone);
    return preg_match('/^\+947[01245678]\d{7}$/', $normalized) === 1;
}

/**
 * Calculate overstay minutes with grace period
 */
function calculateOverstay($expectedExit, $actualExit, $graceMins = 2) {
    $expected = new DateTime($expectedExit);
    $actual = new DateTime($actualExit);
    
    $diff = $actual->getTimestamp() - $expected->getTimestamp();
    $overstayMinutes = (int)ceil($diff / 60);
    
    // Apply grace period
    $billable = max(0, $overstayMinutes - $graceMins);
    
    return $billable;
}

/**
 * Calculate extra charge for overstay
 */
function calculateExtraCharge($overstayMinutes, $extraPerMinute) {
    return round($overstayMinutes * $extraPerMinute, 2);
}

/**
 * Get a setting value from database
 */
function getSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key");
    $stmt->execute([':key' => $key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden field for forms
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Sanitize output for XSS prevention
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = sanitize($flash['type']);
        $message = sanitize($flash['message']);
        echo "<div class='alert alert-{$type}'>{$message}</div>";
    }
}

/**
 * Send JSON response and exit
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Check rate limit: tickets per phone per hour
 */
function checkPhoneRateLimit($pdo, $phone, $maxPerHour = 5) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM tickets WHERE phone = :phone AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $stmt->execute([':phone' => $phone]);
    return $stmt->fetchColumn() < $maxPerHour;
}

/**
 * Validate ticket ID format
 */
function validateTicketId($ticketId) {
    return preg_match('/^WP-[A-Z0-9]{6}$/', $ticketId) === 1;
}

/**
 * Get active sessions count
 */
function getActiveSessionsCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'active'");
    return $stmt->fetchColumn();
}

/**
 * Get today's revenue
 */
function getTodayRevenue($pdo) {
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(created_at) = CURDATE()");
    return $stmt->fetchColumn();
}

/**
 * Get today's visitor count
 */
function getTodayVisitors($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM sessions WHERE DATE(entry_time) = CURDATE()");
    return $stmt->fetchColumn();
}

/**
 * Get selected games summary (count, total price, games array)
 * Centralises duplicated game calculation logic from kiosk pages
 */
function getSelectedGamesSummary($pdo, $gameIdsString) {
    $result = ['total' => 0.0, 'count' => 0, 'games' => []];
    if (empty($gameIdsString)) { return $result; }
    $gameIds = array_filter(array_map('intval', explode(',', $gameIdsString)));
    if (empty($gameIds)) { return $result; }
    $placeholders = implode(',', array_fill(0, count($gameIds), '?'));
    $stmt = $pdo->prepare(
        "SELECT id, name, price FROM games WHERE id IN ({$placeholders}) AND is_active = 1"
    );
    $stmt->execute(array_values($gameIds));
    $games = $stmt->fetchAll();
    $result['games'] = $games;
    $result['count'] = count($games);
    $result['total'] = (float) array_sum(array_column($games, 'price'));
    return $result;
}
