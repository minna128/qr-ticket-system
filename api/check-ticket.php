<?php
/**
 * API - Check Ticket Status
 * World Play QR Ticketing System
 * 
 * GET: Validates a ticket ID and returns its status
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$ticketId = trim($_GET['ticket_id'] ?? '');

if (empty($ticketId) || !validateTicketId($ticketId)) {
    jsonResponse(['valid' => false, 'error' => 'Invalid ticket ID format. Expected: WP-XXXXXX'], 400);
}

$pdo = getDBConnection();

$stmt = $pdo->prepare(
    "SELECT t.*, p.label as plan_label, p.extra_per_minute 
     FROM tickets t 
     JOIN pricing p ON t.pricing_id = p.id 
     WHERE t.ticket_id = :ticket_id"
);
$stmt->execute([':ticket_id' => $ticketId]);
$ticket = $stmt->fetch();

if (!$ticket) {
    jsonResponse(['valid' => false, 'error' => 'Ticket not found. Please check the ticket ID.'], 404);
}

// Check status
switch ($ticket['status']) {
    case 'not_activated':
        if ($ticket['payment_status'] !== 'confirmed') {
            jsonResponse(['valid' => false, 'error' => 'Payment not confirmed for this ticket.']);
        }
        jsonResponse([
            'valid' => true,
            'ticket' => [
                'ticket_id' => $ticket['ticket_id'],
                'duration_minutes' => $ticket['duration_minutes'],
                'payment_method' => $ticket['payment_method'],
                'status' => $ticket['status'],
                'plan_label' => $ticket['plan_label'],
                'created_at' => $ticket['created_at']
            ]
        ]);
        break;
        
    case 'active':
        // Get session info
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE ticket_id = :ticket_id");
        $stmt->execute([':ticket_id' => $ticketId]);
        $session = $stmt->fetch();
        
        jsonResponse([
            'valid' => false, 
            'error' => 'Ticket is already active. Entry time: ' . ($session ? $session['entry_time'] : 'Unknown'),
            'status' => 'active'
        ]);
        break;
        
    case 'expired':
        jsonResponse(['valid' => false, 'error' => 'This ticket has expired.', 'status' => 'expired']);
        break;
        
    case 'completed':
        jsonResponse(['valid' => false, 'error' => 'This ticket has already been used and completed.', 'status' => 'completed']);
        break;
        
    default:
        jsonResponse(['valid' => false, 'error' => 'Unknown ticket status.']);
}
