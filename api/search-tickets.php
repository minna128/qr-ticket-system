<?php
/**
 * API - Search Tickets
 * World Play QR Ticketing System
 * 
 * Search tickets by ID or phone number
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$query = trim($_GET['q'] ?? '');

if (empty($query) || strlen($query) < 3) {
    jsonResponse(['results' => [], 'error' => 'Query too short (min 3 characters)']);
}

$pdo = getDBConnection();

$stmt = $pdo->prepare(
    "SELECT t.ticket_id, t.phone, t.duration_minutes, t.status, t.payment_method, t.created_at,
            s.entry_time, s.exit_time, s.overstay_minutes, s.extra_charge
     FROM tickets t
     LEFT JOIN sessions s ON t.ticket_id = s.ticket_id
     WHERE t.ticket_id LIKE :query OR t.phone LIKE :query2
     ORDER BY t.created_at DESC
     LIMIT 20"
);
$stmt->execute([':query' => "%{$query}%", ':query2' => "%{$query}%"]);
$results = $stmt->fetchAll();

jsonResponse(['results' => $results, 'count' => count($results)]);
