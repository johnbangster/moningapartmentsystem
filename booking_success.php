<?php
// Set header for JSON response (in case it's an API or used for JSON response)
// header('Content-Type: application/json');

// Include database connection
require 'admin/config/dbcon.php';

// Fetch the reservation ID from the URL parameters (e.g., booking_success.php?id=123)
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID missing']);
    exit;
}

$reservation_id = (int)$_GET['id']; // Make sure it's an integer

// Query the database to get the reservation details
$stmt = $con->prepare("SELECT r.*, u.name AS unit_name, u.price AS unit_price FROM reservations r JOIN units u ON r.unit_id = u.id WHERE r.id = ?");
$stmt->bind_param('i', $reservation_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the reservation exists
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$reservation_data = $result->fetch_assoc(); // Get the reservation details
$unit_name = $reservation_data['unit_name'];
$unit_price = number_format($reservation_data['unit_price'], 2);
$payment_status = $reservation_data['payment_status'];
$amount_paid = number_format($reservation_data['amount_paid'], 2);
$move_in_date = $reservation_data['move_in_date'];
$payment_date = $reservation_data['payment_date'];
$fname = $reservation_data['fname'];
$lname = $reservation_data['lname'];
$email = $reservation_data['email'];

// Show a success message with the reservation details
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <title>Booking Success</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm rounded">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Booking Successful!</h3>
                        <p class="lead text-center">Thank you, <?= htmlspecialchars($fname . ' ' . $lname) ?>. Your booking has been confirmed.</p>

                        <div class="mb-3">
                            <h5>Reservation Details</h5>
                            <p><strong>Unit Name:</strong> <?= htmlspecialchars($unit_name) ?></p>
                            <p><strong>Price per Month:</strong> ₱ <?= $unit_price ?></p>
                            <p><strong>Move-In Date:</strong> <?= date('F d, Y', strtotime($move_in_date)) ?></p>
                            <p><strong>Payment Status:</strong> <?= ucfirst($payment_status) ?></p>
                            <p><strong>Amount Paid:</strong> ₱ <?= $amount_paid ?></p>
                            <p><strong>Payment Date:</strong> <?= date('F d, Y', strtotime($payment_date)) ?></p>
                        </div>

                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                            <a href="units.php" class="btn btn-secondary">View Other Units</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

