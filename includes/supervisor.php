<?php
/**
 * Floor Supervisor Notifications (SMS)
 * World Play QR Ticketing System
 */

require_once __DIR__ . '/../config/textlk.php';
require_once __DIR__ . '/functions.php';

/**
 * Send an SMS alert to the floor supervisor (no DB logging).
 *
 * Settings:
 * - supervisor_alerts_enabled: "1" to enable
 * - supervisor_phone: supervisor phone (any local/+94 format)
 */
function sendSupervisorAlert($pdo, $message, $ticketId = null, $type = 'system') {
    $enabled = getSetting($pdo, 'supervisor_alerts_enabled') === '1';
    if (!$enabled) {
        return false;
    }

    $phoneSetting = trim((string)getSetting($pdo, 'supervisor_phone'));
    if ($phoneSetting === '') {
        return false;
    }

    $recipient = formatPhoneForTextLK($phoneSetting);

    $prefix = 'World Play Alert';
    if ($type) {
        $prefix .= " ({$type})";
    }
    if ($ticketId) {
        $prefix .= "\nTicket: {$ticketId}";
    }

    $body = $prefix . "\n" . $message;

    $url = 'https://app.text.lk/api/v3/sms/send';
    $data = [
        'recipient' => $recipient,
        'sender_id' => TEXTLK_SENDER_ID,
        'type' => 'plain',
        'message' => $body
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
        error_log("Supervisor SMS cURL error: " . $curlError);
        return false;
    }

    $responseData = json_decode($response, true);
    return ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === 'success');
}

