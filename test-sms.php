<?php
/**
 * Text.lk SMS Test Script
 * Use this to test your Text.lk configuration
 */

require_once 'config/textlk.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "=== Text.lk SMS Test ===\n\n";

// Test 1: Check configuration
echo "1. Configuration Check:\n";
echo "   API Key: " . (TEXTLK_API_KEY !== 'your_api_key_here' ? 'SET' : 'NOT SET') . "\n";
echo "   Sender ID: " . TEXTLK_SENDER_ID . "\n";
echo "   Sender ID Length: " . strlen(TEXTLK_SENDER_ID) . " characters\n";

if (strlen(TEXTLK_SENDER_ID) > 11) {
    echo "   ⚠️  WARNING: Sender ID is too long (max 11 characters)\n";
}

if (TEXTLK_API_KEY === 'your_api_key_here') {
    echo "   ❌ ERROR: API Key is not configured!\n";
    exit(1);
}

echo "\n2. Phone Number Formatting Test:\n";
$testPhones = ['0712345678', '712345678', '94712345678', '+94712345678'];
foreach ($testPhones as $phone) {
    $formatted = formatPhoneForTextLK($phone);
    echo "   {$phone} → {$formatted}\n";
}

echo "\n3. API Connection Test:\n";
echo "   Enter a test phone number (e.g., 0712345678): ";
$testPhone = trim(fgets(STDIN));

if (empty($testPhone)) {
    echo "   No phone number provided. Skipping API test.\n";
    exit(0);
}

$formattedPhone = formatPhoneForTextLK($testPhone);
echo "   Formatted: {$formattedPhone}\n\n";

// Try to send SMS
$url = 'https://app.text.lk/api/v3/sms/send';
$data = [
    'recipient' => $formattedPhone,
    'sender_id' => TEXTLK_SENDER_ID,
    'type' => 'plain',
    'message' => 'Test message from World Play System'
];

$headers = [
    'Authorization: Bearer ' . TEXTLK_API_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
];

echo "   Sending request to Text.lk API...\n";

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

echo "   HTTP Code: {$httpCode}\n";

if ($curlError) {
    echo "   ❌ cURL Error: {$curlError}\n";
} else {
    echo "   Response: {$response}\n\n";
    
    $responseData = json_decode($response, true);
    
    if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
        echo "   ✅ SMS sent successfully!\n";
    } else {
        echo "   ❌ SMS failed!\n";
        if (isset($responseData['message'])) {
            echo "   Error: " . $responseData['message'] . "\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
