<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

if(isset($_POST['update_bill'])) {
    $bill_id = intval($_POST['bill_id']);
    $new_status = mysqli_real_escape_string($con, $_POST['status']);
    $total_amount = floatval($_POST['total_amount']);
    $amount_paid_input = floatval($_POST['amount_paid'] ?? 0); // optional: if you allow editing amount directly
    $remarks = mysqli_real_escape_string($con, $_POST['remarks']);
    $admin_name = $_SESSION['auth_user']['username'] ?? 'admin';

    // Fetch current bill
    $bill = mysqli_fetch_assoc(mysqli_query($con, "SELECT renter_id, amount_paid, balance, carry_balance, total_amount, status FROM bills WHERE id='$bill_id'"));
    if(!$bill) {
        echo json_encode(['status'=>'error', 'message'=>'Bill not found']);
        exit;
    }

    $renter_id = $bill['renter_id'];
    $prev_paid = floatval($bill['amount_paid']);
    $prev_balance = floatval($bill['balance']);
    $prev_carry = floatval($bill['carry_balance']);
    $current_total = floatval($bill['total_amount']);

    // Determine new payment logic
    $current_due = ($prev_balance > 0) ? $prev_balance : max(($current_total + $prev_carry - $prev_paid), 0);
    $new_total_paid = $prev_paid;
    $new_balance = $current_due;
    $carry_balance = 0;
    $final_status = $new_status;

    // If status is paid, update amounts
    if($new_status === 'paid') {
        $new_total_paid = $current_total + $prev_carry;
        $new_balance = 0;
        $final_status = 'paid';
    } elseif($new_status === 'partial') {
        $new_total_paid = $prev_paid + $amount_paid_input;
        $new_balance = $current_due - $amount_paid_input;

        if($new_balance < 0) { // overpaid
            $carry_balance = $new_balance; // negative = credit
            $new_balance = 0;
            $final_status = 'overpaid';
        }
    } elseif($new_status === 'open') {
        // Reset partial payment
        $new_balance = $current_total;
        $new_total_paid = 0;
        $carry_balance = 0;
    }

    // Update bill
    $update = mysqli_query($con, "UPDATE bills SET 
        status='$final_status',
        total_amount='$total_amount',
        amount_paid='$new_total_paid',
        balance='$new_balance',
        carry_balance='$carry_balance',
        remarks='$remarks'
        WHERE id='$bill_id'");

    if($update) {
        // Insert payment if partial or paid
        if(in_array($final_status, ['paid','partial','overpaid'])) {
            // Generate reference number
            $ref_prefix = "EDIT-" . date("Ymd") . "-";
            $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM payments WHERE DATE(payment_date) = CURDATE()"));
            $reference_number = $ref_prefix . str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);

            $payment_amount = $new_total_paid - $prev_paid;

            mysqli_query($con, "INSERT INTO payments
                (renter_id, bill_id, amount, payment_type, payment_date, issued_by, remarks, reference_number, status, carry_balance)
                VALUES
                ('$renter_id', '$bill_id', '$payment_amount', 'cash', NOW(), '$admin_name', '$remarks', '$reference_number', '$final_status', '$carry_balance')");
            
            $payment_id = mysqli_insert_id($con);

            // Notifications
            $message = "Bill #$bill_id updated. Payment: ₱" . number_format($payment_amount,2) . " (Ref: $reference_number)";
            $type = 'payment';
            $created_at = date('Y-m-d H:i:s');

            // Notify admins/employees
            $users = mysqli_query($con, "SELECT id FROM users WHERE role IN ('admin','employee')");
            while($u = mysqli_fetch_assoc($users)) {
                mysqli_query($con, "INSERT INTO notifications (user_id, message, type, created_at, is_read) 
                                    VALUES ('{$u['id']}', '$message', '$type', '$created_at', 0)");
            }

            // Notify renter
            mysqli_query($con, "INSERT INTO notifications (user_id, message, type, created_at, is_read) 
                                VALUES ('$renter_id', '$message', '$type', '$created_at', 0)");
        }

        echo json_encode(['status'=>'success','message'=>'Bill updated successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'DB Error: '.mysqli_error($con)]);
    }

    exit;
}




