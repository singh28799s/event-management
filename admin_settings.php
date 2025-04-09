<?php
session_start();
include('db_connection.php');

// Allow only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user']['user_id'];
$msg = "";

// Update profile
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $update = $conn->query("UPDATE users SET name='$name', email='$email' WHERE user_id = $admin_id");

    if ($update) {
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $msg = "<div class='alert alert-success'>Profile updated successfully.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Failed to update profile.</div>";
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $result = $conn->query("SELECT password FROM users WHERE user_id = $admin_id");
    $row = $result->fetch_assoc();

    if (password_verify($old_pass, $row['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password='$hashed' WHERE user_id = $admin_id");
            $msg = "<div class='alert alert-success'>Password changed successfully.</div>";
        } else {
            $msg = "<div class='alert alert-warning'>New passwords do not match.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Incorrect old password.</div>";
    }
}

// Fetch current admin info
$admin = $conn->query("SELECT * FROM users WHERE user_id = $admin_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <h2 class="mb-4">⚙️ Admin Settings</h2>
    <?= $msg ?>

    <div class="row">
        <!-- Profile Update -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">👤 Update Profile</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($admin['name']) ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="form-control" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Change -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">🔐 Change Password</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>Old Password</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
