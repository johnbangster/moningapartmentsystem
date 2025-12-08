<?php
session_start();
require('config/dbcon.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($_SESSION['auth_user']) || !in_array($_SESSION['auth_user']['auth_role'], ['admin','superadmin'])){
    echo json_encode(['success'=>false,'message'=>'Access denied']);
    exit;
}

if(!$data || !isset($data['payment_id']) || !isset($data['action'])){
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$payment_id = intval($data['payment_id']);
$action = $data['action']; // confirm or reject

$payment = mysqli_fetch_assoc(mysqli_query($con,"SELECT * FROM payments WHERE id='$payment_id' AND payment_type='cash' AND status='pending'"));
if(!$payment){
    echo json_encode(['success'=>false,'message'=>'Payment not found or already processed']);
    exit;
}

$bill_id = $payment['bill_id'];
$amount_paid = floatval($payment['amount_paid']);

$bill = mysqli_fetch_assoc(mysqli_query($con,"SELECT total_amount, amount_paid, carry_balance FROM bills WHERE id='$bill_id'"));
if(!$bill){
    echo json_encode(['success'=>false,'message'=>'Bill not found']);
    exit;
}

if($action=='confirm'){
    // Update payment status
    mysqli_query($con,"UPDATE payments SET status='confirmed' WHERE id='$payment_id'");

    // Update bill
    $new_total_paid = $bill['amount_paid'] + $amount_paid;
    $new_balance = max($bill['total_amount'] - $new_total_paid,0);
    $status = $new_balance <= 0 ? 'paid' : 'partial';

    mysqli_query($con,"UPDATE bills SET amount_paid='$new_total_paid', balance='$new_balance', status='$status' WHERE id='$bill_id'");

    echo json_encode(['success'=>true,'message'=>'Payment confirmed']);
}else{
    // Reject: remove payment and do not change bill
    mysqli_query($con,"DELETE FROM payments WHERE id='$payment_id'");
    echo json_encode(['success'=>true,'message'=>'Payment rejected']);
}
