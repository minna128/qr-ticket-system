<?php
/**
 * Text.lk SMS Wrapper
 * World Play QR Ticketing System
 * 
 * Sends SMS via Text.lk REST API using cURL
 */

require_once __DIR__ . '/../config/textlk.php';
require_once __DIR__ . '/supervisor.php';

/**
 * Send an SMS message via Text.lk
 * 
 * @param string $phone Recipient phone number
 * @param string $message Message body
 * @param string $ticketId Associated ticket ID for logging
 * @param string $type SMS type (purchase, activation, reminder_10min, etc.)
 * @return bool Success status
 */
function sendSMS($phone, $message, $ticketId, $type) {
    $pdo = getDBConnection();
    
    // Check if SMS is enabled
    $smsEnabled = getSetting($pdo, 'sms_enabled');
    if ($smsEnabled !== '1') {
        // Log but don't send
        logSMS($pdo, $ticketId, $phone, $message, $type, 'queued', null, 'SMS disabled in settings');
        return true;
    }
    
    // Normalize phone number to text.lk format (94XXXXXXXXX)
    $phone = formatPhoneForTextLK($phone);
    
    // Send via Text.lk
    $url = 'https://app.text.lk/api/v3/sms/send';
    
    $data = [
        'recipient' => $phone,
        'sender_id' => TEXTLK_SENDER_ID,
        'type' => 'plain',
        'message' => $message
    ];
    
    $headers = [
        'Authorization: Bearer ' . TEXTLK_API_KEY,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        logSMS($pdo, $ticketId, $phone, $message, $type, 'failed', null, 'cURL error: ' . $curlError);
        sendSupervisorAlert(
            $pdo,
            "Customer SMS failed to send.\nReason: cURL error.\nError: {$curlError}",
            $ticketId,
            'sms_failed'
        );
        return false;
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        $messageId = isset($responseData['data']) ? json_encode($responseData['data']) : null;
        logSMS($pdo, $ticketId, $phone, $message, $type, 'sent', $messageId, null);
        return true;
    } else {
        $errorMsg = $responseData['message'] ?? 'HTTP ' . $httpCode;
        logSMS($pdo, $ticketId, $phone, $message, $type, 'failed', null, $errorMsg);
        sendSupervisorAlert(
            $pdo,
            "Customer SMS failed to send.\nReason: {$errorMsg}\nHTTP: {$httpCode}",
            $ticketId,
            'sms_failed'
        );
        return false;
    }
}

/**
 * Log SMS to database
 */
function logSMS($pdo, $ticketId, $phone, $message, $type, $status, $messageId = null, $errorMessage = null) {
    $stmt = $pdo->prepare(
        "INSERT INTO sms_logs (ticket_id, phone, message, type, status, message_id, error_message) 
         VALUES (:ticket_id, :phone, :message, :type, :status, :message_id, :error_message)"
    );
    $stmt->execute([
        ':ticket_id' => $ticketId,
        ':phone' => $phone,
        ':message' => $message,
        ':type' => $type,
        ':status' => $status,
        ':message_id' => $messageId,
        ':error_message' => $errorMessage
    ]);
}

/**
 * Check if SMS of a specific type has already been sent for a ticket
 */
function smsAlreadySent($pdo, $ticketId, $type) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM sms_logs WHERE ticket_id = :ticket_id AND type = :type AND status = 'sent'"
    );
    $stmt->execute([':ticket_id' => $ticketId, ':type' => $type]);
    return $stmt->fetchColumn() > 0;
}
