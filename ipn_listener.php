<?php
require 'admin/config/dbcon.php';

$rawData = file_get_contents("php://input");
$data  = json_decode($rawData,true);

//validation
if(!$data || !isset($data['booking_id']) || !isset($data['paypal_transaction_id'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$booking_id = intval($data['booking_id']);
$payer_email = trim($data['payer_email']);
$txn_id =  trim( $data['paypal_transaction_id']);
$amount =  floatval($data['amount']);
$status = "completed";

//fetch booking
$sql = "SELECT b.unit_id, u.price FROM bookings b
        INNER JOIN units u ON b.unit_id = u.id
        WHERE b.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i",$booking_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    http_response_code(404);
    echo json_encode(['success'=>false, 'message'=>'Booking not found']);
    exit;
}

$row = $result->fetch_assoc();
$expected_price = floatval($row['price']);

//check amount of unit
if (abs($amount - $expected_price) > 0.01) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => "Amount mismatch. Expected {$expected_price}, got {$amount}"
    ]);
    exit;
}

//update booking record
$update =$con->prepare("UPDATE bookings SET payment_status=?, payer_email=?, paypal_transaction_id=?,
                        amount=?, updated_at = NOW() WHERE id=?");
$update->bind_param("sssdi",$status,$payer_email,$txn_id,$amount,$booking_id);

if($update->execute()) {
    echo json_encode(['succes'=>true]);

}else {
    echo json_encode(['success'=>false, 'message'=>'Datbase update failed'. $update->error]);
}



