<?php
session_start();
require 'db.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header('Location: index.php');
    exit;
}

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die('Invalid event ID.');
}

$event_id = (int) $_GET['event_id'];
$user_id = $_SESSION['user']['user_id'];
$message = '';

// Get event & ticket
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM tickets WHERE event_id = ?");
$stmt->execute([$event_id]);
$ticket = $stmt->fetch();

if (!$event || !$ticket) die('Event or tickets not found.');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Ticket - <?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-light">
<div class="container my-5">
    <h2>Book Tickets for <span class="text-primary"><?= htmlspecialchars($event['title']) ?></span></h2>

    <div class="card shadow">
        <div class="card-body">
            <p><strong>Date:</strong> <?= htmlspecialchars($event['event_date']) ?></p>
            <p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
            <p><strong>Available Tickets:</strong> <?= htmlspecialchars($ticket['quantity']) ?></p>
            <p><strong>Price per Ticket:</strong> ₹<?= htmlspecialchars($ticket['price']) ?></p>

            <?php if ($ticket['quantity'] > 0): ?>
            <form method="POST" id="paymentForm">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Number of Tickets</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="<?= $ticket['quantity'] ?>" required>
                </div>
                <button type="button" class="btn btn-success" onclick="payNow()">Pay & Book</button>
            </form>
            <?php else: ?>
                <div class="alert alert-danger">Sorry, tickets are sold out.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function payNow() {
    const quantity = document.getElementById("quantity").value;
    const price = <?= $ticket['price'] ?>;
    const totalAmount = quantity * price * 100; // ₹ to paise

    var options = {
        "key": "rzp_test_CY1dJChs2kocMe", // 🔁 Replace with your Razorpay Test Key
        "amount": totalAmount,
        "currency": "INR",
        "name": "<?= addslashes($event['title']) ?>",
        "description": "Ticket Booking",
        "handler": function (response) {
            // ✅ After payment success, submit form
            let form = document.getElementById('paymentForm');
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'razorpay_payment_id';
            hidden.value = response.razorpay_payment_id;
            form.appendChild(hidden);
            form.submit();
        },
        "prefill": {
            "name": "<?= addslashes($_SESSION['user']['name']) ?>",
            "email": "<?= addslashes($_SESSION['user']['email']) ?>"
        },
        "theme": {
            "color": "#0d6efd"
        }
    };
    var rzp = new Razorpay(options);
    rzp.open();
}
</script>
</body>
</html>

<?php
// Handle POST after payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $quantity = (int) $_POST['quantity'];
    if ($quantity <= 0 || $quantity > $ticket['quantity']) {
        echo "<div class='alert alert-danger'>Invalid quantity.</div>";
    } else {
        $total_price = $ticket['price'] * $quantity;
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, ticket_id, quantity, total_amount, booking_date) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $event_id, $ticket['ticket_id'], $quantity, $total_price]);

        $stmt = $pdo->prepare("UPDATE tickets SET quantity = quantity - ? WHERE ticket_id = ?");
        $stmt->execute([$quantity, $ticket['ticket_id']]);

        echo "<div class='alert alert-success'>🎉 Booking successful! Payment ID: {$_POST['razorpay_payment_id']}</div>";
    }
}
?>
