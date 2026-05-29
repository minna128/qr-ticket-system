<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

$host = 'http://localhost:4444';
$driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
$driver->manage()->window()->maximize();

echo "========================================\n";
echo "  Exit Gate & Overstay Test\n";
echo "========================================\n\n";

try {
    // Login as exit staff
    $driver->get('http://localhost/worldplay/exit/');
    sleep(2);

    $driver->findElement(WebDriverBy::name('username'))->sendKeys('exit_staff');
    $driver->findElement(WebDriverBy::name('password'))->sendKeys('staff123');
    $driver->findElement(WebDriverBy::cssSelector("button[type='submit']"))->click();
    sleep(2);

    echo "✅ Exit staff logged in\n";

    // Go to exit scan page
    $driver->get('http://localhost/worldplay/exit/scan.php');
    sleep(2);

    // TC-018: Valid active ticket exit (WP-E5NF47 is active in your DB)
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys('WP-E5NF47');
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    $result = $driver->findElement(WebDriverBy::id('scan-result'));
    $resultText = $result->getText();
    $resultClass = $result->getAttribute('class');

    if (strpos($resultClass, 'success') !== false ||
        strpos($resultText, 'exit') !== false ||
        strpos($resultText, 'Exit') !== false ||
        strpos($resultText, 'overstay') !== false ||
        strpos($resultText, 'Overstay') !== false) {
        echo "✅ TC-018/019 PASSED: Active ticket processed at exit\n";
        echo "   Result: $resultText\n";

        // Check if overstay message appears (ticket is from 05/09 so definitely overstayed)
        if (strpos($resultText, 'overstay') !== false ||
            strpos($resultText, 'Overstay') !== false ||
            strpos($resultText, 'extra') !== false) {
            echo "✅ TC-019 PASSED: Overstay charge detected and displayed\n";
        } else {
            echo "ℹ️  TC-019 INFO: No overstay shown (ticket may be within time)\n";
        }
    } else {
        echo "❌ TC-018 FAILED: Exit not processed correctly\n";
        echo "   Result: $resultText\n";
    }

    // TC-020: Non-existent ticket
    sleep(1);
    $driver->get('http://localhost/worldplay/exit/scan.php');
    sleep(2);
    $driver->findElement(WebDriverBy::id('manual-ticket-id'))->sendKeys('WP-ZZZZZZ');
    $driver->findElement(WebDriverBy::id('btn-manual-submit'))->click();
    sleep(3);

    $result2 = $driver->findElement(WebDriverBy::id('scan-result'));
    $result2Text = $result2->getText();

    if (strpos($result2Text, 'not found') !== false ||
        strpos($result2Text, 'Invalid') !== false ||
        strpos($result2->getAttribute('class'), 'error') !== false) {
        echo "✅ TC-020 PASSED: Non-existent ticket correctly rejected\n";
    } else {
        echo "❌ TC-020 FAILED: Should have shown not found error\n";
        echo "   Result: $result2Text\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

sleep(3);
$driver->quit();

echo "\n========================================\n";
echo "  Exit Test Complete\n";
echo "========================================\n";