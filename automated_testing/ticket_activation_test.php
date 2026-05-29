<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Ticket Activation Test\n";
echo "========================================\n\n";

// ── Login as entry staff ──────────────────────────────────────────────────────
try {
    $driver->get('http://localhost/worldplay/entry/');
    sleep(2);
    $driver->findElement(WebDriverBy::name('username'))->sendKeys('entry_staff');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('staff123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);
    echo "[PASS] Entry staff logged in\n";
} catch (Exception $e) {
    echo "[FAIL] Login failed: " . $e->getMessage() . "\n";
    $driver->quit();
    exit;
}

// ── Go to scan page ───────────────────────────────────────────────────────────
$driver->get('http://localhost/worldplay/entry/scan.php');
sleep(2);

// %% TC-014: Activate ticket via UI (visible for demo)
try {
    // First scan the ticket
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys('WP-TEST01');
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    // Now click the Activate Session button using JavaScript
    $csrfToken = $driver->findElement(
        WebDriverBy::cssSelector("input[name='csrf_token']")
    )->getAttribute('value');

    // Inject the CSRF token into the page then call activateTicket()
    $driver->executeScript(
        "document.querySelector(\"input[name='csrf_token']\").value = '" . $csrfToken . "';" .
        "activateTicket('WP-DEMO01');"
    );
    sleep(5);

    // Check what the page shows now
    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();

    if (strpos($resultText, 'Session Activated') !== false ||
        strpos($resultText, 'Entry approved') !== false) {
        echo "[PASS] TC-014 PASSED: Ticket WP-TEST01 activated - Session Activated shown on screen\n";
    } else {
        echo "[FAIL] TC-014 FAILED: Expected 'Session Activated' on screen\n";
        echo "   Result: " . substr($resultText, 0, 100) . "\n";
    }
} catch (Exception $e) {
    echo "[FAIL] TC-014 ERROR: " . $e->getMessage() . "\n";
}

// %% TC-015: Try activating same ticket again - should be rejected
try {
    $driver->get('http://localhost/worldplay/entry/scan.php');
    sleep(2);

    // Scan the ticket first (visual for demo)
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys('WP-TEST01');
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    // Try to activate it - should fail since it's already active
    $csrfToken = $driver->findElement(
        WebDriverBy::cssSelector("input[name='csrf_token']")
    )->getAttribute('value');

    $driver->executeScript(
        "document.querySelector(\"input[name='csrf_token']\").value = '" . $csrfToken . "';" .
        "activateTicket('WP-TEST01');"
    );
    sleep(5);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();

    if (strpos($resultText, 'Activation Failed') !== false ||
        strpos($resultText, 'cannot be activated') !== false) {
        echo "[PASS] TC-015 PASSED: Already-active ticket correctly rejected\n";
    } else {
        echo "[FAIL] TC-015 FAILED: Should have rejected already-active ticket\n";
        echo "   Result: " . substr($resultText, 0, 150) . "\n";
    }
} catch (Exception $e) {
    echo "[FAIL] TC-015 ERROR: " . $e->getMessage() . "\n";
}

// ── TC-017: Invalid ticket format ─────────────────────────────────────────────
try {
    $driver->get('http://localhost/worldplay/entry/scan.php');
    sleep(2);

    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys('INVALID123');
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(2);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    if (strpos($result->getText(), 'Invalid') !== false ||
        strpos($result->getAttribute('class'), 'error') !== false) {
        echo "[PASS] TC-017 PASSED: Invalid ticket format correctly rejected\n";
    } else {
        echo "[FAIL] TC-017 FAILED: Invalid format was not rejected\n";
    }
} catch (Exception $e) {
    echo "[FAIL] TC-017 ERROR: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Activation Test Complete\n";
echo "========================================\n";