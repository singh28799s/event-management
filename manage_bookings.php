<?php
session_start();
include('db_connection.php');

// Only admin allowed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// Delete booking
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM bookings WHERE booking_id = $id");
    $msg = "<div class='alert alert-success'>Booking deleted successfully.</div>";
}

// Fetch all bookings with user, event, and ticket info
$sql = "
    SELECT 
        b.booking_id, b.quantity, b.total_amount, b.booking_date,
        u.name AS user_name,
        e.title AS event_title,
        t.ticket_type
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN events e ON b.event_id = e.event_id
    JOIN tickets t ON b.ticket_id = t.ticket_id
    ORDER BY b.booking_id DESC
";

$bookings = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">📋 Manage All Bookings</h2>

  <?= $msg ?>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Booking ID</th>
        <th>User</th>
        <th>Event</th>
        <th>Ticket Type</th>
        <th>Quantity</th>
        <th>Total Amount</th>
        <th>Booking Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($bookings->num_rows > 0): ?>
      <?php while ($row = $bookings->fetch_assoc()): ?>
        <tr>
          <td><?= $row['booking_id'] ?></td>
          <td><?= htmlspecialchars($row['user_name']) ?></td>
          <td><?= htmlspecialchars($row['event_title']) ?></td>
          <td><?= htmlspecialchars($row['ticket_type']) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>₹<?= number_format($row['total_amount'], 2) ?></td>
          <td><?= date('d M Y, h:i A', strtotime($row['booking_date'])) ?></td>
          <td>
            <a href="?delete=<?= $row['booking_id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to delete this booking?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8" class="text-center text-muted">No bookings found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
