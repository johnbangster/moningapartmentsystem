<?php
session_start();
require('admin/config/dbcon.php'); 

header('Content-Type: application/json');

// Required POST fields
$required = ['unit_id', 'fname', 'lname', 'email', 'move_in', 'payment_amount', 'payment_status', 'payment_email', 'payment_transaction_id'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
        exit;
    }
}

// Sanitize data
$unit_id       = intval($_POST['unit_id']);
$fname         = mysqli_real_escape_string($con, $_POST['fname']);
$lname         = mysqli_real_escape_string($con, $_POST['lname']);
$email         = mysqli_real_escape_string($con, $_POST['email']);
$contact       = isset($_POST['contact']) ? mysqli_real_escape_string($con, $_POST['contact']) : null;
$move_in       = mysqli_real_escape_string($con, $_POST['move_in']);
$payment_type  = 'full_payment'; // Always full payment for now
$amount        = floatval($_POST['payment_amount']);
$payment_status = strtolower($_POST['payment_status']) === 'completed' ? 'completed' : 'pending';
$payer_email   = mysqli_real_escape_string($con, $_POST['payment_email']);
$transaction_id = mysqli_real_escape_string($con, $_POST['payment_transaction_id']);

// Step 1: Verify unit exists & get price
$unit_q = mysqli_query($con, "SELECT price FROM units WHERE id = $unit_id AND status = 'Available' LIMIT 1");
if (!$unit_q || mysqli_num_rows($unit_q) == 0) {
    echo json_encode(['success' => false, 'error' => 'Unit not available']);
    exit;
}
$unit_data = mysqli_fetch_assoc($unit_q);
$unit_price = floatval($unit_data['price']);

// Step 2: Verify payment matches unit price
if ($amount != $unit_price) {
    echo json_encode(['success' => false, 'error' => 'Payment does not match unit price']);
    exit;
}

// Step 3: Insert into bookings table
$sql = "INSERT INTO bookings 
    (unit_id, fname, lname, email, contact, move_in, payment_type, amount, payment_status, payer_email, paypal_transaction_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "issssssdsss",
    $unit_id, $fname, $lname, $email, $contact, $move_in, $payment_type, $amount, $payment_status, $payer_email, $transaction_id
);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    exit;
}

// Step 4: Mark unit as booked
mysqli_query($con, "UPDATE units SET status = 'Booked' WHERE id = $unit_id");


// Step 5: Send SMS confirmation using Semaphore
if (!empty($contact)) {
    $api_key = "YOUR_SEMAPHORE_API_KEY"; // replace with your Semaphore API key
    $message = "Hi $fname, your booking for '$unit_name' is confirmed. Move-in date: $move_in. Please be aware that your booked unit is valid for one month only. Additionally, please allow us three days to prepare your unit. Thank you!";
    
    $post_fields = [
        'apikey'     => $api_key,
        'number'     => $contact,
        'message'    => $message,
        'sendername' => 'BookingSys'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://semaphore.co/api/v4/messages");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);

    // Optionally, you can log $output for debugging
}

// Step 5: Return success
echo json_encode(['success' => true]);
