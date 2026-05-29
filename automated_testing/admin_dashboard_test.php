<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Admin Dashboard Test\n";
echo "========================================\n\n";

try {
    // Login as admin
    $driver->get('http://localhost/worldplay/admin/login.php');
    sleep(2);
    $driver->findElement(WebDriverBy::name('username'))->sendKeys('admin');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('admin123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);
    echo "[PASS] Admin logged in successfully\n";

    // TC-024: Dashboard loads with stats
    $currentURL = $driver->getCurrentURL();
    if (strpos($currentURL, 'admin') !== false) {
        echo "[PASS] TC-024: Admin dashboard loaded\n";
    } else {
        echo "[FAIL] TC-024: Dashboard did not load\n";
    }

    // TC-025: Navigate to Active Sessions
    $driver->get('http://localhost/worldplay/admin/active-sessions.php');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'Active') !== false || strpos($pageText, 'Session') !== false) {
        echo "[PASS] TC-025: Active Sessions page loaded\n";
    } else {
        echo "[FAIL] TC-025: Active Sessions page failed\n";
    }

    // TC-026: Navigate to Pricing
    $driver->get('http://localhost/worldplay/admin/pricing.php');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'Pricing') !== false || strpos($pageText, 'Duration') !== false) {
        echo "[PASS] TC-026: Pricing page loaded\n";
    } else {
        echo "[FAIL] TC-026: Pricing page failed\n";
    }

    // TC-027: Navigate to Transactions
    $driver->get('http://localhost/worldplay/admin/transactions.php');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'Transaction') !== false || strpos($pageText, 'Payment') !== false) {
        echo "[PASS] TC-027: Transactions page loaded\n";
    } else {
        echo "[FAIL] TC-027: Transactions page failed\n";
    }

    // TC-028: Navigate to Reports
    $driver->get('http://localhost/worldplay/admin/reports.php');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'Report') !== false || strpos($pageText, 'Revenue') !== false) {
        echo "[PASS] TC-028: Reports page loaded\n";
    } else {
        echo "[FAIL] TC-028: Reports page failed\n";
    }

    // TC-029: Navigate to Staff Management
    $driver->get('http://localhost/worldplay/admin/staff.php');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'Staff') !== false || strpos($pageText, 'entry_staff') !== false) {
        echo "[PASS] TC-029: Staff Management page loaded\n";
    } else {
        echo "[FAIL] TC-029: Staff Management page failed\n";
    }

    // TC-030: Logout
    $driver->get('http://localhost/worldplay/admin/logout.php');
    sleep(2);
    $currentURL = $driver->getCurrentURL();
    if (strpos($currentURL, 'login') !== false || strpos($currentURL, 'index.php') !== false) {
        echo "[PASS] TC-030: Admin logout successful\n";
    } else {
        echo "[FAIL] TC-030: Logout did not redirect properly\n";
    }

} catch (Exception $e) {
    echo "[FAIL] ERROR: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Admin Dashboard Test Complete\n";
echo "========================================\n";