<?php
session_start();
include('db_connection.php');

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// Delete review
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM reviews WHERE review_id = $id");
    $msg = "<div class='alert alert-success'>Review deleted successfully.</div>";
}

// Fetch all reviews with user and event info
$sql = "
    SELECT 
        r.review_id, r.rating, r.comment, r.review_date,
        u.name AS user_name,
        e.title AS event_title
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN events e ON r.event_id = e.event_id
    ORDER BY r.review_id DESC
";

$reviews = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Reviews</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">🌟 Manage Event Reviews</h2>

  <?= $msg ?>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Review ID</th>
        <th>User</th>
        <th>Event</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Review Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($reviews->num_rows > 0): ?>
      <?php while ($row = $reviews->fetch_assoc()): ?>
        <tr>
          <td><?= $row['review_id'] ?></td>
          <td><?= htmlspecialchars($row['user_name']) ?></td>
          <td><?= htmlspecialchars($row['event_title']) ?></td>
          <td><?= str_repeat("⭐", $row['rating']) . " ({$row['rating']})" ?></td>
          <td><?= htmlspecialchars($row['comment']) ?></td>
          <td><?= date('d M Y, h:i A', strtotime($row['review_date'])) ?></td>
          <td>
            <a href="?delete=<?= $row['review_id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="7" class="text-center text-muted">No reviews found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
