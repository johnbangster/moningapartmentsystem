<?php
require 'admin/config/dbcon.php';
header('Content-Type: application/json');

// Read PayPal POST payload
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate
if (!$data || !isset($data['reservation_id'], $data['payer_email'], $data['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$reservation_id = intval($data['reservation_id']);
$amount_paid    = floatval($data['amount']);

// Fetch reservation
$res_query = $con->prepare("SELECT * FROM reservations WHERE id = ? LIMIT 1");
$res_query->bind_param("i", $reservation_id);
$res_query->execute();
$res_result = $res_query->get_result();

if ($res_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Reservation not found']);
    exit;
}

$reservation = $res_result->fetch_assoc();
$res_query->close();

// Verify full payment
$total_amount = floatval($reservation['amount_due']); // or 'total_amount' depending on your DB

if ($amount_paid < $total_amount) {
    echo json_encode(['success' => false, 'message' => 'Amount mismatch (not full payment)']);
    exit;
}

$unit_id = $reservation['unit_id'];

$con->begin_transaction();

try {

    // Mark reservation as PAID + RESERVED
    $stmt1 = $con->prepare("
        UPDATE reservations 
        SET payment_status='paid', 
            reservation_status='reserved',
            updated_at=NOW()
        WHERE id=?
    ");
    $stmt1->bind_param("i", $reservation_id);
    $stmt1->execute();
    $stmt1->close();

    // Mark unit as BOOKED
    $stmt2 = $con->prepare("
        UPDATE units 
        SET status='Booked', updated_at=NOW() 
        WHERE id=?
    ");
    $stmt2->bind_param("i", $unit_id);
    $stmt2->execute();
    $stmt2->close();

    $con->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified. Reservation confirmed. Unit marked as BOOKED.'
    ]);

} catch (Exception $e) {
    $con->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Transaction failed: ' . $e->getMessage()
    ]);
}

$con->close();
