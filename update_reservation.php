<?php
// Enable error reporting for debugging purposes (only enable this during development)
ini_set('display_errors', 1); // Change to 0 for production
error_reporting(E_ALL);

// Set header for JSON response
header('Content-Type: application/json');

// Helper function to return JSON and exit
function respond($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

// Include database connection
require 'admin/config/dbcon.php';

// Check if $con exists and is a valid mysqli object
if (!isset($con) || !$con instanceof mysqli) {
    respond(false, 'Database connection not found');
}

// Get JSON input (Post data)
$data = json_decode(file_get_contents('php://input'), true);

// Validate the received data
if (!$data || !isset($data['reservation_id']) || !isset($data['transaction_id']) || !isset($data['amount_paid'])) {
    respond(false, 'Invalid request: Missing required parameters');
}

// Extract variables from the received data
$reservation_id = (int)$data['reservation_id']; // Ensure it's an integer
$transaction_id = $data['transaction_id']; // This will be the PayPal transaction ID (string)
$amount_paid = (float)$data['amount_paid']; // This will be the amount that was paid
$payment_status = 'paid'; // The payment status, assuming the payment was successful
$payment_method = 'paypal'; // The payment method (can be dynamic if other methods are added)
$payment_date = date('Y-m-d H:i:s'); // Current timestamp for payment date

// Prepare the SQL statement to update the reservation's payment information
$stmt = $con->prepare("UPDATE reservations SET 
    payment_status = ?, 
    payment_method = ?, 
    transaction_id = ?, 
    amount_paid = ?, 
    payment_date = ? 
    WHERE id = ?"
);

// Check if statement preparation was successful
if (!$stmt) {
    respond(false, 'Failed to prepare SQL statement: ' . $con->error);
}

// Bind the parameters (sssdsi corresponds to: string, string, string, float, string, int)
$stmt->bind_param('sssssi', $payment_status, $payment_method, $transaction_id, $amount_paid, $payment_date, $reservation_id);

// Execute the statement
if ($stmt->execute()) {
    respond(true, 'Payment updated successfully');
} else {
    // Log the actual error for debugging purposes
    error_log('Error in updating reservation payment: ' . $stmt->error);
    respond(false, 'Failed to update reservation payment: ' . $stmt->error);
}

?>
