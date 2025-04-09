<?php
session_start();
include('db_connection.php');

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// Add new service
if (isset($_POST['add_service'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon_url']);

    $insert = $conn->query("INSERT INTO services (title, description, icon_url) VALUES ('$title', '$desc', '$icon')");
    $msg = $insert ? "<div class='alert alert-success'>Service added successfully.</div>" : "<div class='alert alert-danger'>Failed to add service.</div>";
}

// Delete service
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM services WHERE service_id = $id");
    $msg = "<div class='alert alert-success'>Service deleted.</div>";
}

// Fetch all services
$services = $conn->query("SELECT * FROM services ORDER BY service_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">🛠️ Manage Services</h2>

  <?= $msg ?>

  <!-- Add Service Form -->
  <div class="card mb-4">
    <div class="card-header">➕ Add New Service</div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Icon URL</label>
          <input type="text" name="icon_url" class="form-control" placeholder="https://example.com/icon.png">
        </div>
        <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
      </form>
    </div>
  </div>

  <!-- Services List -->
  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Icon</th>
        <th>Title</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($services->num_rows > 0): ?>
      <?php while ($row = $services->fetch_assoc()): ?>
        <tr>
          <td><?= $row['service_id'] ?></td>
          <td>
            <?php if ($row['icon_url']): ?>
              <img src="<?= $row['icon_url'] ?>" alt="icon" style="width:40px; height:40px;">
            <?php else: ?>
              <span class="text-muted">No Icon</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['description']) ?></td>
          <td>
            <a href="?delete=<?= $row['service_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this service?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="5" class="text-center text-muted">No services added yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
