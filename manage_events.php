<?php
session_start();
include('db_connection.php');

// Admin access only
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Delete Event
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM events WHERE id=$id");
    header("Location: manage_events.php");
    exit();
}

// Filter options
$filter_query = "SELECT events.*, users.name AS organizer_name FROM events JOIN users ON events.organizer_id = users.id";

$where_clauses = [];

if (isset($_GET['category']) && $_GET['category'] != '') {
    $cat = $_GET['category'];
    $where_clauses[] = "category = '$cat'";
}

if (isset($_GET['organizer']) && $_GET['organizer'] != '') {
    $org = $_GET['organizer'];
    $where_clauses[] = "organizer_id = '$org'";
}

if (isset($_GET['date']) && $_GET['date'] != '') {
    $date = $_GET['date'];
    $where_clauses[] = "DATE(date) = '$date'";
}

if (!empty($where_clauses)) {
    $filter_query .= " WHERE " . implode(" AND ", $where_clauses);
}

$filter_query .= " ORDER BY events.id DESC";

$events = mysqli_query($conn, $filter_query);

// For dropdowns
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM events");
$organizers = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'organizer'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Events</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">Manage Events</h2>

  <!-- Filter Form -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-3">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <?php while ($row = mysqli_fetch_assoc($categories)): ?>
          <option value="<?= $row['category'] ?>" <?= isset($_GET['category']) && $_GET['category'] === $row['category'] ? 'selected' : '' ?>>
            <?= $row['category'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="organizer" class="form-select">
        <option value="">All Organizers</option>
        <?php while ($row = mysqli_fetch_assoc($organizers)): ?>
          <option value="<?= $row['id'] ?>" <?= isset($_GET['organizer']) && $_GET['organizer'] == $row['id'] ? 'selected' : '' ?>>
            <?= $row['name'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="date" name="date" class="form-control" value="<?= $_GET['date'] ?? '' ?>">
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary" type="submit">Filter</button>
      <a href="manage_events.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Events Table -->
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Organizer</th>
        <th>Category</th>
        <th>Date</th>
        <th>Location</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($events)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['title'] ?></td>
          <td><?= $row['organizer_name'] ?></td>
          <td><?= $row['category'] ?></td>
          <td><?= $row['date'] ?></td>
          <td><?= $row['location'] ?></td>
          <td>
            <a href="edit_event.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="manage_events.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
