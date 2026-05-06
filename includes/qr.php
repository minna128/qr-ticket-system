<?php
/**
 * QR Code Generation Wrapper
 * World Play QR Ticketing System
 */

require_once __DIR__ . '/../vendor/phpqrcode/phpqrcode.php';

/**
 * Generate QR code PNG for a ticket
 * 
 * @param string $ticketId The ticket ID to encode
 * @return string|false Path to generated QR image, or false on failure
 */
function generateQRCode($ticketId) {
    $filename = $ticketId . '.png';
    $filepath = QR_CODES_PATH . '/' . $filename;
    $webPath = BASE_URL . '/assets/images/qrcodes/' . $filename;
    
    // Generate QR code: data, filepath, error correction level, size, margin
    try {
        QRcode::png($ticketId, $filepath, QR_ECLEVEL_M, 10, 2);
        
        if (file_exists($filepath)) {
            return $webPath;
        }
    } catch (Exception $e) {
        error_log("QR Code generation failed for {$ticketId}: " . $e->getMessage());
    }
    
    return false;
}
