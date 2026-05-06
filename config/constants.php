<?php
/**
 * Application Constants
 * World Play QR Ticketing System
 */

define('APP_NAME', 'World Play');
define('APP_TAGLINE', 'QR-Based Smart Kiosk Ticketing System');
define('BASE_URL', '/worldplay');
define('APP_TIMEZONE', 'Asia/Colombo');
define('APP_CURRENCY', 'LKR');
define('APP_VERSION', '1.0.0');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('QR_CODES_PATH', ASSETS_PATH . '/images/qrcodes');
define('VENDOR_PATH', ROOT_PATH . '/vendor');
