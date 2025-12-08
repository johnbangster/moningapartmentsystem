<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'admin/config/dbcon.php';
require 'function_booking.php';

// Decode JSON request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate required fields
$required = ['unit_id', 'fname', 'lname', 'email', 'contact', 'move_in', 'payment_type', 'amount'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

// Insert reservation into the reservations table
$unit_id = (int)$data['unit_id'];
$fname = $data['fname'];
$lname = $data['lname'];
$email = $data['email'];
$phone = $data['contact'];
$move_in = $data['move_in'];
$payment_type = $data['payment_type']; // reserve_only or full_payment
$payment_method = 'paypal';  // Assuming PayPal payment method for now
$amount = (float)$data['amount'];

// Set expiration date for reservation (3 days)
$expires_at = date('Y-m-d H:i:s', strtotime('+3 days')); // expire in 3 days

// Prepare SQL statement for inserting reservation data
$stmt = $con->prepare("INSERT INTO reservations 
    (fname, lname, email, phone, move_in_date, unit_id, payment_status, payment_type, payment_method, amount_paid, expires_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$payment_status = 'pending'; // The payment status will be pending until it's confirmed

$stmt->bind_param(
    'sssssisdsds',
    $fname,
    $lname,
    $email,
    $phone,
    $move_in,
    $unit_id,
    $payment_status,
    $payment_type,
    $payment_method,
    $amount,
    $expires_at
);

if ($stmt->execute()) {
    $reservation_id = $stmt->insert_id;
    
    // After successful reservation insert, update the unit status to "Booked"
    $update_unit_stmt = $con->prepare("UPDATE units SET status = 'Booked' WHERE id = ?");
    $update_unit_stmt->bind_param('i', $unit_id);
    
    if ($update_unit_stmt->execute()) {
        // Successfully updated the unit status
        echo json_encode(['success' => true, 'reservation_id' => $reservation_id]);

    
    } else {
        // Failed to update unit status
        echo json_encode(['success' => false, 'message' => 'Failed to update unit status']);
    }

    
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create reservation']);
}



exit;
