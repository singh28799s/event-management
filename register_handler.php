<?php
session_start();
$conn = new mysqli("localhost", "root", "", "event");

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    header("Location: index.php?register_error=Email already registered");
    exit;
}

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
$stmt->bind_param("sss", $name, $email, $password);
if ($stmt->execute()) {
    $_SESSION['user'] = ['name' => $name, 'email' => $email, 'role' => 'customer'];
    header("Location: index.php");
} else {
    header("Location: index.php?register_error=Registration failed");
}
?>
