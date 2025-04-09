<?php
session_start();
include('db_connection.php');

$msg = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;

            // Redirect based on role
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'organizer') {
                header("Location: organizer_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $msg = "<div class='alert alert-danger'>❌ Incorrect password.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>❌ No user found with this email.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>/* Background Gradient */
body {
  background: linear-gradient(135deg, #1f1c2c, #928dab);
  font-family: 'Segoe UI', sans-serif;
  min-height: 100vh;
  color: #fff;
}

/* Centered container with glow */
.container {
  max-width: 500px;
  background: rgba(0, 0, 0, 0.6);
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 0 20px rgba(0, 255, 255, 0.4);
  backdrop-filter: blur(8px);
  margin-top: 80px;
}

/* Heading style */
h3 {
  color: #00ffff;
  text-align: center;
  text-shadow: 0 0 5px #00ffff;
}

/* Labels */
label {
  color: #fff;
  font-weight: 500;
}

/* Form inputs */
.form-control {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid #00ffff;
  color: #fff;
  border-radius: 8px;
  box-shadow: none;
  transition: 0.3s ease;
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.2);
  border-color: #00ffff;
  box-shadow: 0 0 10px #00ffff;
  color: #fff;
}

/* Login Button */
.btn-primary {
  background-color: #00ffff;
  border: none;
  color: #000;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 0 10px #00ffff;
}

.btn-primary:hover {
  background-color: #0077ff;
  color: #fff;
  box-shadow: 0 0 20px #00ffff;
}

/* Alert Messages */
.alert {
  border-radius: 8px;
  padding: 12px;
  font-size: 0.95rem;
  text-align: center;
}
</style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h3 class="mb-4 text-center">🔐 Login</h3>
            <?= $msg ?>
            <form method="post">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button name="login" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
