<?php
/**
 * Cron Job - Send SMS Reminders
 * World Play QR Ticketing System
 * 
 * Runs every 60 seconds to check for sessions needing reminders.
 * Can be triggered via Windows Task Scheduler or AJAX polling.
 * 
 * Setup (Windows Task Scheduler):
 * Program: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\worldplay\cron\send-reminders.php
 * Trigger: Every 1 minute
 */

// Allow CLI and web execution
if (php_sapi_name() !== 'cli') {
    // Web execution - check for AJAX
    header('Content-Type: application/json');
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/sms.php';

$pdo = getDBConnection();

$results = [
    'reminder_10min' => 0,
    'reminder_5min' => 0,
    'expiry' => 0,
    'expired_updated' => 0
];

// Check if SMS is enabled
$smsEnabled = getSetting($pdo, 'sms_enabled');
if ($smsEnabled !== '1') {
    if (php_sapi_name() !== 'cli') {
        echo json_encode(['status' => 'disabled', 'message' => 'SMS is disabled']);
    }
    exit;
}

// Check settings
$reminder10Enabled = getSetting($pdo, 'reminder_10min_enabled') === '1';
$reminder5Enabled = getSetting($pdo, 'reminder_5min_enabled') === '1';

// 1. Send 10-minute reminders
if ($reminder10Enabled) {
    $stmt = $pdo->prepare(
        "SELECT t.ticket_id, t.phone, s.expected_exit_time
         FROM tickets t
         JOIN sessions s ON t.ticket_id = s.ticket_id
         WHERE t.status = 'active'
         AND s.expected_exit_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 10 MINUTE)
         AND s.expected_exit_time > DATE_ADD(NOW(), INTERVAL 5 MINUTE)
         AND t.ticket_id NOT IN (
             SELECT ticket_id FROM sms_logs WHERE type = 'reminder_10min' AND status = 'sent'
         )"
    );
    $stmt->execute();
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as $ticket) {
        $exitTime = date('g:i A', strtotime($ticket['expected_exit_time']));
        $message = "10 minutes remaining!\n"
            . "Ticket: {$ticket['ticket_id']}\n"
            . "Session ends at {$exitTime}.\n"
            . "Please start wrapping up.";
        
        if (sendSMS($ticket['phone'], $message, $ticket['ticket_id'], 'reminder_10min')) {
            $results['reminder_10min']++;
        }
    }
}

// 2. Send 5-minute reminders
if ($reminder5Enabled) {
    $stmt = $pdo->prepare(
        "SELECT t.ticket_id, t.phone, s.expected_exit_time
         FROM tickets t
         JOIN sessions s ON t.ticket_id = s.ticket_id
         WHERE t.status = 'active'
         AND s.expected_exit_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 5 MINUTE)
         AND t.ticket_id NOT IN (
             SELECT ticket_id FROM sms_logs WHERE type = 'reminder_5min' AND status = 'sent'
         )"
    );
    $stmt->execute();
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as $ticket) {
        $exitTime = date('g:i A', strtotime($ticket['expected_exit_time']));
        $message = "5 minutes left!\n"
            . "Ticket: {$ticket['ticket_id']}\n"
            . "Session ends at {$exitTime}.\n"
            . "Head to exit to avoid overstay charges.";
        
        if (sendSMS($ticket['phone'], $message, $ticket['ticket_id'], 'reminder_5min')) {
            $results['reminder_5min']++;
        }
    }
}

// 3. Send expiry notifications
$stmt = $pdo->prepare(
    "SELECT t.ticket_id, t.phone, s.expected_exit_time, p.extra_per_minute
     FROM tickets t
     JOIN sessions s ON t.ticket_id = s.ticket_id
     JOIN pricing p ON t.pricing_id = p.id
     WHERE t.status = 'active'
     AND s.expected_exit_time < NOW()
     AND t.ticket_id NOT IN (
         SELECT ticket_id FROM sms_logs WHERE type = 'expiry' AND status = 'sent'
     )"
);
$stmt->execute();
$tickets = $stmt->fetchAll();

foreach ($tickets as $ticket) {
    $rate = formatCurrency($ticket['extra_per_minute']);
    $message = "Session Expired!\n"
        . "Ticket: {$ticket['ticket_id']}\n"
        . "Your session has ended.\n"
        . "Please exit now. Overstay charges: {$rate}/min.";
    
    if (sendSMS($ticket['phone'], $message, $ticket['ticket_id'], 'expiry')) {
        $results['expiry']++;
    }
}

// 4. Auto-update expired tickets (status: active -> expired for tickets past time)
$stmt = $pdo->prepare(
    "UPDATE tickets t
     JOIN sessions s ON t.ticket_id = s.ticket_id
     SET t.status = 'expired'
     WHERE t.status = 'active'
     AND s.expected_exit_time < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
     AND s.exit_time IS NULL"
);
$stmt->execute();
$results['expired_updated'] = $stmt->rowCount();

// Output results
if (php_sapi_name() === 'cli') {
    echo "[" . date('Y-m-d H:i:s') . "] Reminders sent: " 
        . "10min={$results['reminder_10min']}, "
        . "5min={$results['reminder_5min']}, "
        . "expiry={$results['expiry']}, "
        . "expired_updated={$results['expired_updated']}\n";
} else {
    echo json_encode(['status' => 'ok', 'results' => $results]);
}
