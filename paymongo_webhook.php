<?php
require 'admin/config/dbcon.php';

// Read raw POST body
$input = file_get_contents('php://input');
$event = json_decode($input, true);

if (!$event || !isset($event['data'])) {
    http_response_code(400);
    exit("Invalid payload");
}

// Only handle payment success
$event_type = $event['data']['attributes']['type'] ?? '';
if ($event_type === 'checkout_session.payment.paid') {

    $session = $event['data']['attributes']['data'] ?? [];
    $attributes = $session['attributes'] ?? [];
    
    // 1. Retrieve metadata
    $metadata = $attributes['metadata'] ?? [];
    $unit_id   = $metadata['unit_id'] ?? null;
    $unit_name = $metadata['unit_name'] ?? $attributes['line_items'][0]['name'] ?? 'Unit';
    $full_name = $metadata['full_name'] ?? '';
    $email     = $metadata['email'] ?? '';
    $contact   = $metadata['contact'] ?? '';
    $move_in   = $metadata['move_in'] ?? null;
    // $move_out  = $metadata['move_out'] ?? null;

    // 2. Total amount (sum of line_items)
    $total_amount = 0;
    foreach ($attributes['line_items'] as $item) {
        $total_amount += ($item['amount'] ?? 0);
    }
    $total_amount = $total_amount / 100;

    // 3. Save booking
    $stmt = $conn->prepare("INSERT INTO bookings (unit_id, unit_name, full_name, email, contact, move_in, total_amount, payment_type, payment_status, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'gcash', 'paid', 'confirmed', NOW())");
    $stmt->bind_param("isssssds", $unit_id, $unit_name, $full_name, $email, $contact, $move_in, $total_amount);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();

    // 4. Update unit status
    if ($unit_id) {
        $stmt2 = $conn->prepare("UPDATE units SET status='Occupied' WHERE id=?");
        $stmt2->bind_param("i", $unit_id);
        $stmt2->execute();
        $stmt2->close();
    }

    http_response_code(200);
    echo "Webhook processed. Booking ID: $booking_id";

} else {
    // Ignore other events
    http_response_code(200);
    echo "Event ignored: $event_type";
}

$conn->close();
?>
