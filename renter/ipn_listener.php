<?php
require('../admin/config/dbcon.php');

// Get raw IPN data
$raw_post_data = file_get_contents('php://input');
parse_str($raw_post_data, $data);

// Log for debugging
file_put_contents("ipn_log.txt", date('Y-m-d H:i:s') . " | " . json_encode($data) . "\n", FILE_APPEND);

// PayPal IPN data
$renter_id      = intval($data['custom']);        // renter_id passed as custom
$amount_paid    = floatval($data['mc_gross']);    // amount
$payment_status = $data['payment_status'] ?? '';
$txn_id         = mysqli_real_escape_string($con, $data['txn_id'] ?? '');
$bill_ids_raw   = $data['invoice'] ?? '';         // e.g. "12,15,18"
$bill_ids       = array_filter(array_map('intval', explode(',', $bill_ids_raw)));

if ($payment_status !== 'Completed') {
    exit; // Ignore failed or pending
}

$payment_date = date('Y-m-d H:i:s');

// Mark each bill as paid
foreach ($bill_ids as $id) {
    mysqli_query($con, "
        UPDATE bills 
        SET status = 'paid', payment_date = '$payment_date'
        WHERE id = $id
    ");
}

// Insert payment record
mysqli_query($con, "
    INSERT INTO payments (renter_id, amount, payment_type, payment_date, transaction_id) 
    VALUES (
        $renter_id,
        $amount_paid,
        'paypal',
        '$payment_date',
        '$txn_id'
    )
");

// Create an admin notification
// $notify_message = "Renter #$renter_id paid ₱" . number_format($amount_paid, 2) . " via PayPal (Txn: $txn_id).";

// mysqli_query($con, "
//     INSERT INTO notifications (user_id, message, type, is_read, created_at) 
//     VALUES (
//         NULL,
//         '".mysqli_real_escape_string($con, $notify_message)."',
//         'bill_paid',
//         0,
//         NOW()
//     )
// ");


$notify_message = "Payment of ₱" . number_format($amount_paid, 2) . " via PayPal (Txn: $txn_id).";

//Notify Admin
mysqli_query($con, "
    INSERT INTO notifications (user_id, message, type, is_read, created_at) 
    VALUES (
        NULL, 
        '".mysqli_real_escape_string($con, "Renter #$renter_id made $notify_message")."',
        'bill_paid',
        0,
        NOW()
    )
");

//Notify All Employees
$emp_query = mysqli_query($con, "SELECT id FROM users WHERE role = 'employee'");
while ($emp = mysqli_fetch_assoc($emp_query)) {
    $emp_id = intval($emp['id']);
    mysqli_query($con, "
        INSERT INTO notifications (user_id, message, type, is_read, created_at)
        VALUES (
            $emp_id,
            '".mysqli_real_escape_string($con, "Renter #$renter_id made $notify_message")."',
            'bill_paid',
            0,
            NOW()
        )
    ");
}

//Notify the specific Renter
mysqli_query($con, "
    INSERT INTO notifications (user_id, message, type, is_read, created_at)
    VALUES (
        $renter_id,
        '".mysqli_real_escape_string($con, "We received your payment of ₱" . number_format($amount_paid, 2) . " via PayPal. Thank you!")."',
        'bill_paid',
        0,
        NOW()
    )
");

file_put_contents("ipn_log.txt", date('Y-m-d H:i:s') . " | Notifications created for renter $renter_id\n", FILE_APPEND);

?>
