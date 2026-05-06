<?php
/**
 * API - Active Sessions List
 * World Play QR Ticketing System
 * 
 * Returns JSON of active sessions for dashboard polling
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

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

$result = [];
foreach ($sessions as $session) {
    $remaining = strtotime($session['expected_exit_time']) - time();
    $result[] = [
        'ticket_id' => $session['ticket_id'],
        'phone' => substr($session['phone'], 0, -4) . '****',
        'duration_minutes' => $session['duration_minutes'],
        'entry_time' => date('g:i A', strtotime($session['entry_time'])),
        'expected_exit' => date('g:i A', strtotime($session['expected_exit_time'])),
        'remaining_seconds' => $remaining,
        'remaining_text' => $remaining > 0 ? intval($remaining / 60) . ' min' : 'OVERDUE (' . abs(intval($remaining / 60)) . ' min)',
        'is_overdue' => $remaining < 0
    ];
}

jsonResponse(['sessions' => $result, 'count' => count($result)]);
