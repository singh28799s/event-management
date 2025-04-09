<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$organizer_id = $_SESSION['user']['user_id'];
$event_id = null;
$success = $error = "";

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Image upload
    $image_path = '';
    if ($_FILES['image']['error'] == 0) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_path = $target_dir . $filename;
            move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
        } else {
            $error = "❌ Invalid image format!";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO events (organizer_id, title, description, category, venue, event_date, start_time, end_time, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $organizer_id, $title, $description, $category, $venue, $event_date, $start_time, $end_time, $image_path);
        if ($stmt->execute()) {
            $event_id = $stmt->insert_id;
        } else {
            $error = "❌ Event creation failed.";
        }
    }
}

// Handle ticket creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_tickets'])) {
    $event_id = $_POST['event_id'];
    $types = $_POST['ticket_type'];
    $prices = $_POST['price'];
    $quantities = $_POST['quantity'];

    $stmt = $conn->prepare("INSERT INTO tickets (event_id, ticket_type, price, quantity) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($types); $i++) {
        $type = trim($types[$i]);
        $price = (int)$prices[$i];
        $qty = (int)$quantities[$i];

        if ($type && $price > 0 && $qty > 0) {
            $stmt->bind_param("isii", $event_id, $type, $price, $qty);
            $stmt->execute();
        }
    }

    header("Location: create_event.php?success=1");
    exit();
}

if (isset($_GET['success'])) {
    $success = "✅ Event and tickets created successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ticket-block { border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">🎤 Create Event</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!$event_id): ?>
    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow">
        <input type="hidden" name="create_event" value="1">
        <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div>
        <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" required></textarea></div>
        <div class="mb-3"><label>Category</label><input type="text" name="category" class="form-control"></div>
        <div class="mb-3"><label>Venue</label><input type="text" name="venue" class="form-control" required></div>
        <div class="row mb-3">
            <div class="col"><label>Date</label><input type="date" name="event_date" class="form-control" required></div>
            <div class="col"><label>Start</label><input type="time" name="start_time" class="form-control" required></div>
            <div class="col"><label>End</label><input type="time" name="end_time" class="form-control" required></div>
        </div>
        <div class="mb-3"><label>Image</label><input type="file" name="image" class="form-control"></div>
        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
    <?php endif; ?>

    <?php if ($event_id): ?>
    <h3 class="mt-5">🎫 Add Tickets for Event #<?= $event_id ?></h3>
    <form method="POST" class="card p-4 shadow">
        <input type="hidden" name="create_tickets" value="1">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <div id="ticketContainer">
            <div class="ticket-block">
                <div class="row">
                    <div class="col-md-4">
                        <label>Type</label>
                        <input type="text" name="ticket_type[]" class="form-control" placeholder="e.g. VIP, General, Student" required>
                    </div>
                    <div class="col-md-4">
                        <label>Price (₹)</label>
                        <input type="number" name="price[]" class="form-control" placeholder="Enter price" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label>Quantity</label>
                        <input type="number" name="quantity[]" class="form-control" placeholder="Enter quantity" min="1" required>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-secondary mb-3" onclick="addTicket()">+ Add More Ticket</button>
        <br>
        <button type="submit" class="btn btn-success">Save Tickets</button>
    </form>
    <?php endif; ?>
</div>

<script>
function addTicket() {
    const html = `
        <div class="ticket-block">
            <div class="row">
                <div class="col-md-4">
                    <label>Type</label>
                    <input type="text" name="ticket_type[]" class="form-control" placeholder="e.g. VIP, General, Student" required>
                </div>
                <div class="col-md-4">
                    <label>Price (₹)</label>
                    <input type="number" name="price[]" class="form-control" placeholder="Enter price" min="0" required>
                </div>
                <div class="col-md-4">
                    <label>Quantity</label>
                    <input type="number" name="quantity[]" class="form-control" placeholder="Enter quantity" min="1" required>
                </div>
            </div>
        </div>`;
    document.getElementById('ticketContainer').insertAdjacentHTML('beforeend', html);
}
</script>
</body>
</html>
