<?php
/**
 * API - Dashboard Stats
 * World Play QR Ticketing System
 * 
 * Returns JSON stats for AJAX dashboard polling
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();

$activeSessions = getActiveSessionsCount($pdo);
$todayVisitors = getTodayVisitors($pdo);
$todayRevenue = getTodayRevenue($pdo);

$stmt = $pdo->query(
    "SELECT COUNT(*) FROM tickets t 
     JOIN sessions s ON t.ticket_id = s.ticket_id 
     WHERE t.status IN ('active','expired') 
     AND s.expected_exit_time < NOW() 
     AND s.exit_time IS NULL"
);
$expiredCount = $stmt->fetchColumn();

jsonResponse([
    'active_sessions' => (int)$activeSessions,
    'today_visitors' => (int)$todayVisitors,
    'today_revenue' => (float)$todayRevenue,
    'today_revenue_formatted' => formatCurrency($todayRevenue),
    'expired_count' => (int)$expiredCount
]);
