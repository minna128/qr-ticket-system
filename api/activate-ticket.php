<?php
/**
 * API - Activate Ticket (Entry)
 * World Play QR Ticketing System
 * 
 * POST: Activates a ticket and starts the session timer
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/sms.php';
require_once __DIR__ . '/../includes/supervisor.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Parse input
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$ticketId = trim($input['ticket_id'] ?? '');

if (empty($ticketId) || !validateTicketId($ticketId)) {
    jsonResponse(['success' => false, 'error' => 'Invalid ticket ID format.'], 400);
}

$pdo = getDBConnection();

try {
    $pdo->beginTransaction();
    
    // Lock and fetch ticket
    $stmt = $pdo->prepare(
        "SELECT t.*, p.extra_per_minute 
         FROM tickets t 
         JOIN pricing p ON t.pricing_id = p.id 
         WHERE t.ticket_id = :ticket_id 
         FOR UPDATE"
    );
    $stmt->execute([':ticket_id' => $ticketId]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'error' => 'Ticket not found.'], 404);
    }
    
    if ($ticket['status'] !== 'not_activated') {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'error' => 'Ticket cannot be activated. Current status: ' . $ticket['status']]);
    }
    
    if ($ticket['payment_status'] !== 'confirmed') {
        $pdo->rollBack();
        jsonResponse(['success' => false, 'error' => 'Payment not confirmed.']);
    }
    
    // Calculate times
    $entryTime = date('Y-m-d H:i:s');
    $expectedExit = date('Y-m-d H:i:s', strtotime("+{$ticket['duration_minutes']} minutes"));
    
    $staffId = $_SESSION['staff_id'] ?? null;
    
    // Create session record
    $stmt = $pdo->prepare(
        "INSERT INTO sessions (ticket_id, entry_time, expected_exit_time, staff_entry_id)
         VALUES (:ticket_id, :entry_time, :expected_exit, :staff_id)"
    );
    $stmt->execute([
        ':ticket_id' => $ticketId,
        ':entry_time' => $entryTime,
        ':expected_exit' => $expectedExit,
        ':staff_id' => $staffId
    ]);
    
    // Update ticket status
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'active' WHERE ticket_id = :ticket_id");
    $stmt->execute([':ticket_id' => $ticketId]);
    
    $pdo->commit();
    
    // Send activation SMS
    $exitFormatted = date('g:i A', strtotime($expectedExit));
    $smsMessage = "Session Started!\n"
        . "Ticket: {$ticketId}\n"
        . "Started: " . date('g:i A', strtotime($entryTime)) . "\n"
        . "Ends at: {$exitFormatted}\n"
        . "Duration: {$ticket['duration_minutes']} min.\n"
        . "Enjoy your time at World Play!";
    
    sendSMS($ticket['phone'], $smsMessage, $ticketId, 'activation');
    
    jsonResponse([
        'success' => true,
        'session' => [
            'ticket_id' => $ticketId,
            'entry_time' => date('g:i A', strtotime($entryTime)),
            'expected_exit_time' => $exitFormatted,
            'duration_minutes' => $ticket['duration_minutes']
        ]
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Activation failed for {$ticketId}: " . $e->getMessage());
    sendSupervisorAlert($pdo, "Activation API error.\n" . $e->getMessage(), $ticketId ?: null, 'system_error');
    jsonResponse(['success' => false, 'error' => 'System error. Please try again.'], 500);
}
