<?php
/**
 * Exit Gate - QR Scanner Interface
 * World Play QR Ticketing System
 */

$pageTitle = 'Exit Scanner';
$bodyClass = 'scanner-page';
$extraCSS = ['scanner.css'];
$extraJS = ['scanner.js'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/auth.php';

if (!requireStaff()) {
    header("Location: " . BASE_URL . "/exit/");
    exit;
}
?>

<meta name="base-url" content="<?php echo BASE_URL; ?>">

<div class="scanner-container">
    <div class="scanner-header">
        <h2>Exit Gate - Scan Ticket</h2>
        <p>Scan the visitor's QR code to process their exit</p>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Staff: <?php echo sanitize($_SESSION['staff_name']); ?></p>
    </div>
    
    <?php echo csrfField(); ?>
    
    <div class="scanner-viewport">
        <div id="qr-reader"></div>
    </div>
    
    <div class="scanner-divider">
        <span>OR ENTER MANUALLY</span>
    </div>
    
    <div class="manual-entry">
        <input type="text" 
               id="manual-ticket-id" 
               placeholder="WP-XXXXXX" 
               maxlength="9"
               autocomplete="off">
        <button id="btn-manual-submit" class="btn btn-primary">Check</button>
    </div>
    
    <div id="scan-result" class="scan-result"></div>
    
    <div class="text-center mt-4">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-outline">Back to Home</a>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initExitScanner();
});

function initExitScanner() {
    const resultDiv = document.getElementById('scan-result');
    const manualInput = document.getElementById('manual-ticket-id');
    const manualBtn = document.getElementById('btn-manual-submit');

    function processTicketId(ticketId) {
        ticketId = ticketId.trim().toUpperCase();
        
        if (!ticketId.match(/^WP-[A-Z0-9]{6}$/)) {
            showScanResult(resultDiv, 'error', 'Invalid Ticket ID', 'Ticket ID must be in format WP-XXXXXX');
            return;
        }
        checkExitTicket(ticketId, resultDiv);
    }

    initScanner(processTicketId);

    if (manualBtn) {
        manualBtn.addEventListener('click', function() {
            processTicketId(manualInput.value);
        });
    }

    if (manualInput) {
        manualInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processTicketId(this.value);
            }
        });
    }
}

async function checkExitTicket(ticketId, resultDiv) {
    resultDiv.innerHTML = '<div class="spinner"></div><p class="text-center">Processing exit...</p>';
    resultDiv.classList.add('show');
    resultDiv.className = 'scan-result show';

    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/worldplay';
    const response = await apiRequest(`${baseUrl}/api/exit-ticket.php?ticket_id=${encodeURIComponent(ticketId)}`);

    if (response.ok && response.data.success) {
        const data = response.data;
        let overstayHtml = '';
        
        if (data.overstay_minutes > 0) {
            overstayHtml = `
                <div class="detail-row" style="color: var(--danger);">
                    <span><strong>Overstay</strong></span>
                    <span><strong>${data.overstay_minutes} minutes</strong></span>
                </div>
                <div class="detail-row" style="color: var(--danger);">
                    <span><strong>Extra Charge</strong></span>
                    <span><strong>${data.extra_charge_formatted}</strong></span>
                </div>
            `;
            resultDiv.classList.add('error');
        } else {
            overstayHtml = `
                <div class="detail-row" style="color: var(--success);">
                    <span><strong>Status</strong></span>
                    <span><strong>On Time - No extra charge</strong></span>
                </div>
            `;
            resultDiv.classList.add('success');
        }

        resultDiv.innerHTML = `
            <div class="result-header">
                <span class="result-icon">${data.overstay_minutes > 0 ? '&#9888;' : '&#9989;'}</span>
                <div>
                    <h3>${data.overstay_minutes > 0 ? 'Overstay Detected' : 'Exit Ready'}</h3>
                    <p style="color: var(--text-muted); margin:0;">Session summary below</p>
                </div>
            </div>
            <div class="result-details">
                <div class="detail-row">
                    <span>Ticket ID</span>
                    <span><strong>${data.ticket_id}</strong></span>
                </div>
                <div class="detail-row">
                    <span>Entry Time</span>
                    <span>${data.entry_time}</span>
                </div>
                <div class="detail-row">
                    <span>Expected Exit</span>
                    <span>${data.expected_exit_time}</span>
                </div>
                <div class="detail-row">
                    <span>Actual Exit</span>
                    <span>${data.actual_exit_time}</span>
                </div>
                <div class="detail-row">
                    <span>Duration Purchased</span>
                    <span>${data.duration_minutes} min</span>
                </div>
                <div class="detail-row">
                    <span>Actual Duration</span>
                    <span>${data.actual_duration} min</span>
                </div>
                ${overstayHtml}
            </div>
            <div class="action-buttons">
                <button onclick="completeExit('${data.ticket_id}')" class="btn btn-secondary btn-lg btn-block">
                    ${data.overstay_minutes > 0 ? 'Confirm Payment & Close Session' : 'Close Session & Allow Exit'}
                </button>
            </div>
        `;
    } else {
        const errorMsg = response.data.error || 'Cannot process exit';
        showScanResult(resultDiv, 'error', 'Exit Error', errorMsg);
    }
}

async function completeExit(ticketId) {
    const resultDiv = document.getElementById('scan-result');
    resultDiv.innerHTML = '<div class="spinner"></div><p class="text-center">Closing session...</p>';

    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/worldplay';
    const response = await apiFormRequest(`${baseUrl}/api/exit-ticket.php`, new URLSearchParams({
        ticket_id: ticketId,
        action: 'complete',
        csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
    }));

    if (response.ok && response.data.success) {
        resultDiv.className = 'scan-result show success';
        resultDiv.innerHTML = `
            <div class="result-header">
                <span class="result-icon">&#128075;</span>
                <div>
                    <h3>Session Closed</h3>
                    <p style="color: var(--success); margin:0;">Exit approved. Thank you!</p>
                </div>
            </div>
            <div class="action-buttons">
                <button onclick="resetScanner()" class="btn btn-primary btn-block">
                    Scan Next Ticket
                </button>
            </div>
        `;
    } else {
        showScanResult(resultDiv, 'error', 'Error', response.data.error || 'Failed to close session');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
