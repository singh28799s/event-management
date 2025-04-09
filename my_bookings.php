<?php
session_start();
require_once "db_connection.php";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Get logged-in user ID
$user_id = $_SESSION['user']['user_id'];

// Query to get all bookings with event and ticket info
$sql = "SELECT 
            b.booking_id,
            b.quantity,
            b.total_amount,
            b.booking_date,
            e.title AS event_title,
            e.image AS event_image,
            e.event_date,
            t.ticket_type,
            t.price
        FROM bookings b
        JOIN events e ON b.event_id = e.event_id
        JOIN tickets t ON b.ticket_id = t.ticket_id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>/* Apply background and font */
body {
  background: linear-gradient(to right, #000428, #004e92);
  font-family: 'Segoe UI', sans-serif;
  color: #fff;
  min-height: 100vh;
  padding: 20px 0;
}

/* Page heading */
h2 {
  text-align: center;
  font-size: 2.5rem;
  text-shadow: 0 0 10px #0ff, 0 0 20px #00f;
  margin-bottom: 40px;
}

/* Card style */
.card {
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
  color: #fff;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: scale(1.03);
  box-shadow: 0 0 20px #0ff;
}

.card-title {
  color: #0ff;
  text-shadow: 0 0 5px #0ff;
}

.card-img-top {
  max-height: 200px;
  object-fit: cover;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}

p {
  margin-bottom: 6px;
  font-size: 0.95rem;
}

.alert-info {
  background-color: rgba(0, 255, 255, 0.1);
  border: 1px solid #0ff;
  color: #0ff;
  text-align: center;
  border-radius: 10px;
  box-shadow: 0 0 10px #0ff;
}
</style>
</head>
<body>
<div class="container my-5">
  <h2 class="mb-4">My Bookings</h2>

  <?php if (empty($bookings)): ?>
    <div class="alert alert-info">You have not booked any tickets yet.</div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($bookings as $b): ?>
        <div class="col-md-4 mb-4">
          <div class="card">
            <?php if (!empty($b['event_image'])): ?>
              <img src="<?= htmlspecialchars($b['event_image']) ?>" class="card-img-top" alt="Event Image">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($b['event_title']) ?></h5>
              <p><strong>Event Date:</strong> <?= $b['event_date'] ?></p>
              <p><strong>Ticket Type:</strong> <?= $b['ticket_type'] ?> | ₹<?= $b['price'] ?></p>
              <p><strong>Quantity:</strong> <?= $b['quantity'] ?></p>
              <p><strong>Total Paid:</strong> ₹<?= $b['total_amount'] ?></p>
              <p><strong>Booked On:</strong> <?= $b['booking_date'] ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
