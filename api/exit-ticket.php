<?php
/**
 * API - Exit Ticket (Process Exit + Overstay)
 * World Play QR Ticketing System
 * 
 * GET: Calculate overstay and return session summary
 * POST: Complete the exit and close the session
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/sms.php';
require_once __DIR__ . '/../includes/supervisor.php';

$pdo = getDBConnection();

// GET: Calculate overstay preview
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ticketId = trim($_GET['ticket_id'] ?? '');
    
    if (empty($ticketId) || !validateTicketId($ticketId)) {
        jsonResponse(['success' => false, 'error' => 'Invalid ticket ID format.'], 400);
    }
    
    // Get ticket and session
    $stmt = $pdo->prepare(
        "SELECT t.*, s.entry_time, s.expected_exit_time, p.extra_per_minute
         FROM tickets t
         JOIN sessions s ON t.ticket_id = s.ticket_id
         JOIN pricing p ON t.pricing_id = p.id
         WHERE t.ticket_id = :ticket_id"
    );
    $stmt->execute([':ticket_id' => $ticketId]);
    $data = $stmt->fetch();
    
    if (!$data) {
        jsonResponse(['success' => false, 'error' => 'Ticket not found or no active session.'], 404);
    }
    
    if ($data['status'] !== 'active' && $data['status'] !== 'expired') {
        jsonResponse(['success' => false, 'error' => 'Ticket is not currently active. Status: ' . $data['status']]);
    }
    
    // Calculate overstay
    $now = date('Y-m-d H:i:s');
    $graceMins = (int)(getSetting($pdo, 'overstay_grace_minutes') ?? 2);
    $overstayMinutes = calculateOverstay($data['expected_exit_time'], $now, $graceMins);
    $extraCharge = calculateExtraCharge($overstayMinutes, $data['extra_per_minute']);
    
    // Actual duration
    $entryTimestamp = strtotime($data['entry_time']);
    $actualDuration = (int)ceil((time() - $entryTimestamp) / 60);
    
    jsonResponse([
        'success' => true,
        'ticket_id' => $ticketId,
        'entry_time' => date('g:i A', strtotime($data['entry_time'])),
        'expected_exit_time' => date('g:i A', strtotime($data['expected_exit_time'])),
        'actual_exit_time' => date('g:i A'),
        'duration_minutes' => $data['duration_minutes'],
        'actual_duration' => $actualDuration,
        'overstay_minutes' => $overstayMinutes,
        'extra_charge' => $extraCharge,
        'extra_charge_formatted' => formatCurrency($extraCharge),
        'extra_per_minute' => $data['extra_per_minute']
    ]);
}

// POST: Complete exit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST;
    if (empty($input)) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    $ticketId = trim($input['ticket_id'] ?? '');
    $action = trim($input['action'] ?? '');
    
    if (empty($ticketId) || !validateTicketId($ticketId) || $action !== 'complete') {
        jsonResponse(['success' => false, 'error' => 'Invalid request.'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        
        // Lock and fetch
        $stmt = $pdo->prepare(
            "SELECT t.*, s.id as session_id, s.entry_time, s.expected_exit_time, p.extra_per_minute
             FROM tickets t
             JOIN sessions s ON t.ticket_id = s.ticket_id
             JOIN pricing p ON t.pricing_id = p.id
             WHERE t.ticket_id = :ticket_id
             FOR UPDATE"
        );
        $stmt->execute([':ticket_id' => $ticketId]);
        $data = $stmt->fetch();
        
        if (!$data || ($data['status'] !== 'active' && $data['status'] !== 'expired')) {
            $pdo->rollBack();
            jsonResponse(['success' => false, 'error' => 'Cannot process exit for this ticket.']);
        }
        
        $exitTime = date('Y-m-d H:i:s');
        $graceMins = (int)(getSetting($pdo, 'overstay_grace_minutes') ?? 2);
        $overstayMinutes = calculateOverstay($data['expected_exit_time'], $exitTime, $graceMins);
        $extraCharge = calculateExtraCharge($overstayMinutes, $data['extra_per_minute']);
        
        $staffId = $_SESSION['staff_id'] ?? null;
        
        // Update session
        $stmt = $pdo->prepare(
            "UPDATE sessions SET exit_time = :exit_time, overstay_minutes = :overstay, 
             extra_charge = :charge, staff_exit_id = :staff_id
             WHERE id = :session_id"
        );
        $stmt->execute([
            ':exit_time' => $exitTime,
            ':overstay' => $overstayMinutes,
            ':charge' => $extraCharge,
            ':staff_id' => $staffId,
            ':session_id' => $data['session_id']
        ]);
        
        // Update ticket status to completed
        $stmt = $pdo->prepare("UPDATE tickets SET status = 'completed' WHERE ticket_id = :ticket_id");
        $stmt->execute([':ticket_id' => $ticketId]);
        
        // Record overstay payment if applicable
        if ($overstayMinutes > 0 && $extraCharge > 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO payments (ticket_id, amount, payment_type, payment_method, confirmed_by, confirmed_at)
                 VALUES (:ticket_id, :amount, 'overstay', :method, :staff_id, NOW())"
            );
            $stmt->execute([
                ':ticket_id' => $ticketId,
                ':amount' => $extraCharge,
                ':method' => $data['payment_method'],
                ':staff_id' => $staffId
            ]);
        }
        
        $pdo->commit();
        
        // Send exit SMS
        $entryTimestamp = strtotime($data['entry_time']);
        $actualDuration = (int)ceil((strtotime($exitTime) - $entryTimestamp) / 60);
        
        if ($overstayMinutes > 0) {
            $smsMessage = "Thanks for visiting World Play!\n"
                . "Ticket: {$ticketId}\n"
                . "Session: {$actualDuration} min.\n"
                . "Overstay: {$overstayMinutes} min - " . formatCurrency($extraCharge) . ".\n"
                . "See you again!";
        } else {
            $smsMessage = "Thanks for visiting World Play!\n"
                . "Ticket: {$ticketId}\n"
                . "Session: {$actualDuration} min.\n"
                . "See you again soon!";
        }
        
        sendSMS($data['phone'], $smsMessage, $ticketId, 'exit');

        if ($overstayMinutes > 0 && $extraCharge > 0) {
            sendSupervisorAlert(
                $pdo,
                "Overstay payment recorded.\nOverstay: {$overstayMinutes} min\nAmount: " . formatCurrency($extraCharge) . "\nMethod: {$data['payment_method']}",
                $ticketId,
                'overstay'
            );
        }
        
        jsonResponse(['success' => true, 'message' => 'Session closed successfully.']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Exit processing failed for {$ticketId}: " . $e->getMessage());
        sendSupervisorAlert($pdo, "Exit processing error.\n" . $e->getMessage(), $ticketId ?: null, 'system_error');
        jsonResponse(['success' => false, 'error' => 'System error. Please try again.'], 500);
    }
}
