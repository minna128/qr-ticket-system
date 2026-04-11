<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$phone = trim($_POST["phone"] ?? '');
$duration = trim($_POST["duration"] ?? '');

// Input Validation
$errors = [];

if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^(?:0|\+94)?7[01245678]\d{7}$/', $phone)) {
    $errors[] = "Please enter a valid Sri Lankan mobile number (e.g. 0712345678 or +94712345678).";
}

if (empty($duration) || !in_array($duration, ['30', '60'])) {
    $errors[] = "Please select a valid duration.";
}

if (!empty($errors)) {
    // Redirect back with error (simple way - you can improve with session later)
    $error_msg = implode("<br>", $errors);
    echo "<!DOCTYPE html>
    <html><head><title>Error</title><link rel='stylesheet' href='style.css'></head><body>
    <div class='container'>
        <h1>❌ Oops!</h1>
        <div class='error-box'>$error_msg</div>
        <a class='back-link' href='index.php'>← Back to Kiosk</a>
    </div></body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment - World Play</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🎮 Confirm Your Session</h1>
        <p class="subtitle">Review details and choose payment method</p>

        <div class="card-info">
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($phone); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($duration); ?> Minutes</p>
        </div>

        <form action="generate_ticket.php" method="POST">
            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <input type="hidden" name="duration" value="<?php echo htmlspecialchars($duration); ?>">

            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash">💵 Cash</option>
                <option value="Card">💳 Card</option>
            </select>

            <button type="submit">Confirm & Generate Ticket →</button>
        </form>

        <a class="back-link" href="index.php">← Back to Kiosk</a>
    </div>
</body>
</html>