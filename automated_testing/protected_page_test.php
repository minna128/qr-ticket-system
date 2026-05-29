<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

// Selenium server
$host = 'http://localhost:4444';

// Start browser
$driver = RemoteWebDriver::create(
    $host,
    DesiredCapabilities::chrome()
);

try {

    // Directly access protected page
    $driver->get('http://localhost/worldplay/entry/scan.php');

    sleep(3);

    // Get current URL
    $currentURL = $driver->getCurrentURL();

    // Check if redirected to login page
    if (strpos($currentURL, '/entry/') !== false &&
        strpos($currentURL, 'scan.php') === false) {

        echo "✅ Protected Page Test Passed";

    } else {

        echo "❌ Protected Page Test Failed";
    }

} catch (Exception $e) {

    echo "❌ Error: " . $e->getMessage();

}

// Close browser
$driver->quit();