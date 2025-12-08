<?php
session_start();
require 'config/dbcon.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

// Ensure admin access
if(!isset($_SESSION['auth']) || $_SESSION['auth_role'] !== 'admin'){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

if(isset($_POST['verify_payment'])){
    $payment_id = intval($_POST['payment_id']);
    $bill_id    = intval($_POST['bill_id']);

    if($payment_id <= 0 || $bill_id <= 0){
        echo json_encode(['status'=>'error','message'=>'Invalid payment or bill ID']);
        exit;
    }

    // Check if already verified
    $check = mysqli_query($con, "SELECT verified, amount_paid FROM cash_reports WHERE id='$payment_id' LIMIT 1");
    if(mysqli_num_rows($check) == 0){
        echo json_encode(['status'=>'error','message'=>'Payment not found']);
        exit;
    }
    $cash = mysqli_fetch_assoc($check);
    if($cash['verified'] == 1){
        echo json_encode(['status'=>'error','message'=>'Payment already verified']);
        exit;
    }

    // Begin transaction
    mysqli_begin_transaction($con);

    try {
        // Mark cash report as verified
        mysqli_query($con, "UPDATE cash_reports SET verified=1 WHERE id='$payment_id'");

        // Update bill status to 'paid'
        mysqli_query($con, "UPDATE bills SET status='paid' WHERE id='$bill_id'");

        // Fetch updated bill info
        $billInfo = mysqli_query($con, "SELECT reference_id, status FROM bills WHERE id='$bill_id' LIMIT 1");
        $bill = mysqli_fetch_assoc($billInfo);

        mysqli_commit($con);

        // Return success
        echo json_encode([
            'status' => 'success',
            'reference_number' => $bill['reference_id'],
            'amount_paid' => number_format($cash['amount_paid'], 2),
            'bill_status' => $bill['status'],
            'carry_balance' => 0 // modify if you implement overpayment logic
        ]);

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['status'=>'error','message'=>'Failed to verify payment: '.$e->getMessage()]);
    }
    exit;
}
?>
