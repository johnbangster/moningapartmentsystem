<?php
require('../admin/config/dbcon.php');
date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents('php://input'), true);

file_put_contents('paypal_debug.txt', date('Y-m-d H:i:s') . " | Incoming data: " . json_encode($data) . "\n", FILE_APPEND);

$bill_ids = $data['bill_ids'] ?? [];
$total_amount = floatval($data['total_amount'] ?? 0);
$payer_name = mysqli_real_escape_string($con, $data['payer_name'] ?? '');
$transaction_id = mysqli_real_escape_string($con, $data['transaction_id'] ?? '');
$payment_date = date('Y-m-d H:i:s');

if (empty($bill_ids) || $total_amount <= 0 || empty($transaction_id) || empty($payer_name)) {
    file_put_contents('paypal_debug.txt', "Invalid request parameters.\n", FILE_APPEND);
    echo json_encode(['success'=>false, 'message'=>'Invalid request parameters']);
    exit;
}

$check = mysqli_query($con, "SELECT id FROM payments WHERE transaction_id='$transaction_id'");
if (mysqli_num_rows($check) > 0) {
    file_put_contents('paypal_debug.txt', "Duplicate transaction: $transaction_id\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Duplicate transaction']);
    exit;
}

$bill_count = count($bill_ids);
$share_amount = $bill_count > 0 ? $total_amount / $bill_count : 0;

foreach ($bill_ids as $id) {
    $id = intval($id);

    // Get renter info + name
    // $bill_q = mysqli_query($con, "
    //     SELECT b.renter_id, b.total_amount, r.first_name, r.last_name AS renter_name
    //     FROM bills b
    //     JOIN renters r ON b.renter_id = r.id
    //     WHERE b.id = $id
    // ");

    $bill_q = mysqli_query($con, "
        SELECT 
            b.renter_id, 
            b.total_amount, 
            CONCAT(r.first_name, ' ', r.last_name) AS renter_name
        FROM bills b
        JOIN renters r ON b.renter_id = r.id
        WHERE b.id = $id
    ");


    if (!$bill_q || mysqli_num_rows($bill_q) == 0) {
        file_put_contents('paypal_debug.txt', "Bill not found: $id\n", FILE_APPEND);
        continue;
    }

    $bill = mysqli_fetch_assoc($bill_q);
    $renter_id   = $bill['renter_id'];
    $renter_name = $bill['renter_name'];  //Get full name
    $bill_total  = floatval($bill['total_amount']);

    // Insert payment record
    $insert_sql = "INSERT INTO payments (renter_id, bill_id, amount, payment_date, payment_type, transaction_id) 
                   VALUES ($renter_id, $id, $share_amount, '$payment_date', 'paypal', '$transaction_id')";
    if (!mysqli_query($con, $insert_sql)) {
        file_put_contents('paypal_debug.txt', "SQL Error (INSERT payment): " . mysqli_error($con) . "\n", FILE_APPEND);
        continue;
    }

    // Update bill
    $status = $share_amount >= $bill_total ? 'paid' : 'partial';
    mysqli_query($con, "UPDATE bills SET status='$status', payment_date='$payment_date' WHERE id=$id");

    // Notification using renter NAME
    // $msg = "Renter $renter_name paid ₱" . number_format($share_amount,2) . 
    //        " for Bill #$id via PayPal (Txn: $transaction_id).";

    // $notif_sql = "INSERT INTO notifications (user_id, message, type, is_read, created_at) 
    //               VALUES (NULL, '".mysqli_real_escape_string($con, $msg)."', 'bill_paid', 0, NOW())";
    // mysqli_query($con, $notif_sql);

    // Notification using renter NAME
    $msg = "Renter $renter_name paid ₱" . number_format($share_amount,2) . 
        " for Bill #$id via PayPal (Txn: $transaction_id).";

    $notif_sql = "INSERT INTO notifications (user_id, message, type, is_read, created_at) 
                VALUES (NULL, '".mysqli_real_escape_string($con, $msg)."', 'bill_paid', 0, NOW())";
    mysqli_query($con, $notif_sql);

    }

echo json_encode(['success'=>true, 'message'=>'Payment recorded']);
file_put_contents('paypal_debug.txt', "Payment processed successfully for transaction: $transaction_id\n", FILE_APPEND);
?>
