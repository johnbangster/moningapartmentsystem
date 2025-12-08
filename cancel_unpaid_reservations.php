<?php
require 'admin/config/dbcon.php';

// Find expired reservations that are unpaid (still pending)
$sql = "SELECT * FROM reservations 
        WHERE payment_method='cash' 
        AND payment_status='pending'
        AND expires_at < NOW()";

$result = mysqli_query($con, $sql);

while($row = mysqli_fetch_assoc($result)){
    $res_id = $row['id'];
    $unit_id = $row['unit_id'];

    // 1. Cancel reservation
    mysqli_query($con, "UPDATE reservations SET payment_status='cancelled' WHERE id='$res_id'");

    // 2. Release unit
    mysqli_query($con, "UPDATE units SET status='Available' WHERE id='$unit_id'");
}

echo "Expired pending cash reservations cleaned.";
?>

