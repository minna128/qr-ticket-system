<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Play Ticket Kiosk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🌟 WORLD PLAY</h1>
        <p class="subtitle"> <div class="welcome">Welcome to World Play Arcade!</div> Grab your play session fast • Get QR ticket instantly</p>

        <form action="payment.php" method="POST" id="ticketForm">
            <label for="phone">Phone Number (+94)</label>
            <input type="tel" id="phone" name="phone" placeholder="07X XXX XXXX" required 
                   pattern="^(?:0|\+94)?7[01245678]\d{7}$" 
                   title="Enter valid Sri Lankan mobile number (e.g. 0712345678 or +94712345678)">

            <label for="duration">Select Play Duration</label>
            <select id="duration" name="duration" required>
                <option value="">-- Select Duration --</option>
                <option value="30">30 Minutes</option>
                <option value="60">60 Minutes</option>
            </select>

            <button type="submit">Proceed to Payment →</button>
        </form>

        <div id="errorMessage" class="error-box" style="display: none;"></div>
    </div>

    <script>
        // Basic client-side validation
        document.getElementById('ticketForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value.trim();
            const errorDiv = document.getElementById('errorMessage');
            
            errorDiv.style.display = 'none';
            
            if (!phone) {
                e.preventDefault();
                errorDiv.textContent = "Phone number is required.";
                errorDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>