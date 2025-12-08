<?php
require 'admin/config/dbcon.php';

if(!isset($_GET['booking_id'])){
    header("Location: units.php");
    exit;
}

$booking_id = intval($_GET['booking_id']);

// Fetch reservation
$res_q = mysqli_query($con, "SELECT r.*, u.name as unit_name, u.price FROM reservations r
                             JOIN units u ON r.unit_id = u.id
                             WHERE r.id = $booking_id");

if(mysqli_num_rows($res_q) == 0){
    die("Booking not found.");
}

$booking = mysqli_fetch_assoc($res_q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GCash Payment</title>
<link rel="stylesheet" href="assets/css/bootstrap5.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4 shadow-sm">
        <h3>GCash Payment</h3>
        <p>Unit: <strong><?= $booking['unit_name'] ?></strong></p>
        <p>Amount to pay: <strong>₱ <?= number_format($booking['price'],2) ?></strong></p>
        <form method="POST" action="save_gcash_payment.php">
            <input type="hidden" name="reservation_id" value="<?= $booking['id'] ?>">
            <div class="mb-3">
                <label>Upload GCash Payment Proof</label>
                <input type="file" name="payment_proof" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Confirm Payment</button>
        </form>
    </div>
</div>
</body>
</html>
