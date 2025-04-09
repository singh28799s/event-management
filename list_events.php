<?php
session_start();
include('db_connection.php');

// Only allow organizer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user']['user_id'];

// Delete event
if (isset($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM events WHERE event_id = $event_id AND organizer_id = $organizer_id");
    header("Location: list_events.php");
    exit();
}

// Get organizer's events
$result = $conn->query("SELECT * FROM events WHERE organizer_id = $organizer_id ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">📋 My Events</h2>
    <a href="create_event.php" class="btn btn-success mb-3">➕ Create New Event</a>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Venue</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= $event['event_date'] ?></td>
                        <td><?= $event['start_time'] ?> - <?= $event['end_time'] ?></td>
                        <td><?= htmlspecialchars($event['venue']) ?></td>
                        <td><?= htmlspecialchars($event['category']) ?></td>
                        <td>
                            <?php if ($event['image']): ?>
                                <img src="<?= $event['image'] ?>" width="100" height="60" style="object-fit: cover;">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_event.php?id=<?= $event['event_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="list_events.php?delete=<?= $event['event_id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You haven't created any events yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
