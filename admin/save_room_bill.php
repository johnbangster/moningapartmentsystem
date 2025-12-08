<?php
session_start();
require 'config/dbcon.php';
date_default_timezone_set('Asia/Manila');

function sendSMS($number, $message) {
    $api_key = 'ad1be2dd7b2999a0458b90c264aa4966';
    $url = 'https://semaphore.co/api/v4/messages';
    $data = [
        'apikey' => $api_key,
        'number' => $number,
        'message' => $message,
        'sendername' => 'MONINGSRENT'
    ];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => http_build_query($data)
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    file_put_contents('sms_log.txt', "[".date('Y-m-d H:i:s')."] $number | $message | $response\n", FILE_APPEND);
}

// Get inputs
$renter_id = intval($_POST['renter_id']);
$unit_id = intval($_POST['unit_id']);
$bill_month = $_POST['bill_month'];
$ref_no = $_POST['ref_no'];
$unit_price = floatval($_POST['unit_price']);
$total_amount = floatval($_POST['total_amount']);
$addon_names = $_POST['addon_name'] ?? [];
$addon_amounts = $_POST['addon_amount'] ?? [];

// Prevent past month
if ($bill_month < date('Y-m')) {
    echo json_encode(['status' => 'error', 'message' => 'Past month billing not allowed.']);
    exit;
}

// Check duplicate
$check = mysqli_query($con, "SELECT id FROM bills WHERE unit_id='$unit_id' AND bill_month='$bill_month'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Bill for this month already exists.']);
    exit;
}

// Lease validation
$lease = mysqli_query($con, "SELECT lease_start, lease_end FROM rental_agreements WHERE unit_id='$unit_id' AND renter_id='$renter_id' AND status='active'");
if (mysqli_num_rows($lease) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'No active lease found for this renter.']);
    exit;
}
$leaseData = mysqli_fetch_assoc($lease);
if ($bill_month < date('Y-m', strtotime($leaseData['lease_start'])) || $bill_month > date('Y-m', strtotime($leaseData['lease_end']))) {
    echo json_encode(['status' => 'error', 'message' => 'Bill month outside lease period.']);
    exit;
}

// Insert bill
$due_date = date('Y-m-d', strtotime('+3 days'));
mysqli_query($con, "INSERT INTO bills (renter_id, unit_id, bill_month, ref_no, total_amount, status, due_date, generated_by, generated_role)
VALUES ('$renter_id', '$unit_id', '$bill_month', '$ref_no', '$total_amount', 'open', '{$_SESSION['auth_user']['username']}', '{$_SESSION['auth_role']}')");

$bill_id = mysqli_insert_id($con);

// Insert add-ons
for ($i = 0; $i < count($addon_names); $i++) {
    $name = mysqli_real_escape_string($con, $addon_names[$i]);
    $amt = floatval($addon_amounts[$i]);
    mysqli_query($con, "INSERT INTO bill_addon (bill_id, name, amount) VALUES ('$bill_id', '$name', '$amt')");
}

// Send SMS
$renter = mysqli_fetch_assoc(mysqli_query($con, "SELECT contacts FROM renters WHERE id='$renter_id'"));
if (!empty($renter['contacts'])) {
    $msg = "Your room bill for $bill_month has been created. Total ₱$total_amount. Ref: $ref_no. Due: $due_date";
    sendSMS($renter['contacts'], $msg);
}

echo json_encode(['status' => 'success', 'message' => 'Room bill created successfully and SMS sent.']);
