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

    // Open admin login page
    $driver->get('http://localhost/worldplay/admin/');

    // Maximize browser
    $driver->manage()->window()->maximize();

    // Enter admin credentials
    $driver->findElement(
        WebDriverBy::name('username')
    )->sendKeys('admin');

    $driver->findElement(
        WebDriverBy::name('password')
    )->sendKeys('admin123');

    // Click login
    $driver->findElement(
        WebDriverBy::cssSelector("button[type='submit']")
    )->click();

    sleep(3);

    // Verify login success
    $currentURL = $driver->getCurrentURL();

    if (strpos($currentURL, 'dashboard') !== false ||
        strpos($currentURL, 'admin') !== false) {

        echo "✅ Admin Login Test Passed";

    } else {

        echo "❌ Admin Login Test Failed";
    }

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

// Close browser
$driver->quit();