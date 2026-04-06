<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$phone = trim($_POST["phone"]);
$duration = trim($_POST["duration"]);

if (empty($phone) || empty($duration)) {
    die("Phone number and duration are required.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Confirm Payment</h1>
        <p class="subtitle">Review the details and confirm payment to generate your ticket.</p>

        <div class="card-info">
            <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($phone); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($duration); ?> minutes</p>
        </div>

        <form action="generate_ticket.php" method="POST">
            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            <input type="hidden" name="duration" value="<?php echo htmlspecialchars($duration); ?>">

            <label for="payment_method">Payment Method</label>
            <select id="payment_method" name="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
            </select>

            <button type="submit">Confirm Payment</button>
        </form>

        <a class="back-link" href="index.php">Back to Kiosk</a>
    </div>
</body>
</html>