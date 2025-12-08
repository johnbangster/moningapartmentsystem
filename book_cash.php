<?php
require 'admin/config/dbcon.php';       // Database connection
require 'admin/config/code.php';        // filteration() function
require 'function_booking.php';         // cleanup_expired_cash_reservations()

session_start();

// Function to insert notifications
function addNotification($con, $message, $type, $user_id = NULL) {
    $user_val = $user_id !== NULL ? intval($user_id) : "NULL";
    $msg      = mysqli_real_escape_string($con, $message);
    $typ      = mysqli_real_escape_string($con, $type);

    $sql = "INSERT INTO notifications (user_id, message, type, status, is_read)
            VALUES ($user_val, '$msg', '$typ', 'pending', 0)";
    mysqli_query($con, $sql);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cleanup expired cash reservations first
    cleanup_expired_cash_reservations($con);

    // Sanitize POST data
    $data = filteration($_POST);
    $unit_id = intval($data['unit_id']);

    // Fetch the unit (Available or Reserved but not removed)
    $unit_res = mysqli_query($con, "
        SELECT * FROM units 
        WHERE id='$unit_id' 
          AND removed=0 
          AND (status='Available' OR status='Reserved')
        LIMIT 1
    ");
    $unit_data = mysqli_fetch_assoc($unit_res);

    if (!$unit_data) {
        echo json_encode(['success' => false, 'message' => 'Unit does not exist or is unavailable']);
        exit;
    }

    // If unit is Reserved, check for active cash reservation
    if ($unit_data['status'] === 'Reserved') {
        $check_res = mysqli_query($con, "
            SELECT * FROM reservations 
            WHERE unit_id='$unit_id' 
              AND payment_method='cash' 
              AND payment_status='pending'
            LIMIT 1
        ");
        if (mysqli_num_rows($check_res) > 0) {
            $res_data = mysqli_fetch_assoc($check_res);
            $expiry = strtotime($res_data['payment_date'] . ' +3 days');
            if (time() < $expiry) {
                echo json_encode(['success' => false, 'message' => 'Unit already reserved by another cash booking']);
                exit;
            }
        }
    }

    // Prepare reservation data
    $fname = $data['fname'];
    $lname = $data['lname'];
    $email = $data['email'];
    $phone = $data['contact'];
    $move_in = $data['move_in'];
    $amount = floatval($data['amount']);
    $payment_date = date('Y-m-d H:i:s'); // reservation timestamp
    $status = 'pending';
    $payment_type = 'reserve_only';
    $payment_method = 'cash';

    $stmt = $con->prepare("
        INSERT INTO reservations 
        (fname, lname, email, phone, move_in_date, unit_id, payment_status, payment_type, payment_method, amount_paid, payment_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssisssds",
        $fname,
        $lname,
        $email,
        $phone,
        $move_in,
        $unit_id,
        $status,         // STRING
        $payment_type,   // STRING
        $payment_method, // STRING
        $amount,         // DOUBLE
        $payment_date    // STRING
    );
    // $payment_method = 'cash';
    // $payment_method = trim(strtolower($payment_method ?? 'cash'));


    // // Insert reservation
    // $stmt = $con->prepare("
    //     INSERT INTO reservations 
    //     (fname, lname, email, phone, move_in_date, unit_id, payment_status, payment_type, payment_method, amount_paid, payment_date) 
    //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    // ");

    // $stmt->bind_param("sssssiissds",
    // $fname,
    // $lname,
    // $email,
    // $phone,
    // $move_in,
    // $unit_id,
    // $status,
    // $payment_type,
    // $payment_method, 
    // $amount,
    // $payment_date

    // );


    // $stmt->bind_param(
    //     "sssssiissds",
    //     $fname,
    //     $lname,
    //     $email,
    //     $phone,
    //     $move_in,
    //     $unit_id,
    //     $status,
    //     $payment_type,
    //     $payment_method,
    //     $amount,
    //     $payment_date
    // );

    $exec = $stmt->execute();

    if ($exec) {
        // Lock unit as Reserved
        mysqli_query($con, "UPDATE units SET status='Reserved' WHERE id='$unit_id'");

        // Prepare notification messages
        $full_name = $fname . ' ' . $lname;
        $unit_name = $unit_data['name'];

        // Send notification to admin
        addNotification(
            $con,
            "A new reservation has been created by {$full_name} (unit: {$unit_name}).",
            "reservation_admin"
        );

        // Send notification to employee
        addNotification(
            $con,
            "A new reservation requires review (unit: {$unit_name}).",
            "reservation_employee"
        );

        echo json_encode(['success' => true, 'message' => 'Unit reserved! Please pay in 3 days to confirm.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unable to reserve unit.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

