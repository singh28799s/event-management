<?php
session_start();
$conn = new mysqli("localhost", "root", "", "event");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Services
$services = [];
$res = $conn->query("SELECT * FROM services");
while ($row = $res->fetch_assoc()) $services[] = $row;

// Events
$events = [];
$res = $conn->query("SELECT events.*, users.name AS organizer_name 
  FROM events 
  JOIN users ON events.organizer_id = users.user_id 
  WHERE event_date >= CURDATE()
  ORDER BY event_date ASC 
  LIMIT 5");

while ($row = $res->fetch_assoc()) $events[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EventMaster</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .hero { background: #007bff; color: white; padding: 60px 20px; text-align: center; }
    .service-icon { height: 60px; margin-bottom: 10px; }
    .event-card img { height: 180px; object-fit: cover; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <a class="navbar-brand" href="#">EventMaster</a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  
  <!-- ✅ Navigation Links -->
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'customer'): ?>
        <li class="nav-item">
          <a class="nav-link" href="my_bookings.php">My Bookings</a>
        </li>
      <?php elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'organizer'): ?>
        <li class="nav-item">
          <a class="nav-link" href="organizer_events.php">My Events</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>




  <!-- Right-side buttons -->
  <div class="ms-auto">
    <?php if (isset($_SESSION['user'])): ?>
      <span class="text-white me-2">Hi, <?= $_SESSION['user']['name'] ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    <?php else: ?>
      <button class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
      <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
    <?php endif; ?>
  </div>
</nav>



<!-- Hero -->
<div class="hero">
  <h1>Discover & Book Amazing Events</h1>
  <p>Concerts, exhibitions, workshops – all in one place.</p>
  <a href="events.php" class="btn btn-light mt-3">Explore Events</a>
</div>
<style>
  .hero {
    background: linear-gradient(135deg, #0a0a0a 60%, #111 100%);
    color: #0ff;
    padding: 100px 20px;
    text-align: center;
    border-radius: 10px;
    margin-bottom: 50px;
    box-shadow: 0 0 25px #0ff inset;
    font-family: 'Orbitron', sans-serif;
  }

  .hero h1 {
    font-size: 3rem;
    text-shadow: 0 0 15px #0ff;
    margin-bottom: 20px;
  }

  .hero p {
    font-size: 1.2rem;
    color: #ccc;
    text-shadow: 0 0 5px #0ff;
    margin-bottom: 30px;
  }

  .hero .btn {
    font-size: 1rem;
    padding: 10px 25px;
    border: 2px solid #0ff;
    color: #0ff;
    background-color: transparent;
    text-shadow: 0 0 5px #0ff;
    transition: all 0.3s ease-in-out;
  }

  .hero .btn:hover {
    background-color: #0ff;
    color: #000;
    box-shadow: 0 0 15px #0ff;
  }
</style>




<!-- Services -->
<div class="container my-5 service-section">
  <h2 class="text-center mb-4">Our Services</h2>
  <div class="row">
    <?php foreach ($services as $s): ?>
    <div class="col-md-4 text-center mb-4">
      <img src="<?= htmlspecialchars($s['icon_url']) ?>" class="service-icon" alt="">
      <h5><?= htmlspecialchars($s['title']) ?></h5>
      <p><?= htmlspecialchars($s['description']) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<style>
  .service-section {
    background-color: #0a0a0a;
    padding: 50px 20px;
    border-radius: 15px;
    box-shadow: 0 0 15px #0ff inset;
  }

  .service-section h2 {
    color: #0ff;
    text-shadow: 0 0 10px #0ff;
    font-family: 'Orbitron', sans-serif;
  }

  .service-icon {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
    filter: drop-shadow(0 0 8px #0ff);
  }

  .service-section h5 {
    color: #0ff;
    font-weight: bold;
    text-shadow: 0 0 5px #0ff;
    font-family: 'Orbitron', sans-serif;
  }

  .service-section p {
    color: #ccc;
  }
</style>





<!-- Events -->
<!-- Place this in your HTML file -->
<head>
  <style>
    body {
      background-color: #0a0a0a;
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .text-neon {
      color: #0ff;
      text-shadow: 0 0 5px #0ff, 0 0 10px #0ff, 0 0 20px #00f2ff;
      font-family: 'Orbitron', sans-serif;
    }

    .text-glow {
      color: #fff;
      text-shadow: 0 0 3px #fff, 0 0 6px #0ff, 0 0 9px #0ff;
    }

    .neon-card {
      background-color: #111;
      border-radius: 15px;
      box-shadow: 0 0 10px #0ff, 0 0 20px #0ff1, 0 0 30px #0ff1;
      border: 2px solid #0ff;
      transition: transform 0.3s ease;
    }

    .neon-card:hover {
      transform: scale(1.02);
      box-shadow: 0 0 15px #0ff, 0 0 30px #0ff;
    }

    .btn-neon {
      background-color: #0ff;
      color: #000;
      font-weight: bold;
      border: none;
      transition: 0.3s ease-in-out;
    }

    .btn-neon:hover {
      box-shadow: 0 0 10px #0ff, 0 0 20px #0ff, 0 0 30px #0ff;
      transform: scale(1.03);
      color: #000;
    }

    .btn-outline-info {
      border-color: #0ff;
      color: #0ff;
    }

    .btn-outline-info:hover {
      background-color: #0ff;
      color: #000;
    }

    .card-title, .card-text, p {
      color: #ccc;
    }

    a, button {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>
</head>

<body>
  <div class="container my-5">
    <h2 class="text-center mb-4 text-neon">Upcoming Events</h2>
    <div class="row">
      <?php foreach ($events as $e): ?>
      <div class="col-md-4 mb-4">
        <div class="card event-card neon-card h-100">
          <img src="<?= htmlspecialchars($e['image']) ?>" class="card-img-top" alt="Event Image" style="height: 200px; object-fit: cover; border-bottom: 2px solid #0ff;">
          
          <div class="card-body d-flex flex-column">
            <h5 class="card-title text-glow"><?= htmlspecialchars($e['title']) ?></h5>
            <p class="card-text"><?= substr(htmlspecialchars($e['description']), 0, 80) ?>...</p>
            <p><strong>Date:</strong> <?= htmlspecialchars($e['event_date']) ?><br>
               <strong>By:</strong> <?= htmlspecialchars($e['organizer_name']) ?></p>

            <div class="mt-auto">
              <a href="event_detail.php?id=<?= htmlspecialchars($e['event_id']) ?>" class="btn btn-outline-info btn-sm w-100 mb-2">View Details</a>

              <?php if (isset($_SESSION['user'])): ?>
                <?php if ($_SESSION['user']['role'] === 'customer'): ?>
                  <a href="book_ticket.php?event_id=<?= htmlspecialchars($e['event_id']) ?>" class="btn btn-neon btn-sm w-100">🎟️ Book Now</a>
                <?php elseif ($_SESSION['user']['role'] === 'organizer'): ?>
                  <button class="btn btn-secondary btn-sm w-100" disabled>Organizer Cannot Book</button>
                <?php elseif ($_SESSION['user']['role'] === 'admin'): ?>
                  <button class="btn btn-info btn-sm w-100" disabled>Admin Access</button>
                <?php endif; ?>
              <?php else: ?>
                <button class="btn btn-neon btn-sm w-100" data-bs-toggle="modal" data-bs-target="#loginModal">Login to Book</button>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>






<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content neon-modal" action="login_handler.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">🔐 Login</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <?php if (isset($_GET['login_error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['login_error']) ?></div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="loginEmail" class="form-label">Email</label>
          <input type="email" name="email" id="loginEmail" class="form-control" required autocomplete="off">
        </div>
        <div class="mb-3">
          <label for="loginPassword" class="form-label">Password</label>
          <input type="password" name="password" id="loginPassword" class="form-control" required autocomplete="new-password">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-neon">Login</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<style>
  .neon-modal {
    background-color: #111;
    border: 2px solid #0ff;
    border-radius: 15px;
    box-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    color: #fff;
  }

  .modal-header, .modal-footer {
    border: none;
  }

  .modal-title {
    color: #0ff;
    text-shadow: 0 0 5px #0ff;
    font-family: 'Orbitron', sans-serif;
  }

  .form-control {
    background-color: #000;
    color: #0ff;
    border: 1px solid #0ff;
    transition: box-shadow 0.3s ease;
  }

  .form-control:focus {
    box-shadow: 0 0 10px #0ff;
    border-color: #0ff;
    background-color: #000;
  }

  .btn-neon {
    background-color: #0ff;
    color: #000;
    font-weight: bold;
    transition: all 0.3s ease-in-out;
  }

  .btn-neon:hover {
    box-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    transform: scale(1.05);
  }

  .alert-danger {
    background-color: #330000;
    border-color: #ff4d4d;
    color: #ff9999;
    text-shadow: 0 0 5px #ff4d4d;
  }

  .form-label {
    color: #ccc;
  }
</style>



<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content neon-modal" action="register_handler.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="registerModalLabel">🛸 Register</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <?php if (isset($_GET['register_error'])): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($_GET['register_error']) ?></div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="regName" class="form-label">Name</label>
          <input type="text" name="name" id="regName" class="form-control" required autocomplete="off">
        </div>
        <div class="mb-3">
          <label for="regEmail" class="form-label">Email</label>
          <input type="email" name="email" id="regEmail" class="form-control" required autocomplete="off">
        </div>
        <div class="mb-3">
          <label for="regPassword" class="form-label">Password</label>
          <input type="password" name="password" id="regPassword" class="form-control" required autocomplete="new-password">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-neon">Register</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<style>
  .neon-modal {
    background-color: #111;
    border: 2px solid #0ff;
    border-radius: 15px;
    box-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    color: #fff;
  }

  .modal-header,
  .modal-footer {
    border: none;
  }

  .modal-title {
    color: #0ff;
    text-shadow: 0 0 5px #0ff;
    font-family: 'Orbitron', sans-serif;
  }

  .form-control {
    background-color: #000;
    color: #0ff;
    border: 1px solid #0ff;
    transition: box-shadow 0.3s ease;
  }

  .form-control:focus {
    box-shadow: 0 0 10px #0ff;
    border-color: #0ff;
    background-color: #000;
  }

  .btn-neon {
    background-color: #0ff;
    color: #000;
    font-weight: bold;
    transition: all 0.3s ease-in-out;
  }

  .btn-neon:hover {
    box-shadow: 0 0 10px #0ff, 0 0 20px #0ff;
    transform: scale(1.05);
  }

  .alert-danger {
    background-color: #330000;
    border-color: #ff4d4d;
    color: #ff9999;
    text-shadow: 0 0 5px #ff4d4d;
  }

  .form-label {
    color: #ccc;
  }
</style>






<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-4">&copy; <?= date('Y') ?> EventMaster</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_GET['login_error'])): ?>
<script>new bootstrap.Modal(document.getElementById('loginModal')).show();</script>
<?php elseif (isset($_GET['register_error'])): ?>
<script>new bootstrap.Modal(document.getElementById('registerModal')).show();</script>
<?php endif; ?>
</body>
</html>
