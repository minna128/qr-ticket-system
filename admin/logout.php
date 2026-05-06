<?php
/**
 * Admin Logout
 * World Play QR Ticketing System
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "/admin/login.php");
exit;
