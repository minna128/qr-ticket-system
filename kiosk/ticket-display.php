<?php
/**
 * Kiosk - Ticket Display (QR Code + Details)
 * World Play QR Ticketing System
 */

$pageTitle = 'Your Ticket';
$bodyClass = 'kiosk-page';
$extraCSS = ['kiosk.css'];
require_once __DIR__ . '/../includes/header.php';

// Check for generated ticket data
if (!isset($_SESSION['generated_ticket'])) {
    redirect('/kiosk/');
}

$ticket = $_SESSION['generated_ticket'];
unset($_SESSION['generated_ticket']);
?>

<div class="kiosk-container">
    <div class="qr-display">
        <div class="alert alert-success">
            <?php echo sanitize(t('kiosk.ticket.success')); ?>
        </div>
        
        <h2><?php echo sanitize(t('kiosk.ticket.ready')); ?></h2>
        <p><?php echo sanitize(t('kiosk.ticket.show_qr')); ?></p>
        
        <img src="<?php echo sanitize($ticket['qr_path']); ?>" alt="QR Code - <?php echo sanitize($ticket['ticket_id']); ?>">

        <p class="mt-2" style="color: var(--text-muted);">
            <?php echo sanitize(t('kiosk.ticket.photo_hint')); ?>
        </p>
        
        <div class="ticket-info">
            <div class="ticket-id"><?php echo sanitize($ticket['ticket_id']); ?></div>
            
            <div class="ticket-detail">
                <span><?php echo sanitize(t('kiosk.ticket.duration')); ?></span>
                <span><?php echo (int)$ticket['duration']; ?> minutes</span>
            </div>
            <div class="ticket-detail">
                <span><?php echo sanitize(t('kiosk.ticket.amount_paid')); ?></span>
                <span><?php echo formatCurrency($ticket['price']); ?></span>
            </div>
            <div class="ticket-detail">
                <span><?php echo sanitize(t('kiosk.confirm.payment_method')); ?></span>
                <span><?php echo sanitize($ticket['payment_method']); ?></span>
            </div>
            <div class="ticket-detail">
                <span><?php echo sanitize(t('kiosk.phone')); ?></span>
                <span><?php echo sanitize($ticket['phone']); ?></span>
            </div>
            <div class="ticket-detail">
                <span><?php echo sanitize(t('kiosk.ticket.status')); ?></span>
                <span><span class="badge badge-pending"><?php echo sanitize(t('kiosk.ticket.not_activated')); ?></span></span>
            </div>
        </div>

        <a href="<?php echo BASE_URL; ?>/kiosk/" class="btn btn-primary btn-lg mt-3"><?php echo sanitize(t('kiosk.ticket.done_new')); ?></a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
