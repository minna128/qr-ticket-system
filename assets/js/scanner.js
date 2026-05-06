/**
 * QR Scanner JavaScript
 * Uses html5-qrcode library for camera scanning
 */

let html5QrcodeScanner = null;

function initScanner(onScanSuccess) {
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };

    html5QrcodeScanner = new Html5QrcodeScanner("qr-reader", config, false);
    
    html5QrcodeScanner.render((decodedText, decodedResult) => {
        // Stop scanning after successful read
        html5QrcodeScanner.clear();
        onScanSuccess(decodedText);
    }, (errorMessage) => {
        // Scan error - ignore (continuous scanning)
    });
}

function stopScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
}

// Entry scan handler
function initEntryScanner() {
    const resultDiv = document.getElementById('scan-result');
    const manualInput = document.getElementById('manual-ticket-id');
    const manualBtn = document.getElementById('btn-manual-submit');

    function processTicketId(ticketId) {
        ticketId = ticketId.trim().toUpperCase();
        
        if (!ticketId.match(/^WP-[A-Z0-9]{6}$/)) {
            showScanResult(resultDiv, 'error', 'Invalid Ticket ID', 'Ticket ID must be in format WP-XXXXXX');
            return;
        }

        // Check ticket via API
        checkTicket(ticketId, resultDiv);
    }

    // Camera scan callback
    initScanner(processTicketId);

    // Manual entry
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

// Check ticket via API
async function checkTicket(ticketId, resultDiv) {
    resultDiv.innerHTML = '<div class="spinner"></div><p class="text-center">Checking ticket...</p>';
    resultDiv.classList.add('show');
    resultDiv.className = 'scan-result show';

    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/worldplay';
    const response = await apiRequest(`${baseUrl}/api/check-ticket.php?ticket_id=${encodeURIComponent(ticketId)}`);

    if (response.ok && response.data.valid) {
        const ticket = response.data.ticket;
        resultDiv.innerHTML = `
            <div class="result-header">
                <span class="result-icon">&#9989;</span>
                <div>
                    <h3>Valid Ticket Found</h3>
                    <p style="color: var(--text-muted); margin:0;">Ready to activate</p>
                </div>
            </div>
            <div class="result-details">
                <div class="detail-row">
                    <span>Ticket ID</span>
                    <span><strong>${ticket.ticket_id}</strong></span>
                </div>
                <div class="detail-row">
                    <span>Duration</span>
                    <span>${ticket.duration_minutes} minutes</span>
                </div>
                <div class="detail-row">
                    <span>Payment</span>
                    <span>${ticket.payment_method} - Confirmed</span>
                </div>
                <div class="detail-row">
                    <span>Status</span>
                    <span><span class="badge badge-pending">${ticket.status}</span></span>
                </div>
            </div>
            <div class="action-buttons">
                <button onclick="activateTicket('${ticket.ticket_id}')" class="btn btn-secondary btn-lg btn-block">
                    Activate Session
                </button>
            </div>
        `;
        resultDiv.classList.add('success');
    } else {
        const errorMsg = response.data.error || 'Ticket not found or invalid';
        showScanResult(resultDiv, 'error', 'Cannot Proceed', errorMsg);
    }
}

// Activate ticket
async function activateTicket(ticketId) {
    const resultDiv = document.getElementById('scan-result');
    resultDiv.innerHTML = '<div class="spinner"></div><p class="text-center">Activating session...</p>';

    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/worldplay';
    const response = await apiFormRequest(`${baseUrl}/api/activate-ticket.php`, new URLSearchParams({
        ticket_id: ticketId,
        csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
    }));

    if (response.ok && response.data.success) {
        const session = response.data.session;
        resultDiv.className = 'scan-result show success';
        resultDiv.innerHTML = `
            <div class="result-header">
                <span class="result-icon">&#127881;</span>
                <div>
                    <h3>Session Activated!</h3>
                    <p style="color: var(--success); margin:0;">Entry approved</p>
                </div>
            </div>
            <div class="result-details">
                <div class="detail-row">
                    <span>Ticket ID</span>
                    <span><strong>${ticketId}</strong></span>
                </div>
                <div class="detail-row">
                    <span>Entry Time</span>
                    <span>${session.entry_time}</span>
                </div>
                <div class="detail-row">
                    <span>Expected Exit</span>
                    <span>${session.expected_exit_time}</span>
                </div>
                <div class="detail-row">
                    <span>Duration</span>
                    <span>${session.duration_minutes} minutes</span>
                </div>
            </div>
            <div class="action-buttons">
                <button onclick="resetScanner()" class="btn btn-primary btn-block">
                    Scan Next Ticket
                </button>
            </div>
        `;
    } else {
        const errorMsg = response.data.error || 'Activation failed';
        showScanResult(resultDiv, 'error', 'Activation Failed', errorMsg);
    }
}

// Show scan result (error/success)
function showScanResult(container, type, title, message) {
    container.className = `scan-result show ${type}`;
    const icon = type === 'error' ? '&#10060;' : '&#9989;';
    container.innerHTML = `
        <div class="result-header">
            <span class="result-icon">${icon}</span>
            <div>
                <h3>${title}</h3>
                <p style="color: var(--text-muted); margin:0;">${message}</p>
            </div>
        </div>
        <div class="action-buttons">
            <button onclick="resetScanner()" class="btn btn-outline btn-block">
                Try Again
            </button>
        </div>
    `;
}

// Reset scanner for next scan
function resetScanner() {
    const resultDiv = document.getElementById('scan-result');
    resultDiv.classList.remove('show', 'success', 'error');
    resultDiv.innerHTML = '';
    
    const manualInput = document.getElementById('manual-ticket-id');
    if (manualInput) manualInput.value = '';
    
    // Reinitialize scanner
    if (document.getElementById('qr-reader')) {
        initScanner(function(ticketId) {
            ticketId = ticketId.trim().toUpperCase();
            if (ticketId.match(/^WP-[A-Z0-9]{6}$/)) {
                checkTicket(ticketId, resultDiv);
            } else {
                showScanResult(resultDiv, 'error', 'Invalid QR Code', 'This QR code does not contain a valid ticket ID.');
            }
        });
    }
}
