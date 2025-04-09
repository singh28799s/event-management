<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "event";

// Connect to database
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($event_id <= 0) {
    die("Invalid event ID.");
}

// Fetch event details with organizer info
$sql_event = "
    SELECT e.*, u.name AS organizer_name, u.email AS organizer_email
    FROM events e
    LEFT JOIN users u ON e.organizer_id = u.user_id
    WHERE e.event_id = $event_id
";
$result_event = $conn->query($sql_event);
if (!$result_event || $result_event->num_rows === 0) {
    die("Event not found.");
}
$event = $result_event->fetch_assoc();

// Fetch tickets
$sql_tickets = "SELECT * FROM tickets WHERE event_id = $event_id";
$result_tickets = $conn->query($sql_tickets);

// Fetch reviews
$sql_reviews = "
    SELECT r.*, u.name AS reviewer
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.user_id
    WHERE r.event_id = $event_id
    ORDER BY r.review_date DESC
";
$result_reviews = $conn->query($sql_reviews);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($event['title']); ?> - Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #f1f1f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background-color: #1a1a1a;
            border: 1px solid #0ff;
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.2);
        }

        .card-title {
            color: #0ff;
            text-shadow: 0 0 6px #0ff;
        }

        .card-img-top {
            max-height: 400px;
            object-fit: cover;
            border-bottom: 2px solid #0ff;
        }

        .list-group-item {
            background-color: #141414;
            border: 1px solid #0ff;
            color: #0ff;
        }

        .badge.bg-primary {
            background-color: #00ccff !important;
            color: black;
            font-weight: bold;
            box-shadow: 0 0 6px #00ccff;
        }

        .border.rounded.p-3 {
            background-color: #1f1f1f;
            border: 1px solid #444;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .text-warning {
            color: #ffc107;
            text-shadow: 0 0 2px #ffc107;
        }

        .btn-secondary {
            background-color: #333;
            border-color: #666;
            color: #0ff;
            box-shadow: 0 0 6px rgba(0, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background-color: #0ff;
            color: #000;
        }

        a {
            text-decoration: none;
        }

        h4 {
            color: #0ff;
            border-bottom: 2px solid #0ff;
            padding-bottom: 6px;
            margin-top: 40px;
        }

        p, small {
            color: #ccc;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-4">← Back to Home</a>

    <div class="card mb-4">
        <img src="<?php echo htmlspecialchars($event['image']); ?>" class="card-img-top" alt="Event Image">
        <div class="card-body">
            <h3 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h3>
            <p class="card-text"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?></p>
            <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></p>
            <p><strong>Organized by:</strong> <?php echo htmlspecialchars($event['organizer_name']); ?> (<?php echo htmlspecialchars($event['organizer_email']); ?>)</p>
        </div>
    </div>

    <h4>Available Tickets</h4>
    <?php if ($result_tickets->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($ticket = $result_tickets->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo htmlspecialchars($ticket['ticket_type']); ?> - ₹<?php echo $ticket['price']; ?>
                    <span class="badge bg-primary"><?php echo $ticket['quantity']; ?> left</span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No tickets available for this event.</p>
    <?php endif; ?>

    <h4>Reviews</h4>
    <?php if ($result_reviews->num_rows > 0): ?>
        <div class="mb-4">
            <?php while ($review = $result_reviews->fetch_assoc()): ?>
                <div class="border rounded p-3 mb-3">
                    <strong><?php echo htmlspecialchars($review['reviewer']); ?></strong>
                    <span class="text-warning"><?php echo str_repeat("★", (int)$review['rating']); ?></span>
                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <small class="text-muted"><?php echo htmlspecialchars($review['review_date']); ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php $conn->close(); ?>
