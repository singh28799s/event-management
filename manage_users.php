<?php
session_start();
include('db_connection.php');

// Only admin allowed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$msg = "";

// Create organizer
if (isset($_POST['create_organizer'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'organizer')");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>✅ Organizer added successfully!</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Error adding organizer: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Delete user (prevent admin delete)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $check = $conn->query("SELECT role FROM users WHERE user_id=$id");
    $user = $check->fetch_assoc();

    if ($user && $user['role'] !== 'admin') {
        $conn->query("DELETE FROM users WHERE user_id=$id");
        header("Location: manage_users.php?msg=deleted");
        exit();
    } else {
        $msg = "<div class='alert alert-warning'>⚠️ You cannot delete an admin user.</div>";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $msg = "<div class='alert alert-success'>🗑️ User deleted successfully.</div>";
}

// Handle filtering
$filter_role = $_GET['role'] ?? '';
$filter_sql = $filter_role ? "WHERE role = '$filter_role'" : "";
$users = $conn->query("SELECT * FROM users $filter_sql ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Background */
body {
  background: linear-gradient(120deg, #141e30, #243b55);
  color: #f0f0f0;
  font-family: 'Segoe UI', sans-serif;
}

/* Container */
.container {
  background-color: rgba(255, 255, 255, 0.03);
  padding: 30px;
  border-radius: 12px;
  margin-top: 40px;
  box-shadow: 0 0 25px rgba(0, 255, 255, 0.15);
}

/* Heading */
h2 {
  text-align: center;
  color: #00ffff;
  text-shadow: 0 0 8px #00ffff;
  margin-bottom: 30px;
}

/* Card */
.card {
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid #00ffff33;
  box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
}

/* Card header */
.card-header {
  font-weight: bold;
  background: linear-gradient(135deg, #00c6ff, #0072ff);
  box-shadow: 0 0 8px rgba(0, 255, 255, 0.4);
}

/* Inputs */
input.form-control,
select.form-select {
  background-color: rgba(255, 255, 255, 0.07);
  border: 1px solid #00ffff66;
  color: #fff;
}

input.form-control:focus,
select.form-select:focus {
  box-shadow: 0 0 8px #00ffff;
  border-color: #00ffff;
  background-color: rgba(255, 255, 255, 0.1);
  color: #fff;
}

/* Buttons */
.btn-success,
.btn-secondary,
.btn-danger {
  border: none;
  font-weight: bold;
  box-shadow: 0 0 10px #00ffff44;
}

.btn-success {
  background: linear-gradient(135deg, #00ffcc, #009999);
}

.btn-secondary {
  background: linear-gradient(135deg, #6666ff, #3333cc);
}

.btn-danger {
  background: linear-gradient(135deg, #ff4e50, #f9d423);
  color: #000;
}

.btn:hover {
  opacity: 0.9;
  transform: scale(1.02);
}

/* Table */
.table {
  background-color: rgba(255, 255, 255, 0.05);
  color: #fff;
}

.table th,
.table td {
  vertical-align: middle;
}

.table-dark {
  background-color: #003344;
  color: #00ffff;
  border-bottom: 2px solid #00ffff99;
}

.text-muted {
  color: #cccccc !important;
}

/* Alerts */
.alert {
  font-size: 0.95rem;
  border-radius: 8px;
}
</style>
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">👤 Manage Users</h2>

    <?= $msg ?>

    <!-- Create Organizer Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">➕ Create Organizer</div>
        <div class="card-body">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="name" class="form-control" placeholder="Name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-4">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                </div>
                <button class="btn btn-success mt-3" name="create_organizer">Add Organizer</button>
            </form>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="get" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="role" class="form-label">Filter by Role:</label>
            </div>
            <div class="col-auto">
                <select name="role" id="role" class="form-select">
                    <option value="">Show All</option>
                    <option value="admin" <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="organizer" <?= $filter_role === 'organizer' ? 'selected' : '' ?>>Organizer</option>
                    <option value="customer" <?= $filter_role === 'customer' ? 'selected' : '' ?>>Customer</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-secondary">Apply Filter</button>
            </div>
        </div>
    </form>

    <!-- User Table -->
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
        <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($users->num_rows > 0): ?>
            <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['user_id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td>
                        <?php if ($row['role'] !== 'admin'): ?>
                            <a href="?delete=<?= $row['user_id'] ?>&role=<?= $filter_role ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
