<?php
session_start();
include('db_connection.php');

// Only allow logged-in organizers
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user']['user_id'];

// Fetch Stats
$total_events = $conn->query("SELECT COUNT(*) FROM events WHERE organizer_id = $organizer_id")->fetch_row()[0];
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings 
    JOIN events ON bookings.event_id = events.event_id 
    WHERE events.organizer_id = $organizer_id")->fetch_row()[0];

$total_revenue = $conn->query("SELECT SUM(bookings.total_amount) FROM bookings 
    JOIN events ON bookings.event_id = events.event_id 
    WHERE events.organizer_id = $organizer_id")->fetch_row()[0] ?? 0;

$total_reviews = $conn->query("SELECT COUNT(*) FROM reviews 
    JOIN events ON reviews.event_id = events.event_id 
    WHERE events.organizer_id = $organizer_id")->fetch_row()[0];

// Upcoming events
$events = $conn->query("SELECT * FROM events WHERE organizer_id = $organizer_id AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">🎉 Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h2>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-bg-primary shadow">
                <div class="card-body text-center">
                    <h4><?= $total_events ?></h4>
                    <p>Total Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success shadow">
                <div class="card-body text-center">
                    <h4><?= $total_bookings ?></h4>
                    <p>Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning shadow">
                <div class="card-body text-center">
                    <h4>₹<?= number_format($total_revenue, 2) ?></h4>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info shadow">
                <div class="card-body text-center">
                    <h4><?= $total_reviews ?></h4>
                    <p>Reviews</p>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h4 class="mb-3">📅 Upcoming Events</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events->num_rows > 0): ?>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= $event['event_date'] ?></td>
                            <td><?= htmlspecialchars($event['venue']) ?></td>
                            <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No upcoming events found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex gap-3">
        <a href="create_event.php" class="btn btn-primary">➕ Create New Event</a>
        <a href="list_events.php" class="btn btn-outline-dark">📋 Manage Events</a>
        <a href="list_tickets.php" class="btn btn-outline-dark">🎫 Manage Tickets</a>
        <a href="logout.php" class="btn btn-danger ms-auto">Logout</a>
    </div>
</div>
</body>
</html>
