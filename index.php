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
        <h1>World Play Ticket Kiosk</h1>
        <p class="subtitle">Purchase your play session quickly and receive a digital QR ticket.</p>

        <form action="payment.php" method="POST">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number" required>

            <label for="duration">Select Duration</label>
            <select id="duration" name="duration" required>
                <option value="">-- Select Duration --</option>
                <option value="30">30 Minutes</option>
                <option value="60">60 Minutes</option>
            </select>

            <button type="submit">Proceed to Payment</button>
        </form>
    </div>
</body>
</html>