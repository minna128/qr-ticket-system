/**
 * Common JavaScript Utilities
 * World Play QR Ticketing System
 */

// AJAX Helper
async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const json = await response.json();
        return { ok: response.ok, status: response.status, data: json };
    } catch (error) {
        console.error('API Request failed:', error);
        return { ok: false, status: 0, data: { error: 'Network error' } };
    }
}

// Form data AJAX helper
async function apiFormRequest(url, formData) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const json = await response.json();
        return { ok: response.ok, status: response.status, data: json };
    } catch (error) {
        console.error('API Request failed:', error);
        return { ok: false, status: 0, data: { error: 'Network error' } };
    }
}

// Show alert message
function showAlert(container, type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    // Remove existing alerts
    const existing = container.querySelectorAll('.alert');
    existing.forEach(el => el.remove());
    
    container.prepend(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => alertDiv.remove(), 5000);
}

// Format currency
function formatCurrency(amount) {
    return 'LKR ' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Format time remaining
function formatTimeRemaining(seconds) {
    if (seconds <= 0) return 'EXPIRED';
    
    const hours = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hours > 0) {
        return `${hours}h ${mins}m ${secs}s`;
    }
    return `${mins}m ${secs}s`;
}

// Countdown timer class
class CountdownTimer {
    constructor(elementId, endTime, onExpire) {
        this.element = document.getElementById(elementId);
        this.endTime = new Date(endTime).getTime();
        this.onExpire = onExpire;
        this.interval = null;
    }

    start() {
        this.update();
        this.interval = setInterval(() => this.update(), 1000);
    }

    update() {
        const now = new Date().getTime();
        const remaining = Math.floor((this.endTime - now) / 1000);

        if (remaining <= 0) {
            this.element.textContent = 'EXPIRED';
            this.element.classList.add('text-danger');
            clearInterval(this.interval);
            if (this.onExpire) this.onExpire();
            return;
        }

        this.element.textContent = formatTimeRemaining(remaining);

        // Color coding
        if (remaining <= 300) { // 5 min
            this.element.style.color = '#ff4757';
        } else if (remaining <= 600) { // 10 min
            this.element.style.color = '#ffa502';
        } else {
            this.element.style.color = '#2ed573';
        }
    }

    stop() {
        if (this.interval) clearInterval(this.interval);
    }
}
// app.js
exports.handler = async (event) => {
    // This allows your PHP site to talk to AWS without security blocks (CORS)
    const headers = {
        "Access-Control-Allow-Origin": "*",
        "Access-Control-Allow-Headers": "Content-Type",
        "Access-Control-Allow-Methods": "OPTIONS,POST,GET"
    };

    // Prepare a friendly message to send back to your PHP site
    const responseBody = {
        message: "Hello World! Your AWS Lambda backend is working perfectly.",
        timestamp: new Date().toISOString()
    };

    return {
        statusCode: 200,
        headers: headers,
        body: JSON.stringify(responseBody),
    };
};
