<?php
/**
 * Kiosk - Generate Ticket (Core Logic)
 * World Play QR Ticketing System
 * 
 * Creates ticket record, generates QR code, sends SMS
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/qr.php';
require_once __DIR__ . '/../includes/sms.php';
require_once __DIR__ . '/../includes/supervisor.php';

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/kiosk/');
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/kiosk/');
}

// Verify session data is complete
if (!isset($_SESSION['kiosk_pricing_id']) || 
    !isset($_SESSION['kiosk_phone']) || 
    !isset($_SESSION['kiosk_payment_method'])) {
    redirect('/kiosk/select-duration.php');
}

$pdo = getDBConnection();

try {
    $pdo->beginTransaction();
    
    // Generate unique ticket ID
    $ticketId = generateTicketId($pdo);
    
    // Get session data
    $pricingId = $_SESSION['kiosk_pricing_id'];
    $phone = $_SESSION['kiosk_phone'];
    $duration = $_SESSION['kiosk_duration'];
    $durationPrice = $_SESSION['kiosk_price'];
    $paymentMethod = $_SESSION['kiosk_payment_method'];
    $gamesTotal = $_SESSION['kiosk_games_total'] ?? 0;
    $totalAmount = $_SESSION['kiosk_total_amount'] ?? $durationPrice;
    
    // Generate QR code
    $qrPath = generateQRCode($ticketId);
    
    if (!$qrPath) {
        throw new Exception('Failed to generate QR code');
    }
    
    // Insert ticket record
    $stmt = $pdo->prepare(
        "INSERT INTO tickets (ticket_id, phone, pricing_id, duration_minutes, payment_method, payment_status, qr_code_path, status)
         VALUES (:ticket_id, :phone, :pricing_id, :duration, :payment_method, 'confirmed', :qr_path, 'not_activated')"
    );
    $stmt->execute([
        ':ticket_id' => $ticketId,
        ':phone' => $phone,
        ':pricing_id' => $pricingId,
        ':duration' => $duration,
        ':payment_method' => $paymentMethod,
        ':qr_path' => $qrPath
    ]);
    
    // Insert selected games if any
    if (isset($_SESSION['kiosk_game_ids']) && !empty($_SESSION['kiosk_game_ids'])) {
        $gameIds = explode(',', $_SESSION['kiosk_game_ids']);
        $gameStmt = $pdo->prepare("INSERT INTO ticket_games (ticket_id, game_id) VALUES (:ticket_id, :game_id)");
        
        foreach ($gameIds as $gameId) {
            if (!empty($gameId)) {
                $gameStmt->execute([
                    ':ticket_id' => $ticketId,
                    ':game_id' => (int)$gameId
                ]);
            }
        }
    }
    
    // Insert initial payment record
    $stmt = $pdo->prepare(
        "INSERT INTO payments (ticket_id, amount, payment_type, payment_method, confirmed_at)
         VALUES (:ticket_id, :amount, 'initial', :payment_method, NOW())"
    );
    $stmt->execute([
        ':ticket_id' => $ticketId,
        ':amount' => $totalAmount,
        ':payment_method' => $paymentMethod
    ]);
    
    $pdo->commit();
    
    // Send purchase confirmation SMS
    $smsMessage = "World Play Ticket Ready!\n"
        . "Ticket: {$ticketId}\n"
        . "Duration: {$duration} min\n";
    
    // Add games to SMS if selected
    if (isset($_SESSION['kiosk_game_ids']) && !empty($_SESSION['kiosk_game_ids'])) {
        $gameIds = explode(',', $_SESSION['kiosk_game_ids']);
        $gameIds = array_map('intval', $gameIds);
        $gameIds = array_filter($gameIds);
        
        if (!empty($gameIds)) {
            $stmt = $pdo->prepare("SELECT name FROM games WHERE id IN (" . implode(',', $gameIds) . ")");
            $stmt->execute();
            $games = $stmt->fetchAll();
            
            if (!empty($games)) {
                $smsMessage .= "Games: " . implode(', ', array_column($games, 'name')) . "\n";
            }
        }
    }
    
    $smsMessage .= "Total: " . formatCurrency($totalAmount) . "\n"
        . "Show QR code at entry gate.\n"
        . "Have fun!";
    
    sendSMS($phone, $smsMessage, $ticketId, 'purchase');
    
    // Store ticket data for display page
    $_SESSION['generated_ticket'] = [
        'ticket_id' => $ticketId,
        'phone' => $phone,
        'duration' => $duration,
        'price' => $totalAmount,
        'payment_method' => $paymentMethod,
        'qr_path' => $qrPath
    ];
    
    // Clear kiosk session data
    unset($_SESSION['kiosk_pricing_id']);
    unset($_SESSION['kiosk_duration']);
    unset($_SESSION['kiosk_price']);
    unset($_SESSION['kiosk_label']);
    unset($_SESSION['kiosk_extra_per_min']);
    unset($_SESSION['kiosk_phone']);
    unset($_SESSION['kiosk_payment_method']);
    
    // Redirect to display page
    header("Location: " . BASE_URL . "/kiosk/ticket-display.php");
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ticket generation failed: " . $e->getMessage());
    sendSupervisorAlert($pdo, "Ticket generation failed.\n" . $e->getMessage(), null, 'system_error');
    setFlash('danger', 'An error occurred while generating your ticket. Please try again.');
    redirect('/kiosk/');
}
