<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['cash_pay'])) {
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

header('Content-Type: application/json');

$bill_id     = intval($_POST['bill_id']);
$renter_id   = intval($_POST['renter_id']);
$amount_paid = floatval($_POST['amount_paid']);
$remarks     = mysqli_real_escape_string($con, $_POST['remarks']);
$admin_name  = $_SESSION['auth_user']['username'] ?? 'admin';

// ... (your same logic here)

echo json_encode([
    'status' => 'success',
    'payment_id' => $payment_id,
    'reference_number' => $reference_number
]);
exit;
