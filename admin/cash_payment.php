<?php
session_start();
require('config/dbcon.php');

if (isset($_POST['cash_pay'])) {
    $bill_id = mysqli_real_escape_string($con, $_POST['bill_id']);
    $reference = mysqli_real_escape_string($con, $_POST['reference_number']);

    // Get bill details
    $billQuery = mysqli_query($con,"SELECT * FROM bills WHERE id='$bill_id' 
                    AND (status='open' OR status='awaiting_confirmation') LIMIT 1");

    if (mysqli_num_rows($billQuery) > 0) {
        $bill = mysqli_fetch_assoc($billQuery);
        $amount = $bill['total_amount'];
        $renter_id = $bill['renter_id'];

        // Insert payment
        $insert = mysqli_query($con, "
            INSERT INTO payments (renter_id, bill_id, amount, payment_date, payment_type, transaction_id)
            VALUES ('$renter_id', '$bill_id', '$amount', NOW(), 'cash', '$reference')
        ");

        if ($insert) {
            // Update bill status
            mysqli_query($con, "
                UPDATE bills 
                SET status='paid', payment_date=NOW() 
                WHERE id='$bill_id'
            ");

            /** 
             * Insert notification for admin
             */
             $notify_message = "Renter #$renter_id submitted a CASH payment for Bill #$bill_id (Ref#: $reference).";
                                mysqli_query($con, "INSERT INTO notifications (user_id, message, type) 
                                VALUES (NULL, '$notify_message', 'cash_payment')");
            // $notify_message = "Renter #$renter_id submitted a CASH payment for Bill #$bill_id (Ref#: $reference).";
            $admins = mysqli_query($con, "SELECT id FROM users WHERE role='admin' AND status='Active'");
            while ($admin = mysqli_fetch_assoc($admins)) {
                $admin_id = $admin['id'];
                mysqli_query($con, "INSERT INTO notifications (user_id, message, type, is_read) 
                                    VALUES ($admin_id, '$notify_message', 'cash_payment', 0)");
            }

            // Get renter contact info
            $renterQuery = mysqli_query($con, "SELECT first_name, contacts FROM renters WHERE id = '$renter_id'");
            if ($renterQuery && mysqli_num_rows($renterQuery) > 0) {
                $renter = mysqli_fetch_assoc($renterQuery);
                $contact = $renter['contacts'];
                $name = $renter['first_name'];

                // Format message
                $message = "Hello $name, we received your CASH payment for bill Ref#: $reference. Thank you!";

                // Send SMS using Semaphore API
                $semaphore_apikey = 'your_semaphore_api_key';
                $semaphore_sender = 'SEMAPHORE';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'apikey' => $semaphore_apikey,
                    'number' => $contact,
                    'message' => $message,
                    'sendername' => $semaphore_sender
                ]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
            } else {
                $_SESSION['message'] = "Failed to retrieve renter information.";
                header('Location: transaction.php');
                exit();
            }

            $_SESSION['message'] = "Cash payment successful for Ref#: $reference";
        } else {
            $_SESSION['message'] = "Payment failed to record.";
        }
    } else {
        $_SESSION['message'] = "Bill not found or already paid.";
    }

    header('Location: transaction.php');
    exit();
}
?>
