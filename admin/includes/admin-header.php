<?php
/**
 * Admin Header Layout
 * World Play QR Ticketing System
 */

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

requireAdmin();

$admin = getCurrentAdmin();
$adminPageTitle = isset($adminPageTitle) ? $adminPageTitle : 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?php echo BASE_URL; ?>">
    <title><?php echo sanitize($adminPageTitle); ?> - Admin - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-topbar">
            <h1><?php echo sanitize($adminPageTitle); ?></h1>
            <div class="user-info">
                Welcome, <?php echo sanitize($admin['full_name']); ?>
            </div>
        </div>
        
        <?php displayFlash(); ?>
