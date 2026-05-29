<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

// Selenium server
$host = 'http://localhost:4444';

// Start browser
$driver = RemoteWebDriver::create(
    $host,
    DesiredCapabilities::chrome()
);

try {

    // Open entry login page
    $driver->get('http://localhost/worldplay/entry/');

    // Maximize browser
    $driver->manage()->window()->maximize();

    // Enter username
    $driver->findElement(
        WebDriverBy::name('username')
    )->sendKeys('entry_staff');

    // Enter password
    $driver->findElement(
        WebDriverBy::name('password')
    )->sendKeys('staff123');

    // Click login
    $driver->findElement(
        WebDriverBy::cssSelector("button[type='submit']")
    )->click();

    sleep(3);

    // Verify redirect
    $currentURL = $driver->getCurrentURL();

    if (strpos($currentURL, 'scan.php') !== false) {
        echo "✅ Entry Login Test Passed";
    } else {
        echo "❌ Entry Login Test Failed";
    }

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

// Close browser
$driver->quit();