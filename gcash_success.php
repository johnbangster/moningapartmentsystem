<?php
require 'admin/config/dbcon.php';

if(!isset($_GET['session_id'])) exit("Missing session ID");

$payment_id = $_GET['session_id'];

// Update booking
$stmt = $con->prepare("UPDATE bookings SET status='paid' WHERE payment_id=?");
$stmt->bind_param("s",$payment_id);
$stmt->execute();

// Get unit_id
$stmt = $con->prepare("SELECT unit_id FROM bookings WHERE payment_id=? LIMIT 1");
$stmt->bind_param("s",$payment_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows){
    $unit_id = $res->fetch_assoc()['unit_id'];
    $u = $con->prepare("UPDATE units SET status='booked' WHERE id=?");
    $u->bind_param("i",$unit_id);
    $u->execute();
}

echo "<h2>Payment Success!</h2><p>Your booking is confirmed.</p>";
?>
