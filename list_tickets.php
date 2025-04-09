<?php
session_start();
include('db_connection.php');

// Ensure organizer is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user']['user_id'];

// Fetch tickets with event info
$sql = "SELECT t.*, e.title AS event_title 
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        WHERE e.organizer_id = ?
        ORDER BY t.ticket_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>List Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Your Tickets</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Event</th>
                <th>Type</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($ticket = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $ticket['ticket_id'] ?></td>
                    <td><?= htmlspecialchars($ticket['event_title']) ?></td>
                    <td><?= htmlspecialchars($ticket['ticket_type']) ?></td>
                    <td>₹<?= $ticket['price'] ?></td>
                    <td><?= $ticket['quantity'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No tickets found for your events.</div>
    <?php endif; ?>

    <a href="organizer_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
