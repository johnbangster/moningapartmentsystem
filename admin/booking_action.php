<?php
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

//SMS Function (Semaphore)
function sendSMS($number, $message) {
    $api_key = "ad1be2dd7b2999a0458b90c264aa4966";
    $url = "https://api.semaphore.co/api/v4/messages";

    $data = [
        'apikey' => $api_key,
        'number' => $number,
        'message' => $message,
        'sendername' => 'MONINGSRENT'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}


if (isset($_POST['mark_assigned'])) {
    $reservation_id = $_POST['reservation_id'];
    $unit_id = $_POST['unit_id'];

    //  Update unit to "Occupied"
    mysqli_query($con, "UPDATE units SET status='Occupied' WHERE id='$unit_id'");

    //  Update reservation to assigned
    mysqli_query($con, "UPDATE reservations SET status='Assigned' WHERE id='$reservation_id'");

    //  Log action
    mysqli_query($con, "
        INSERT INTO unit_history (unit_id, action, remarks)
        VALUES ('$unit_id', 'Mark as Assigned', 'Unit assigned to renter from reservation #$reservation_id')
    ");

    header("Location: reservations_admin.php?msg=assigned_success");
    exit;
}

//Cancel Reservation
if (isset($_POST['cancel_reservation'])) {
    $reservation_id = $_POST['reservation_id'];

    // Update reservation & free unit
    $res = mysqli_query($con, "SELECT unit_id FROM reservations WHERE id='$reservation_id'");
    $row = mysqli_fetch_assoc($res);
    $unit_id = $row['unit_id'];

    mysqli_query($con, "UPDATE reservations SET status='Cancelled' WHERE id='$reservation_id'");
    mysqli_query($con, "UPDATE units SET status='Available', booked_until=NULL WHERE id='$unit_id'");

    // Log
    mysqli_query($con, "
        INSERT INTO unit_history (unit_id, action, remarks)
        VALUES ('$unit_id', 'Cancel Reservation', 'Reservation #$reservation_id cancelled.')
    ");

    header("Location: reservations_admin.php?msg=cancel_success");
    exit;
}

//Send SMS Reminder
if (isset($_POST['send_sms'])) {
    $reservation_id = $_POST['reservation_id'];
    $phone = $_POST['phone'];
    $move_in_date = $_POST['move_in_date'];

    // Check if reservation is fully paid
    $query = mysqli_query($con, "SELECT payment_status FROM reservations WHERE id='$reservation_id'");
    $res = mysqli_fetch_assoc($query);

    if (strtolower($res['payment_status']) === 'full') {
        $message = "Your booking payment is verified! Please prepare for move-in on $move_in_date.";
        sendSMS($phone, $message);
    } else {
        $message = "Reminder: Your booking will expire soon. Please confirm your move-in or contact admin.";
        sendSMS($phone, $message);
    }

    // Log SMS
    mysqli_query($con, "
        INSERT INTO sms_logs (reservation_id, phone, message, sent_at)
        VALUES ('$reservation_id', '$phone', '" . mysqli_real_escape_string($con, $message) . "', NOW())
    ");

    header("Location: reservations_admin.php?msg=sms_sent");
    exit;
}
?>
