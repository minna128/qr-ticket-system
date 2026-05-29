<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Ticket Lifecycle Test\n";
echo "  Activate -> Exit (Full Journey)\n";
echo "========================================\n\n";

$ticketId = 'WP-DEMO01';

// ── STEP 1: Login as entry staff and activate ─────────────────────────────────
try {
    $driver->get('http://localhost/worldplay/entry/');
    sleep(2);
    $driver->findElement(WebDriverBy::name('username'))->sendKeys('entry_staff');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('staff123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);
    echo "[PASS] Entry staff logged in\n";

    // Go to scan page
    $driver->get('http://localhost/worldplay/entry/scan.php');
    sleep(2);

    // Scan ticket
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys($ticketId);
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    // Click Activate Session
    $csrfToken = $driver->findElement(
        WebDriverBy::cssSelector("input[name='csrf_token']")
    )->getAttribute('value');

    $driver->executeScript(
        "document.querySelector(\"input[name='csrf_token']\").value = '" . $csrfToken . "';" .
        "activateTicket('" . $ticketId . "');"
    );
    sleep(5);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();

    if (strpos($resultText, 'Session Activated') !== false) {
        echo "[PASS] TC-014: Ticket $ticketId activated at ENTRY gate\n";
        echo "   Session started - timer is running\n";
    } else {
        echo "[FAIL] TC-014: Activation failed\n";
        echo "   Result: " . substr($resultText, 0, 100) . "\n";
    }

} catch (Exception $e) {
    echo "[FAIL] Entry Error: " . $e->getMessage() . "\n";
}

// ── STEP 2: Logout from entry ─────────────────────────────────────────────────
$driver->manage()->deleteAllCookies();
sleep(1);

// ── STEP 3: Login as exit staff and process exit ──────────────────────────────
try {
    $driver->get('http://localhost/worldplay/exit/');
    sleep(2);
    $driver->findElement(WebDriverBy::name('username'))->sendKeys('exit_staff');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('staff123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);
    echo "[PASS] Exit staff logged in\n";

    // Go to exit scan page
    $driver->get('http://localhost/worldplay/exit/scan.php');
    sleep(2);

    // Scan the same ticket at exit
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys($ticketId);
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();

    if (strpos($resultText, 'Exit') !== false ||
        strpos($resultText, 'Session') !== false ||
        strpos($resultText, 'Overstay') !== false ||
        strpos($resultText, 'Confirm') !== false) {
        echo "[PASS] TC-018: Ticket $ticketId found at EXIT gate\n";
        echo "   Exit details displayed on screen\n";
    } else {
        echo "[FAIL] TC-018: Exit scan failed\n";
        echo "   Result: " . substr($resultText, 0, 100) . "\n";
    }

    // Verify ticket shows session details
    if (strpos($resultText, 'Entry Time') !== false ||
        strpos($resultText, 'Duration') !== false) {
        echo "[PASS] TC-019: Session details shown (entry time, duration)\n";
    } else {
        echo "[FAIL] TC-019: Session details not shown\n";
    }

} catch (Exception $e) {
    echo "[FAIL] Exit Error: " . $e->getMessage() . "\n";
}

// ── STEP 4: Verify ticket can't be activated again ────────────────────────────
try {
    $driver->manage()->deleteAllCookies();
    sleep(1);

    $driver->get('http://localhost/worldplay/entry/');
    sleep(2);
    $driver->findElement(WebDriverBy::name('username'))->sendKeys('entry_staff');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('staff123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);

    $driver->get('http://localhost/worldplay/entry/scan.php');
    sleep(2);

    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys($ticketId);
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();

    if (strpos($resultText, 'Cannot') !== false ||
        strpos($resultText, 'active') !== false ||
        strpos($resultText, 'completed') !== false ||
        strpos($resultText, 'error') !== false) {
        echo "[PASS] TC-020: Used ticket correctly rejected at entry\n";
    } else {
        echo "[FAIL] TC-020: Used ticket should be rejected\n";
        echo "   Result: " . substr($resultText, 0, 100) . "\n";
    }

} catch (Exception $e) {
    echo "[FAIL] Reentry Error: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Ticket Lifecycle Test Complete\n";
echo "========================================\n";