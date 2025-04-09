<?php
session_start();
$conn = new mysqli("localhost", "root", "", "event");

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
    } else {
        header("Location: index.php?login_error=Invalid password");
    }
} else {
    header("Location: index.php?login_error=User not found");
}
?>
