<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$phone = trim($_POST["phone"] ?? '');
$duration = trim($_POST["duration"] ?? '');
$payment_method = trim($_POST["payment_method"] ?? '');

// Stronger Input Validation
$errors = [];

if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^(?:0|\+94)?7[01245678]\d{7}$/', $phone)) {
    $errors[] = "Invalid Sri Lankan phone number format.";
}

if (empty($duration) || !in_array($duration, ['30', '60'])) {
    $errors[] = "Invalid duration selected.";
}

if (empty($payment_method) || !in_array($payment_method, ['Cash', 'Card'])) {
    $errors[] = "Please select a valid payment method.";
}

if (!empty($errors)) {
    $error_msg = implode("<br>", $errors);
    echo "<!DOCTYPE html>
    <html><head><title>Error</title><link rel='stylesheet' href='style.css'></head><body>
    <div class='container'>
        <h1>❌ Error</h1>
        <div class='error-box'>$error_msg</div>
        <a class='back-link' href='index.php'>← Try Again</a>
    </div></body></html>";
    exit();
}

// Generate Ticket
$ticket_id = "WPT" . rand(100000, 999999);   // Changed prefix to WPT for World Play Ticket
$qr_data = $ticket_id;

$stmt = mysqli_prepare($conn, "INSERT INTO tickets (phone, duration, payment_method, ticket_id, qr_data, status) 
                               VALUES (?, ?, ?, ?, ?, 'Not Activated')");

mysqli_stmt_bind_param($stmt, "sisss", $phone, $duration, $payment_method, $ticket_id, $qr_data);

if (!mysqli_stmt_execute($stmt)) {
    die("Error saving ticket: " . mysqli_error($conn));
}

$qr_image = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Ticket - World Play</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🎟️ Welcome to World Play – Your Ticket is Ready!</h1>
        <p class="subtitle">Show this QR code at the entry. Have fun playing!</p>

        <div class="ticket-box">
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
            
            <div class="ticket-id">
                <?php echo htmlspecialchars($ticket_id); ?>
            </div>
            
            <span class="status-badge">Not Activated</span>
            
            <img src="<?php echo $qr_image; ?>" alt="QR Code for Entry">
            
            <p class="ticket-note">Valid for <?php echo htmlspecialchars($duration); ?> minutes</p>
        </div>

        <div class="success-box">
            ✅ Ticket created successfully!<br>
            <strong>Scan at entry to activate.</strong>
        </div>

        <a class="back-link" href="index.php">Create Another Ticket</a>
    </div>
</body>
</html>