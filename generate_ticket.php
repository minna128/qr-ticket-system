<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$phone = trim($_POST["phone"]);
$duration = trim($_POST["duration"]);
$payment_method = trim($_POST["payment_method"]);

if (empty($phone) || empty($duration) || empty($payment_method)) {
    die("All fields are required.");
}

$ticket_id = "TKT" . rand(100000, 999999);
$qr_data = $ticket_id;

$stmt = mysqli_prepare($conn, "INSERT INTO tickets (phone, duration, payment_method, ticket_id, qr_data, status) VALUES (?, ?, ?, ?, ?, 'Not Activated')");
mysqli_stmt_bind_param($stmt, "sisss", $phone, $duration, $payment_method, $ticket_id, $qr_data);

if (!mysqli_stmt_execute($stmt)) {
    die("Error saving ticket: " . mysqli_error($conn));
}

$qr_image = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Generated</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Ticket Generated</h1>
        <p class="subtitle">Your digital ticket is ready. Use this QR code at the entry point.</p>

        <div class="ticket-box">
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method); ?></p>
            <div class="ticket-id"><?php echo htmlspecialchars($ticket_id); ?></div>
            <span class="status-badge">Not Activated</span>
            <img src="<?php echo $qr_image; ?>" alt="QR Code">
        </div>

        <div class="success-box">
            Ticket created successfully.
        </div>

        <a class="back-link" href="index.php">Create Another Ticket</a>
    </div>
</body>
</html>