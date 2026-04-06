<?php
include "db.php";

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ticket_id = trim($_POST["ticket_id"]);

    if (empty($ticket_id)) {
        $message = "Please enter a ticket ID.";
        $messageClass = "error-box";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT ticket_id, status FROM tickets WHERE ticket_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($row["status"] === "Not Activated") {
                $update = mysqli_prepare($conn, "UPDATE tickets SET status = 'Active' WHERE ticket_id = ?");
                mysqli_stmt_bind_param($update, "s", $ticket_id);
                mysqli_stmt_execute($update);

                $message = "Valid ticket. Session activated successfully.";
                $messageClass = "success-box";
            } else {
                $message = "Ticket already active or already used.";
                $messageClass = "error-box";
            }
        } else {
            $message = "Invalid ticket.";
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
    <title>Entry Validation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Entry Validation</h1>
        <p class="subtitle">Validate the visitor ticket by entering the ticket ID.</p>

        <form method="POST" action="">
            <label for="ticket_id">Ticket ID</label>
            <input type="text" id="ticket_id" name="ticket_id" placeholder="Enter ticket ID" required>
            <button type="submit">Validate Ticket</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $messageClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>