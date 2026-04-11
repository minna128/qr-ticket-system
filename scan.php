<?php
include "db.php";

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = strtoupper(trim($_POST["ticket_id"] ?? ''));

    if (empty($ticket_id)) {
        $message = "Please enter a ticket ID.";
        $messageClass = "error-box";
    } else {
        // Check if ticket exists
        $stmt = mysqli_prepare($conn, "SELECT ticket_id, status FROM tickets WHERE ticket_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row["status"] === "Not Activated") {
                // Activate the ticket
                $update = mysqli_prepare($conn, "UPDATE tickets SET status = 'Active' WHERE ticket_id = ?");
                mysqli_stmt_bind_param($update, "s", $ticket_id);
                mysqli_stmt_execute($update);

                $message = "✅ Ticket validated successfully!<br>Session is now ACTIVE.<br>Enjoy your play time!";
                $messageClass = "success-box";
            } else {
                $message = "⚠️ This ticket has already been activated or used.";
                $messageClass = "error-box";
            }
        } else {
            $message = "❌ Invalid ticket ID. Please check and try again.";
            $messageClass = "error-box";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entry Validation - World Play</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🔍 ENTRY VALIDATION</h1>
        <p class="subtitle">Scan or enter the ticket ID to activate the session</p>

        <form method="POST" action="">
            <label for="ticket_id">Ticket ID</label>
            <input type="text" id="ticket_id" name="ticket_id" 
                   placeholder="Enter Ticket ID (e.g. WPT123456)" 
                   style="text-transform: uppercase;" required>

            <button type="submit">Validate & Activate Ticket</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <a class="back-link" href="index.php">← Back to Kiosk</a>
    </div>
</body>
</html>