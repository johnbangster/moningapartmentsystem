<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

function generateReferenceID() {
    return 'BILL-' . strtoupper(uniqid());
}

function sendSMS($number, $message) {
    $ch = curl_init();
    $parameters = [
        'apikey' => 'ad1be2dd7b2999a0458b90c264aa4966',
        'number' => $number,
        'message' => $message,
        'sendername' => 'MONINGSRENT'
    ];
    curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

if (isset($_POST['unit_id'])) {
    $unit_id = mysqli_real_escape_string($con, $_POST['unit_id']);

    // Fetch renter/unit info
    $renterQuery = mysqli_query($con, "
        SELECT 
            r.id AS renter_id, 
            r.first_name, 
            r.last_name,
            r.contacts,
            r.carry_balance,
            u.price 
        FROM renters r 
        INNER JOIN units u ON r.unit_id = u.id
        WHERE u.id = '$unit_id' AND (r.status='Active' OR r.status='Occupied')
        LIMIT 1
    ");

    if (mysqli_num_rows($renterQuery) > 0) {
        $data = mysqli_fetch_assoc($renterQuery);
        $renter_id = $data['renter_id'];
        $renter_name = $data['first_name'] . ' ' . $data['last_name'];
        $contact_number = $data['contacts'];
        $base_amount = floatval($data['price']);
        $carry_balance = floatval($data['carry_balance']); // can be negative (credit)

        // Get previous unpaid or overpaid amounts from last bill
        $prevBill = mysqli_query($con, "
            SELECT total_amount, 
                   COALESCE((SELECT SUM(amount) FROM payments WHERE bill_id=b.id AND status='confirmed'),0) AS paid_amount
            FROM bills b
            WHERE renter_id='$renter_id'
            ORDER BY id DESC
            LIMIT 1
        ");

        if (mysqli_num_rows($prevBill) > 0) {
            $prev = mysqli_fetch_assoc($prevBill);
            $total = floatval($prev['total_amount']);
            $paid = floatval($prev['paid_amount']);
            $carry_balance += ($total - $paid); // previous remaining/unapplied payment
        }

        // Calculate new bill total with carry_balance applied
        $adjusted_total = $base_amount + $carry_balance;

        $note = '';
        if ($carry_balance < 0) {
            // Negative carry_balance = credit/overpayment
            if ($adjusted_total <= 0) {
                $note = "Full overpayment of ₱" . number_format(abs($carry_balance),2) . " applied. No new bill due.";
                $carry_balance = $adjusted_total; // still negative credit retained
                $adjusted_total = 0;
            } else {
                $note = "Overpayment of ₱" . number_format(abs($carry_balance),2) . " deducted from this bill.";
                $carry_balance = 0;
            }
        } elseif ($carry_balance > 0) {
            $note = "Unpaid balance of ₱" . number_format($carry_balance,2) . " added to new bill.";
            $carry_balance = 0;
        } else {
            $note = "Normal bill generated. No carry balance.";
        }

        // Insert new bill
        $reference_id = generateReferenceID();
        $due_date = date('Y-m-d', strtotime('+3 days'));

        $insert = mysqli_query($con, "
            INSERT INTO bills (renter_id, unit_id, total_amount, reference_id, status, due_date, note)
            VALUES ('$renter_id', '$unit_id', '$adjusted_total', '$reference_id', 'open', '$due_date', '$note')
        ");

        // Update renter's carry_balance
        mysqli_query($con, "
            UPDATE renters 
            SET carry_balance = '$carry_balance'
            WHERE id = '$renter_id'
        ");

        // Send SMS notification
        if ($insert) {
            if (strpos($note, 'Overpayment') !== false) {
                $msg = "Hello $renter_name, your overpayment has been applied to your new bill. ₱" . number_format($adjusted_total,2) . " due.";
            } elseif (strpos($note, 'Unpaid') !== false) {
                $msg = "Hello $renter_name, your unpaid balance was added to your new bill. ₱" . number_format($adjusted_total,2) . " due.";
            } else {
                $msg = "Hello $renter_name, your new bill is ₱" . number_format($adjusted_total,2) . ". Due: " . date('M d, Y', strtotime($due_date));
            }
            sendSMS($contact_number, $msg);
            $_SESSION['message'] = "Bill generated successfully! $note";
        } else {
            $_SESSION['message'] = "Error generating bill.";
        }

        header("Location: billing.php");
        exit();
    } else {
        $_SESSION['message'] = "No active renter found for this unit.";
        header("Location: billing.php");
        exit();
    }
}
?>
