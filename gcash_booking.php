<?php
require 'admin/config/dbcon.php';
require 'function_booking.php'; // addNotification() function
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

function addNotification($con, $message, $type, $user_id = NULL) {
    $user_val = $user_id !== NULL ? intval($user_id) : "NULL";
    $msg      = mysqli_real_escape_string($con, $message);
    $typ      = mysqli_real_escape_string($con, $type);

    $sql = "INSERT INTO notifications (user_id, message, type, status, is_read)
            VALUES ($user_val, '$msg', '$typ', 'pending', 0)";
    mysqli_query($con, $sql);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

//Sanitize inputs
$fname         = mysqli_real_escape_string($con, $_POST['fname'] ?? '');
$lname         = mysqli_real_escape_string($con, $_POST['lname'] ?? '');
$email         = mysqli_real_escape_string($con, $_POST['email'] ?? '');
$phone         = mysqli_real_escape_string($con, $_POST['contact'] ?? '');
$move_in_date  = mysqli_real_escape_string($con, $_POST['move_in'] ?? '');
$unit_id       = intval($_POST['unit_id'] ?? 0);
$amount_paid   = floatval($_POST['amount'] ?? 0);
$unit_name     = mysqli_real_escape_string($con, $_POST['unit_name'] ?? '');
$source_id     = mysqli_real_escape_string($con, $_POST['source_id'] ?? null);

//Validate required fields
if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($move_in_date) || $unit_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

//Payment info
$payment_type   = 'full_payment';
$payment_status = 'paid';
$payment_method = 'gcash';
$move_out_date  = date('Y-m-d', strtotime("$move_in_date +1 month"));

//Insert reservation record
$query = "INSERT INTO reservations 
    (fname, lname, email, phone, move_in_date, unit_id, source_id, 
     payment_status, payment_type, payment_method, amount_paid, payment_date, expires_at)
    VALUES 
    ('$fname', '$lname', '$email', '$phone', '$move_in_date', '$unit_id', 
     '$source_id', '$payment_status', '$payment_type', '$payment_method', '$amount_paid', NOW(), NULL)";

if (mysqli_query($con, $query)) {
    $reservation_id = mysqli_insert_id($con);

    //Update unit status to "Booked"
    $update = mysqli_query($con, "UPDATE units SET status='Booked' WHERE id='$unit_id'");
    if (!$update) {
        echo json_encode([
            'success' => false,
            'message' => 'Reservation saved but failed to update unit status: ' . mysqli_error($con)
        ]);
        exit;
    }

    //ADD NOTIFICATIONS FOR ADMIN AND EMPLOYEE 
    $message = "New reservation by {$fname} {$lname} for unit {$unit_name}. Amount Paid: ₱{$amount_paid}";

    // Get all admin and employee users (exclude super_admin)
    $users = mysqli_query($con, "SELECT id, role FROM users WHERE role IN ('admin','employee')");
    while ($user = mysqli_fetch_assoc($users)) {
        $type = ($user['role'] === 'admin') ? 'reservation_admin' : 'reservation_employee';
        addNotification($con, $message, $type, $user['id']);
    }
  

    //Redirect to GCash payment page (send via JSON)
    $gcash_redirect = "gcash_create_payment.php?reservation_id={$reservation_id}";

    echo json_encode([
        'success' => true,
        'message' => 'Reservation saved and unit booked successfully. Notifications sent.',
        'reservation_id' => $reservation_id,
        'redirect' => $gcash_redirect
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($con)
    ]);
}
?>
