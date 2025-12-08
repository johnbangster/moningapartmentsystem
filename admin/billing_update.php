<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

if (isset($_POST['update_bill'])) {
    $bill_id = mysqli_real_escape_string($con, $_POST['bill_id']);
    $total_amount = floatval($_POST['total_amount']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    // Fetch renter & bill details
    $bill = mysqli_query($con, "
        SELECT b.*, r.id AS renter_id, r.first_name, r.last_name, r.contacts, r.carry_balance 
        FROM bills b 
        INNER JOIN renters r ON b.renter_id = r.id 
        WHERE b.id='$bill_id' LIMIT 1
    ");

    if (mysqli_num_rows($bill) > 0) {
        $data = mysqli_fetch_assoc($bill);
        $renter_id = $data['renter_id'];
        $renter_name = $data['first_name'] . ' ' . $data['last_name'];
        $contact_number = $data['contacts'];
        $previous_balance = floatval($data['carry_balance']);
        $old_status = $data['status'];

        // Check payments made for this bill
        $payment_query = mysqli_query($con, "
            SELECT IFNULL(SUM(amount),0) AS total_paid 
            FROM payments 
            WHERE bill_id='$bill_id'
        ");
        $payment_data = mysqli_fetch_assoc($payment_query);
        $total_paid = floatval($payment_data['total_paid']);

        // Determine balance difference
        $carry_balance = 0;
        if ($total_paid < $total_amount) {
            // renter still owes some amount (partial payment)
            $carry_balance = $total_paid - $total_amount; // negative value
            $new_status = 'partial';
        } elseif ($total_paid > $total_amount) {
            // renter overpaid
            $carry_balance = $total_paid - $total_amount; // positive value
            $new_status = 'overpaid';
        } else {
            $new_status = 'paid';
        }

        // Update bill record
        $update = mysqli_query($con, "
            UPDATE bills 
            SET total_amount='$total_amount', status='$new_status' 
            WHERE id='$bill_id'
        ");

        if ($update) {
            // Update renter carry balance for future billing
            mysqli_query($con, "
                UPDATE renters 
                SET carry_balance = carry_balance + '$carry_balance' 
                WHERE id='$renter_id'
            ");

            // Send SMS Alert
            if ($carry_balance != 0) {
                $msg = "";

                if ($carry_balance < 0) {
                    $msg = "Hello $renter_name, your recent payment was partial. The remaining balance of ₱" . number_format(abs($carry_balance), 2) . " will be carried over to your next bill. Thank you.";
                } else {
                    $msg = "Hello $renter_name, thank you for your payment! You have overpaid ₱" . number_format($carry_balance, 2) . ". This amount will be deducted from your next bill.";
                }

                // SMS sending function
                $ch = curl_init();
                $parameters = array(
                    'apikey' => 'YOUR_SEMAPHORE_API_KEY', // replace this
                    'number' => $contact_number,
                    'message' => $msg,
                    'sendername' => 'RENTALSYS'
                );
                curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }

            $_SESSION['message'] = "Bill updated successfully! (Status: $new_status)";
        } else {
            $_SESSION['message'] = "Error updating bill: " . mysqli_error($con);
        }
    } else {
        $_SESSION['message'] = "Bill not found!";
    }

    header("Location: billing.php");
    exit();
}
?>
