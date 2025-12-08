<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config/dbcon.php';
header('Content-Type: application/json');

if (!isset($_GET['renter_id'])) {
    echo json_encode(['success'=>false,'message'=>'Renter ID missing']);
    exit;
}

$renter_id = (int) $_GET['renter_id'];
if (!$renter_id) {
    echo json_encode(['success'=>false,'message'=>'Invalid renter ID']);
    exit;
}

// Fetch renter info
$resR = mysqli_query($con, "SELECT CONCAT(first_name,' ',last_name) AS full_name, contacts 
        FROM renters 
        WHERE id=$renter_id LIMIT 1");

if (!$resR) {
    echo json_encode(['success'=>false,'message'=>'Database error: ' . mysqli_error($con)]);
    exit;
}

$renter = mysqli_fetch_assoc($resR);
if (!$renter) {
    echo json_encode(['success'=>false,'message'=>'Renter not found']);
    exit;
}

// Fetch open bills for this renter
$resB = mysqli_query($con, "SELECT id, billing_month, total_amount, note,reference_id
        FROM bills 
        WHERE renter_id=$renter_id AND status='open' 
        ORDER BY billing_month ASC");

$bills = [];
while ($row = mysqli_fetch_assoc($resB)) {
    $bills[] = [
        'id' => $row['id'],
        'month_year' => $row['billing_month'],
        'amount' => $row['total_amount'],
        'description' => $row['note'],
        'reference_id' => $row['reference_id']
    ];
}

echo json_encode([
    'success'=>true,
    'renter'=>$renter,
    'bills'=>$bills
]);
