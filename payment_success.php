<?php
require 'admin/config/dbcon.php';
require 'admin/config/paymongo.php';

$session_id = $_GET['session_id'] ?? '';
$booking_id = $_GET['booking_id'] ?? '';

if (!$session_id || !$booking_id) {
    die("Missing required data.");
}

// Verify payment via PayMongo
$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions/$session_id");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic " . base64_encode($secret_key . ":"),
        "Content-Type: application/json"
    ]
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$status = $result['data']['attributes']['status'] ?? '';

if ($status === 'paid') {

    // Get booking info
    $stmt = $conn->prepare("SELECT unit_id, move_out FROM bookings WHERE id=?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->bind_result($unit_id, $move_out);
    $stmt->fetch();
    $stmt->close();

    if (!$unit_id) die("Booking not found.");

    // Update booking
    $stmt = $conn->prepare("UPDATE bookings SET payment_status='paid', booking_status='confirmed', updated_at=NOW() WHERE id=?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // Update unit
    $stmt = $conn->prepare("UPDATE units SET status='Occupied', booked_until=? WHERE id=?");
    $stmt->bind_param("si", $move_out, $unit_id);
    $stmt->execute();
    $stmt->close();

    echo "Payment successful! Booking confirmed and unit updated.";

} else {
    echo "Payment not completed yet.";
}

$conn->close();
?>
