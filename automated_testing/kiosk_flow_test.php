<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Kiosk Full Flow Test\n";
echo "========================================\n\n";

try {
    // TC-001: Home page loads
    $driver->get('http://localhost/worldplay/');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if (strpos($pageText, 'World Play') !== false) {
        echo "[PASS] TC-001: Home page loaded successfully\n";
    } else {
        echo "[FAIL] TC-001: Home page did not load\n";
    }

    // TC-002: Navigate to Kiosk from home
    $driver->findElement(WebDriverBy::cssSelector("a[href*='kiosk']"))->click();
    sleep(2);
    if (strpos($driver->getCurrentURL(), 'kiosk') !== false) {
        echo "[PASS] TC-002: Navigated to Kiosk from home page\n";
    } else {
        echo "[FAIL] TC-002: Failed to navigate to Kiosk\n";
    }

    // TC-003: Kiosk welcome screen has Start button
    $startBtn = $driver->findElement(WebDriverBy::cssSelector('.btn-primary'));
    if ($startBtn->isDisplayed()) {
        echo "[PASS] TC-003: Kiosk welcome screen shows Start button\n";
    } else {
        echo "[FAIL] TC-003: Start button not visible\n";
    }

    // TC-004: Click Start and reach duration page
    $startBtn->click();
    sleep(2);
    if (strpos($driver->getCurrentURL(), 'select-duration') !== false) {
        echo "[PASS] TC-004: Duration selection page loaded\n";
    } else {
        echo "[FAIL] TC-004: Did not reach duration page\n";
    }

    // TC-005: Pricing plans are displayed
    $plans = $driver->findElements(WebDriverBy::cssSelector('.duration-card'));
    if (count($plans) > 0) {
        echo "[PASS] TC-005: " . count($plans) . " pricing plans displayed\n";
    } else {
        echo "[FAIL] TC-005: No pricing plans found\n";
    }

    // TC-006: Continue disabled before selection
    $continueBtn = $driver->findElement(WebDriverBy::id('btn-continue'));
    if ($continueBtn->getAttribute('disabled')) {
        echo "[PASS] TC-006: Continue button correctly disabled\n";
    } else {
        echo "[FAIL] TC-006: Continue button should be disabled\n";
    }

    // TC-007: Select first plan and Continue enables
    $plans[0]->click();
    sleep(1);
    if (!$continueBtn->getAttribute('disabled')) {
        echo "[PASS] TC-007: Continue button enabled after selecting plan\n";
    } else {
        echo "[FAIL] TC-007: Continue button still disabled\n";
    }

    // TC-008: Click Continue and reach next page
    $continueBtn->click();
    sleep(2);
    $currentURL = $driver->getCurrentURL();
    if (strpos($currentURL, 'select-games') !== false ||
        strpos($currentURL, 'enter-phone') !== false ||
        strpos($currentURL, 'confirm') !== false) {
        echo "[PASS] TC-008: Moved to next step after selecting plan\n";
    } else {
        echo "[FAIL] TC-008: Did not proceed to next step\n";
        echo "   URL: " . $currentURL . "\n";
    }

    // TC-009: Language switcher works
    $driver->get('http://localhost/worldplay/kiosk/?lang=si');
    sleep(2);
    $pageText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    $driver->get('http://localhost/worldplay/kiosk/?lang=en');
    sleep(2);
    $pageTextEN = $driver->findElement(WebDriverBy::tagName('body'))->getText();
    if ($pageText !== $pageTextEN) {
        echo "[PASS] TC-009: Language switcher changes page content\n";
    } else {
        echo "[FAIL] TC-009: Language did not change\n";
    }

} catch (Exception $e) {
    echo "[FAIL] ERROR: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Kiosk Flow Test Complete\n";
echo "========================================\n";