<?php
require 'admin/config/dbcon.php';
date_default_timezone_set('Asia/Manila');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Invalid request.");
}

$reservation_id = intval($_POST['reservation_id'] ?? 0);
$proof_file = $_FILES['payment_proof'] ?? null;

if($reservation_id === 0 || !$proof_file){
    die("Missing reservation or proof file.");
}

// Upload payment proof
$upload_dir = 'images/gcash_proofs/';
if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$filename = time().'_'.basename($proof_file['name']);
$target = $upload_dir . $filename;

if(move_uploaded_file($proof_file['tmp_name'], $target)){
    // Update reservation
    $update = mysqli_query($con, "UPDATE reservations SET payment_status='paid', payment_date=NOW() WHERE id=$reservation_id");
    
    // Update unit to booked
    $res = mysqli_query($con, "SELECT unit_id FROM reservations WHERE id=$reservation_id");
    $unit_id = mysqli_fetch_assoc($res)['unit_id'];
    mysqli_query($con, "UPDATE units SET status='Booked' WHERE id=$unit_id");

    echo "<script>alert('Payment confirmed!'); window.location.href='thank_you.php';</script>";
} else {
    die("Failed to upload payment proof.");
}
?>
