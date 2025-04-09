<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header('Location: index.php');
    exit();
}

require 'db.php'; // Make sure this file sets up $conn

$event_id = $_GET['id'] ?? null;

if (!$event_id) {
    echo "Invalid event ID.";
    exit();
}

// Fetch event details
$stmt = $conn->prepare("SELECT e.*, u.name AS organizer_name FROM events e JOIN users u ON e.organizer_id = u.user_id WHERE e.event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit();
}

// Fetch available tickets
$stmt = $conn->prepare("SELECT * FROM tickets WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$tickets = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $quantity = $_POST['quantity'];

    // Get ticket price and available quantity
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();

    if (!$ticket) {
        echo "Invalid ticket selected.";
        exit();
    }

    if ($quantity > $ticket['quantity']) {
        echo "Not enough tickets available.";
        exit();
    }

    $total = $quantity * $ticket['price'];
    $user_id = $_SESSION['user']['user_id'];

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id, ticket_id, quantity, total_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $user_id, $event_id, $ticket_id, $quantity, $total);
    $stmt->execute();

    // Reduce ticket stock
    $stmt = $conn->prepare("UPDATE tickets SET quantity = quantity - ? WHERE ticket_id = ?");
    $stmt->bind_param("ii", $quantity, $ticket_id);
    $stmt->execute();

    echo "<script>alert('✅ Booking Successful!'); window.location.href='my_bookings.php';</script>";
    exit();
}
?>

<!-- HTML Booking Form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container my-5">
    <h2 class="mb-4">Book Event: <?= htmlspecialchars($event['title']) ?></h2>
    <div class="card mb-4">
      <div class="card-body">
        <p><strong>Date:</strong> <?= $event['event_date'] ?></p>
        <p><strong>Organizer:</strong> <?= htmlspecialchars($event['organizer_name']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($event['description']) ?></p>
      </div>
    </div>

    <form method="POST">
      <div class="mb-3">
        <label for="ticket_id" class="form-label">Choose Ticket Type</label>
        <select name="ticket_id" id="ticket_id" class="form-select" required>
          <option value="">-- Select --</option>
          <?php while ($ticket = $tickets->fetch_assoc()): ?>
            <option value="<?= $ticket['ticket_id'] ?>">
              <?= htmlspecialchars($ticket['ticket_type']) ?> - ₹<?= $ticket['price'] ?> (<?= $ticket['quantity'] ?> left)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="quantity" class="form-label">Quantity</label>
        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
      </div>
      <button type="submit" class="btn btn-primary">Confirm Booking</button>
      <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</body>
</html>
