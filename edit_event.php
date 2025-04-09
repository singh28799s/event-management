<?php
session_start();
include('db_connection.php');

// Ensure organizer is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user']['user_id'];

// Get event ID
if (!isset($_GET['id'])) {
    header("Location: list_events.php");
    exit();
}
$event_id = (int)$_GET['id'];

// Fetch event details
$query = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND organizer_id = ?");
$query->bind_param("ii", $event_id, $organizer_id);
$query->execute();
$result = $query->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "Event not found or access denied.";
    exit();
}

// Update event on form submit
if (isset($_POST['update_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $image = $_POST['image'];

    $update = $conn->prepare("UPDATE events SET title=?, description=?, category=?, venue=?, event_date=?, start_time=?, end_time=?, image=? WHERE event_id=? AND organizer_id=?");
    $update->bind_param("ssssssssii", $title, $description, $category, $venue, $event_date, $start_time, $end_time, $image, $event_id, $organizer_id);
    $update->execute();

    $msg = "Event updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <h2>Edit Event</h2>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Category</label>
            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($event['category']) ?>">
        </div>
        <div class="mb-3">
            <label>Venue</label>
            <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($event['venue']) ?>">
        </div>
        <div class="mb-3">
            <label>Event Date</label>
            <input type="date" name="event_date" class="form-control" value="<?= $event['event_date'] ?>">
        </div>
        <div class="mb-3">
            <label>Start Time</label>
            <input type="time" name="start_time" class="form-control" value="<?= $event['start_time'] ?>">
        </div>
        <div class="mb-3">
            <label>End Time</label>
            <input type="time" name="end_time" class="form-control" value="<?= $event['end_time'] ?>">
        </div>
        <div class="mb-3">
            <label>Image URL</label>
            <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($event['image']) ?>">
        </div>
        <button type="submit" name="update_event" class="btn btn-primary">Update Event</button>
        <a href="list_events.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
