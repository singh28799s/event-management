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

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Function to execute table creation
function createTable($conn, $sql, $tableName) {
    if ($conn->query($sql) === TRUE) {
        echo "$tableName table created successfully.<br>";
    } else {
        echo "Error creating $tableName table: " . $conn->error . "<br>";
    }
}

// Create users table
$sql_users = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'organizer', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
SQL;
createTable($conn, $sql_users, "Users");

// Create events table
$sql_events = <<<SQL
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    venue VARCHAR(255),
    event_date DATE,
    start_time TIME,
    end_time TIME,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(user_id) ON DELETE CASCADE
)
SQL;
createTable($conn, $sql_events, "Events");

// Create tickets table
$sql_tickets = <<<SQL
CREATE TABLE IF NOT EXISTS tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    ticket_type VARCHAR(100),
    price DECIMAL(10, 2),
    quantity INT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
)
SQL;
createTable($conn, $sql_tickets, "Tickets");

// Create bookings table
$sql_bookings = <<<SQL
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    ticket_id INT,
    quantity INT,
    total_amount DECIMAL(10,2),
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id) ON DELETE CASCADE
)
SQL;
createTable($conn, $sql_bookings, "Bookings");

// Create reviews table
$sql_reviews = <<<SQL
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)
SQL;
createTable($conn, $sql_reviews, "Reviews");

// Create services table
$sql_services = <<<SQL
CREATE TABLE IF NOT EXISTS services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    icon_url VARCHAR(255)
)
SQL;
createTable($conn, $sql_services, "Services");

echo "<br><strong>✅ All tables created successfully and optimized.</strong>";

// Close connection
$conn->close();
?>
