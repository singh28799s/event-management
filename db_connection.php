<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "event";

// Connect to MySQL
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select the database
if (!$conn->select_db($database)) {
    die("Database selection failed: " . $conn->error);
}

// ✅ Now you're connected and can query the 'event' database.
?>
