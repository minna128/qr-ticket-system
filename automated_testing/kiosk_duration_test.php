<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Kiosk Duration Selection Test\n";
echo "========================================\n\n";

try {
    // TC-001: Kiosk welcome screen loads
    $driver->get('http://localhost/worldplay/kiosk/');
    sleep(2);

    $btn = $driver->findElement(WebDriverBy::cssSelector('.btn-primary'));
    if ($btn->isDisplayed()) {
        echo "✅ TC-001 PASSED: Kiosk welcome screen loaded\n";
    } else {
        echo "❌ TC-001 FAILED: Start button not visible\n";
    }

    // TC-003: Duration plans are displayed
    $btn->click();
    sleep(2);

    $plans = $driver->findElements(WebDriverBy::cssSelector('.duration-card'));
    if (count($plans) > 0) {
        echo "✅ TC-003 PASSED: " . count($plans) . " pricing plans displayed\n";
    } else {
        echo "❌ TC-003 FAILED: No pricing plans found\n";
    }

    // TC-004: Continue button disabled before selection
    $continueBtn = $driver->findElement(WebDriverBy::id('btn-continue'));
    if ($continueBtn->getAttribute('disabled')) {
        echo "✅ TC-004 PASSED: Continue button disabled before selecting a plan\n";
    } else {
        echo "❌ TC-004 FAILED: Continue button should be disabled\n";
    }

    // TC-005: Continue button enables after selection
    $plans[0]->click();
    sleep(1);
    $continueBtn = $driver->findElement(WebDriverBy::id('btn-continue'));
    if (!$continueBtn->getAttribute('disabled')) {
        echo "✅ TC-005 PASSED: Continue button enabled after selecting a plan\n";
    } else {
        echo "❌ TC-005 FAILED: Continue button still disabled\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Kiosk Test Complete\n";
echo "========================================\n";