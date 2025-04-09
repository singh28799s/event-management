<?php
session_start();
require_once "db_connection.php";

// Fetch all events with organizer name
$sql = "SELECT 
            e.event_id,
            e.title,
            e.description,
            e.category,
            e.venue,
            e.event_date,
            e.start_time,
            e.end_time,
            e.image,
            u.name AS organizer_name
        FROM events e
        JOIN users u ON e.organizer_id = u.user_id
        ORDER BY e.event_date ASC";

$result = $conn->query($sql);
$events = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Events</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>/* Background and font */
body {
  background: linear-gradient(to right, #0f0c29, #302b63, #24243e);
  font-family: 'Segoe UI', sans-serif;
  color: #fff;
  min-height: 100vh;
  padding-top: 30px;
}

/* Section title */
h2 {
  color: #00ffff;
  text-shadow: 0 0 10px #00ffff, 0 0 20px #00f;
  font-weight: bold;
  font-size: 2.5rem;
  margin-bottom: 50px;
}

/* Event cards */
.card {
  background: rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(0, 255, 255, 0.3);
  border-radius: 15px;
  backdrop-filter: blur(8px);
  color: #fff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 0 10px rgba(0, 255, 255, 0.1);
}

.card:hover {
  transform: scale(1.03);
  box-shadow: 0 0 20px #00ffff, 0 0 30px #0077ff;
}

/* Card image */
.card-img-top {
  height: 200px;
  object-fit: cover;
  border-top-left-radius: 15px;
  border-top-right-radius: 15px;
}

/* Card content */
.card-title {
  color: #00ffff;
  font-weight: 600;
  text-shadow: 0 0 5px #00ffff;
}

.card-text {
  font-size: 0.95rem;
  margin-bottom: 6px;
}

/* Button styling */
.btn-primary {
  background-color: #00ffff;
  border: none;
  color: #000;
  font-weight: bold;
  box-shadow: 0 0 10px #00ffff, 0 0 20px #0077ff;
  transition: 0.3s ease;
}

.btn-primary:hover {
  background-color: #0077ff;
  color: #fff;
  box-shadow: 0 0 15px #00ffff, 0 0 25px #0077ff;
}

/* Alert message */
.alert-warning {
  background-color: rgba(255, 255, 0, 0.1);
  border: 1px solid yellow;
  color: yellow;
  box-shadow: 0 0 10px yellow;
  border-radius: 10px;
  padding: 20px;
}
</style>
</head>
<body>
<div class="container my-5">
  <h2 class="mb-4 text-center">Upcoming Events</h2>

  <?php if (empty($events)): ?>
    <div class="alert alert-warning text-center">No events available right now.</div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($events as $event): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <?php if (!empty($event['image'])): ?>
              <img src="<?= htmlspecialchars($event['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Event Image">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
              <p class="card-text"><strong>Date:</strong> <?= $event['event_date'] ?></p>
              <p class="card-text"><strong>Time:</strong> <?= date('h:i A', strtotime($event['start_time'])) ?> - <?= date('h:i A', strtotime($event['end_time'])) ?></p>
              <p class="card-text"><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
              <p class="card-text"><strong>Organizer:</strong> <?= htmlspecialchars($event['organizer_name']) ?></p>
              <p class="card-text text-truncate"><?= htmlspecialchars($event['description']) ?></p>
              <a href="event_details.php?id=<?= $event['event_id'] ?>" class="btn btn-primary mt-auto">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
