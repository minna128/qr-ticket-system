/**
 * Kiosk JavaScript - Phone Validation & Form Interactions
 */

// Phone number validation
function validatePhoneNumber(phone) {
    // Remove spaces, dashes, etc.
    phone = phone.replace(/[\s\-\(\)]/g, '');
    
    // Sri Lankan patterns
    const patterns = [
        /^07[01245678]\d{7}$/,        // 07X XXXXXXX (10 digits)
        /^947[01245678]\d{7}$/,       // 947X XXXXXXX (11 digits)
        /^\+947[01245678]\d{7}$/      // +947X XXXXXXX (12 chars)
    ];
    
    return patterns.some(p => p.test(phone));
}

// Format phone for display
function formatPhoneDisplay(phone) {
    phone = phone.replace(/[\s\-\(\)]/g, '');
    if (phone.startsWith('+94')) {
        phone = '0' + phone.substring(3);
    } else if (phone.startsWith('94') && phone.length === 11) {
        phone = '0' + phone.substring(2);
    }
    // Format as 07X XXX XXXX
    if (phone.length === 10 && phone.startsWith('0')) {
        return phone.substring(0, 3) + ' ' + phone.substring(3, 6) + ' ' + phone.substring(6);
    }
    return phone;
}

// Duration selection handler
document.addEventListener('DOMContentLoaded', function() {
    // Duration card selection
    const durationCards = document.querySelectorAll('.duration-card');
    const durationInput = document.getElementById('selected-pricing-id');
    
    if (durationCards.length > 0) {
        durationCards.forEach(card => {
            card.addEventListener('click', function() {
                durationCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                if (durationInput) {
                    durationInput.value = this.dataset.pricingId;
                }
                // Enable continue button
                const continueBtn = document.getElementById('btn-continue');
                if (continueBtn) continueBtn.disabled = false;
            });
        });
    }
    
    // Game card selection (multiple selection)
    const gameCards = document.querySelectorAll('.game-card');
    const gameIdsInput = document.getElementById('selected-game-ids');
    const gamesCountEl = document.getElementById('games-count');
    const gamesTotalEl = document.getElementById('games-total');
    const grandTotalEl = document.getElementById('grand-total');
    
    if (gameCards.length > 0) {
        const selectedGames = new Set();
        const basePrice = parseFloat(grandTotalEl?.dataset.basePrice || 0);
        
        gameCards.forEach(card => {
            card.addEventListener('click', function() {
                const gameId = this.dataset.gameId;
                const gamePrice = parseFloat(this.dataset.price);
                
                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedGames.delete(gameId);
                } else {
                    this.classList.add('selected');
                    selectedGames.add(gameId);
                }
                
                // Update hidden input
                if (gameIdsInput) {
                    gameIdsInput.value = Array.from(selectedGames).join(',');
                }
                
                // Update summary
                const gamesCount = selectedGames.size;
                const gamesTotal = Array.from(selectedGames).reduce((sum, id) => {
                    const card = document.querySelector(`.game-card[data-game-id="${id}"]`);
                    return sum + (card ? parseFloat(card.dataset.price) : 0);
                }, 0);
                
                if (gamesCountEl) gamesCountEl.textContent = gamesCount;
                if (gamesTotalEl) gamesTotalEl.textContent = 'LKR ' + gamesTotal.toFixed(2);
                if (grandTotalEl) {
                    const total = basePrice + gamesTotal;
                    grandTotalEl.textContent = 'LKR ' + total.toFixed(2);
                }
            });
        });
    }
    
    // Payment method selection
    const paymentCards = document.querySelectorAll('.payment-card');
    const paymentInput = document.getElementById('selected-payment-method');
    
    if (paymentCards.length > 0) {
        paymentCards.forEach(card => {
            card.addEventListener('click', function() {
                paymentCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                if (paymentInput) {
                    paymentInput.value = this.dataset.method;
                }
                const continueBtn = document.getElementById('btn-continue');
                if (continueBtn) continueBtn.disabled = false;
            });
        });
    }
    
    // Phone validation on input
    const phoneInput = document.getElementById('phone-input');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const isValid = validatePhoneNumber(this.value);
            const continueBtn = document.getElementById('btn-continue');
            
            if (isValid) {
                this.style.borderColor = '#2ed573';
                if (continueBtn) continueBtn.disabled = false;
            } else {
                this.style.borderColor = this.value.length > 0 ? '#ff4757' : '';
                if (continueBtn) continueBtn.disabled = true;
            }
        });
    }
    
    // Card payment form handling
    const cardNumberInput = document.getElementById('card-number');
    const cardNameInput = document.getElementById('card-name');
    const expiryInput = document.getElementById('card-expiry');
    const cvvInput = document.getElementById('card-cvv');
    const cardNumberDisplay = document.querySelector('.card-number-display');
    const cardNameDisplay = document.querySelector('.card-name-display');
    const cardExpiryDisplay = document.querySelector('.card-expiry-display');
    
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            value = value.substring(0, 16);
            
            // Format with spaces
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            this.value = formatted;
            
            // Update card preview
            if (cardNumberDisplay) {
                const display = formatted || '•••• •••• •••• ••••';
                cardNumberDisplay.textContent = display;
            }
            
            // Validate
            if (value.length === 16) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
    }
    
    if (cardNameInput) {
        cardNameInput.addEventListener('input', function() {
            if (cardNameDisplay) {
                cardNameDisplay.textContent = this.value || 'CARDHOLDER NAME';
            }
        });
    }
    
    if (expiryInput) {
        expiryInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value.substring(0, 5);
            
            if (cardExpiryDisplay) {
                cardExpiryDisplay.textContent = value || 'MM/YY';
            }
            
            if (value.length === 5) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
    }
    
    if (cvvInput) {
        cvvInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
            
            if (this.value.length >= 3) {
                this.classList.remove('error');
                this.classList.add('success');
            } else {
                this.classList.remove('success');
            }
        });
    }
    
    // Card payment form submission
    const cardPaymentForm = document.getElementById('card-payment-form');
    if (cardPaymentForm) {
        cardPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            
            const cardNumber = cardNumberInput?.value.replace(/\s/g, '');
            if (!cardNumber || cardNumber.length !== 16) {
                cardNumberInput?.classList.add('error');
                isValid = false;
            }
            
            const cardName = cardNameInput?.value;
            if (!cardName || cardName.trim().length < 3) {
                cardNameInput?.classList.add('error');
                isValid = false;
            }
            
            const expiry = expiryInput?.value;
            if (!expiry || expiry.length !== 5) {
                expiryInput?.classList.add('error');
                isValid = false;
            }
            
            const cvv = cvvInput?.value;
            if (!cvv || cvv.length < 3) {
                cvvInput?.classList.add('error');
                isValid = false;
            }
            
            if (isValid) {
                // Show processing overlay
                const overlay = document.getElementById('processing-overlay');
                if (overlay) {
                    overlay.classList.add('show');
                }
                
                // Simulate processing delay
                setTimeout(() => {
                    this.submit();
                }, 2000);
            }
        });
    }
});
