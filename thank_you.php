<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Successful</title>
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
</head>
<body class="bg-light">

<div class="container text-center mt-5">
    <h1 class="text-success">Thank you!</h1>
    <p>Your reservation has been received and payment was successful.</p>
    <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
</div>

</body>
</html> -->

<?php
// booking_success.php

session_start();

// Get payment info from session or request
$unit_name = $_SESSION['unit']['name'] ?? 'your unit';
$payment_type = $_SESSION['unit']['payment_type'] ?? 'full_payment';
$payment_method = $_SESSION['unit']['payment_method'] ?? 'cash';
$amount = $_SESSION['unit']['price'] ?? 0;
$status = '';

// Determine status message based on payment method
if ($payment_method === 'cash') {
    $status = 'Your reservation is recorded. Please pay in person within 3 days to confirm your booking.';
} elseif ($payment_method === 'paypal' || $payment_method === 'gcash') {
    $status = 'Your payment was successful and your booking is confirmed.';
} else {
    $status = 'Your booking was successful.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Successful</title>
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
</head>
<body class="bg-light">

<div class="container text-center mt-5">
    <h1 class="text-success">Thank you!</h1>
    <p>Your reservation for <strong><?= htmlspecialchars($unit_name) ?></strong> has been received.</p>
    <p><?= htmlspecialchars($status) ?></p>
    <p>Amount: <strong>₱ <?= number_format($amount,2) ?></strong></p>
    <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
</div>

</body>
</html>

