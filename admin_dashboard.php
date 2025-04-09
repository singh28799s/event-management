<?php
session_start();
include('db_connection.php');

// You can protect this page if needed
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch counts
$totalEvents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'];
$totalOrganizers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'organizer'"))['count'];
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'customer'"))['count'];
$totalBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as sum FROM bookings"))['sum'] ?? 0;
$totalReviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
  body {
    display: flex;
    min-height: 100vh;
    background-color: #0f0f0f;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #f8f9fa;
  }

  .sidebar {
    width: 240px;
    background: linear-gradient(180deg, #111, #222);
    color: white;
    flex-shrink: 0;
    box-shadow: 2px 0 10px rgba(0, 255, 255, 0.2);
  }

  .sidebar h4 {
    text-align: center;
    padding: 20px;
    border-bottom: 1px solid #444;
    text-shadow: 0 0 5px #0ff;
  }

  .sidebar a {
    color: #0ff;
    padding: 14px 20px;
    display: block;
    text-decoration: none;
    transition: 0.3s;
    font-weight: 500;
    border-bottom: 1px solid #1f1f1f;
  }

  .sidebar a:hover {
    background-color: #0ff;
    color: #000;
    text-shadow: none;
  }

  .content {
    flex-grow: 1;
    padding: 30px;
    background-color: #1a1a1a;
  }

  .content h2 {
    text-shadow: 0 0 8px #0ff;
    margin-bottom: 20px;
  }

  .card {
    background-color: #111;
    color: #0ff;
    border: 1px solid #0ff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 255, 255, 0.2);
    transition: transform 0.2s ease-in-out;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 20px #0ff;
  }

  .card h6 {
    font-size: 1rem;
    text-shadow: 0 0 3px #0ff;
  }

  .card h4 {
    font-size: 1.6rem;
    font-weight: bold;
    text-shadow: 0 0 5px #0ff;
  }

  .card-icon i {
    font-size: 2rem;
    color: #0ff;
    text-shadow: 0 0 5px #0ff;
  }
</style>

</head>
<body>

<div class="sidebar">
  <h4 class="p-3">Admin Panel</h4>
  <a href="admin_dashboard.php">Dashboard</a>
  <a href="manage_users.php">Users</a>
  <a href="manage_events.php">Events</a>
  <a href="manage_tickets.php">Tickets</a>
  <a href="manage_bookings.php">Bookings</a>
  <a href="manage_reviews.php">Reviews</a>
  <a href="manage_services.php">Services</a>
  <a href="admin_settings.php">Settings</a>
  <a href="logout.php">Logout</a>
</div>

<div class="content">
  <h2>Welcome Admin</h2>

  <div class="row mt-4">
    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Events</h6>
            <h4><?= $totalEvents ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-calendar-alt"></i></div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Organizers</h6>
            <h4><?= $totalOrganizers ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-user-tie"></i></div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Customers</h6>
            <h4><?= $totalCustomers ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-users"></i></div>
        </div>
      </div>
    </div>

    <div class="col-md-4 mt-3">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Bookings</h6>
            <h4><?= $totalBookings ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-ticket-alt"></i></div>
        </div>
      </div>
    </div>

    <div class="col-md-4 mt-3">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Revenue</h6>
            <h4>₹<?= number_format($totalRevenue, 2) ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-dollar-sign"></i></div>
        </div>
      </div>
    </div>

    <div class="col-md-4 mt-3">
      <div class="card shadow-sm p-3">
        <div class="d-flex justify-content-between">
          <div>
            <h6>Total Reviews</h6>
            <h4><?= $totalReviews ?></h4>
          </div>
          <div class="card-icon"><i class="fa fa-star"></i></div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
