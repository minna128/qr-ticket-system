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

    // Open login page
    $driver->get('http://localhost/worldplay/entry/');

    // Maximize browser
    $driver->manage()->window()->maximize();

    // Wrong credentials
    $driver->findElement(
        WebDriverBy::name('username')
    )->sendKeys('wrong_user');

    $driver->findElement(
        WebDriverBy::name('password')
    )->sendKeys('wrong_password');

    // Click login
    $driver->findElement(
        WebDriverBy::cssSelector("button[type='submit']")
    )->click();

    sleep(3);

    // Check error message
    $pageSource = $driver->getPageSource();

    if (strpos($pageSource, 'Invalid username or password') !== false) {
        echo "✅ Invalid Login Test Passed";
    } else {
        echo "❌ Invalid Login Test Failed";
    }

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

// Close browser
$driver->quit();