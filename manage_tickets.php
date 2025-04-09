<?php
session_start();
include('db_connection.php');

// Only admin allowed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// Delete ticket
if (isset($_GET['delete'])) {
    $ticket_id = $_GET['delete'];
    $conn->query("DELETE FROM tickets WHERE ticket_id = $ticket_id");
    $msg = "<div class='alert alert-success'>🗑️ Ticket deleted successfully.</div>";
}

// Fetch all tickets with event and organizer info
$sql = "
    SELECT 
        t.ticket_id, t.ticket_type, t.price, t.quantity,
        e.event_id, e.title AS event_title,
        u.name AS organizer_name
    FROM tickets t
    JOIN events e ON t.event_id = e.event_id
    JOIN users u ON e.organizer_id = u.user_id
    ORDER BY t.ticket_id DESC
";
$tickets = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Tickets - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Neon Dark Background */
body {
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
  font-family: 'Segoe UI', sans-serif;
  color: #f0f0f0;
}

/* Main Container */
.container {
  background-color: rgba(255, 255, 255, 0.04);
  padding: 30px;
  border-radius: 15px;
  margin-top: 40px;
  box-shadow: 0 0 25px rgba(0, 255, 255, 0.12);
}

/* Heading */
h2 {
  color: #00ffff;
  text-align: center;
  text-shadow: 0 0 6px #00ffff;
  margin-bottom: 30px;
}

/* Table Styling */
.table {
  background-color: rgba(255, 255, 255, 0.03);
  color: #fff;
  border-radius: 10px;
  overflow: hidden;
}

.table th,
.table td {
  vertical-align: middle;
  text-align: center;
}

.table th {
  background-color: #003344 !important;
  color: #00ffff !important;
  border-bottom: 2px solid #00ffff55;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 255, 255, 0.07);
}

/* Buttons */
.btn-danger {
  background: linear-gradient(135deg, #ff4e50, #f9d423);
  color: #000;
  font-weight: bold;
  border: none;
  box-shadow: 0 0 10px rgba(255, 100, 100, 0.5);
}

.btn-danger:hover {
  transform: scale(1.05);
  opacity: 0.9;
}

/* Muted message row */
.text-muted {
  color: #cccccc !important;
}
</style>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">🎫 Manage All Tickets (Admin)</h2>

    <?= $msg ?>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Ticket ID</th>
                <th>Event</th>
                <th>Organizer</th>
                <th>Type</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($tickets->num_rows > 0): ?>
            <?php while ($ticket = $tickets->fetch_assoc()): ?>
                <tr>
                    <td><?= $ticket['ticket_id'] ?></td>
                    <td><?= htmlspecialchars($ticket['event_title']) ?> (#<?= $ticket['event_id'] ?>)</td>
                    <td><?= htmlspecialchars($ticket['organizer_name']) ?></td>
                    <td><?= htmlspecialchars($ticket['ticket_type']) ?></td>
                    <td>₹<?= number_format($ticket['price'], 2) ?></td>
                    <td><?= $ticket['quantity'] ?></td>
                    <td>
                        <a href="?delete=<?= $ticket['ticket_id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this ticket?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted">No tickets found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
