<?php
session_start();
require('config/dbcon.php');
date_default_timezone_set('Asia/Manila');

if (isset($_POST['cash_pay'])) {

    $bill_id     = intval($_POST['bill_id']);
    $renter_id   = intval($_POST['renter_id']);
    $amount_paid = floatval($_POST['amount_paid']);
    $remarks     = mysqli_real_escape_string($con, $_POST['remarks']);
    $admin_name  = $_SESSION['auth_user']['username'] ?? 'admin';

    // Validate
    if ($bill_id <= 0 || $renter_id <= 0 || $amount_paid <= 0) {
        echo "<script>alert('Invalid input.'); window.history.back();</script>";
        exit;
    }

    // Generate reference number
    $ref_prefix = "CASH-" . date("Ymd") . "-";
    $row = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as count FROM payments WHERE DATE(payment_date) = CURDATE()"));
    $reference_number = $ref_prefix . str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);

    // Fetch bill info
    $bill = mysqli_fetch_assoc(mysqli_query($con, "SELECT total_amount, amount_paid, carry_balance, status FROM bills WHERE id='$bill_id'"));
    if (!$bill) {
        echo "<script>alert('Bill not found.'); window.history.back();</script>";
        exit;
    }

    $prev_paid  = floatval($bill['amount_paid']);
    $prev_carry = floatval($bill['carry_balance']);
    $total_due  = floatval($bill['total_amount']);

    // Compute new totals
    $total_paid_so_far = $prev_paid + $amount_paid;
    $remaining_balance = $total_due - $total_paid_so_far;
    $status = 'partial';
    $carry_balance = 0;

    if ($remaining_balance <= 0) {
        if ($remaining_balance < 0) {
            $status = 'overpaid';
            $carry_balance = abs($remaining_balance);
        } else {
            $status = 'paid';
        }
        $remaining_balance = 0;
    }

    // Insert payment
    $insert = mysqli_query($con, "INSERT INTO payments 
        (renter_id, bill_id, amount, payment_type, payment_date, issued_by, remarks, reference_number, status, carry_balance) 
        VALUES ('$renter_id', '$bill_id', '$amount_paid', 'cash', NOW(), '$admin_name', '$remarks', '$reference_number', '$status', '$carry_balance')");

    if ($insert) {
        $payment_id = mysqli_insert_id($con);

        // Update bill
        mysqli_query($con, "UPDATE bills SET 
            amount_paid = '$total_paid_so_far',
            carry_balance = '$carry_balance',
            balance = '$remaining_balance',
            status = '$status'
            WHERE id='$bill_id'");

        $message = "Payment of ₱" . number_format($amount_paid, 2) . " received for Bill #$bill_id (Ref: $reference_number)";
        $created_at = date('Y-m-d H:i:s');

        // Notify admins/employees
        $users = mysqli_query($con, "SELECT id FROM users WHERE role IN ('admin', 'employee')");
        while ($u = mysqli_fetch_assoc($users)) {
            mysqli_query($con, "INSERT INTO notifications (user_id, message, type, created_at, is_read) 
                                VALUES ('{$u['id']}', '$message', 'payment', '$created_at', 0)");
        }

        // Notify renter
        mysqli_query($con, "INSERT INTO notifications (user_id, message, type, created_at, is_read) 
                            VALUES ('$renter_id', '$message', 'payment', '$created_at', 0)");

        // Show native JS success alert and redirect
        echo "<script>
            alert('Payment Successful!\\nReference Number: $reference_number');
            window.location.href='billing.php';
        </script>";
        exit;

    } else {
        $error_msg = mysqli_error($con);
        echo "<script>
            alert('Payment Failed!\\nError: $error_msg');
            window.history.back();
        </script>";
        exit;
    }
}

// LOAD BILL INFO FOR PAGE DISPLAY
$bill_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($bill_id > 0) {
    $bill = mysqli_fetch_assoc(mysqli_query($con,"SELECT b.*, r.first_name, r.last_name, (b.total_amount - b.amount_paid) AS balance 
        FROM bills b 
        JOIN renters r ON b.renter_id = r.id 
        WHERE b.id='$bill_id' LIMIT 1"));
    if (!$bill) {
        echo "<script>alert('Bill not found!'); location.href='billing.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid or missing Bill ID!'); location.href='billing.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cash Payment</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>Cash Payment</h3>
    <form id="paymentForm" method="post">
        <input type="hidden" name="cash_pay" value="1">
        <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
        <input type="hidden" name="renter_id" value="<?= $bill['renter_id'] ?>">

        <div class="mb-3">
            <label>Renter</label>
            <input type="text" class="form-control" value="<?= $bill['first_name'].' '.$bill['last_name'] ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Total Amount</label>
            <input type="text" class="form-control" value="₱<?= number_format($bill['total_amount'], 2) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Balance</label>
            <input type="text" class="form-control" value="₱<?= number_format($bill['balance'], 2) ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Amount to Pay</label>
            <input type="number" name="amount_paid" class="form-control" required step="0.01">
        </div>
        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control" placeholder="Optional"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit Payment</button>
        <a href="billing.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    $(document).ready(function () {

    $('#paymentForm').on('submit', function (e) {
        e.preventDefault();

        let amount = $('input[name="amount_paid"]').val();
        if (!confirm("Are you sure you want to proceed with payment of ₱" + parseFloat(amount).toFixed(2) + "?")) {
            return; // Cancelled
        }

        $.ajax({
            url: "cash_topay.php",
            method: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: "json",

            success: function (res) {
                if (res.status === "success") {
                    alert("Payment Successful!\nReference Number: " + res.reference_number);

                    // Load receipt modal automatically
                    $.post('function/get_receipt.php', {payment_id: res.payment_id}, function(data) {
                        $('#receiptData').html(data);
                        $('#receiptModal').modal('show');

                        // Redirect after modal closes
                        $('#receiptModal').on('hidden.bs.modal', function () {
                            window.location.href = 'billing.php';
                        });
                    });

                } else {
                    alert("Error: " + res.message);
                }
            },

            error: function () {
                alert("Error: Request failed.");
            }
        });

    });

});

// Print function remains the same
function printReceipt() {
    let printContents = document.getElementById('receiptContent').innerHTML;
    let win = window.open('', '', 'width=900,height=650');
    win.document.write('<html><head><title>Receipt</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">');
    win.document.write('</head><body>' + printContents + '</body></html>');
    win.document.close();
    win.print();
}
    // document.getElementById('paymentForm').addEventListener('submit', function(e) {
    //     // Native confirm dialog before submitting
    //     let amount = document.querySelector('input[name="amount_paid"]').value;
    //     if (!confirm('Are you sure you want to proceed with payment of ₱' + parseFloat(amount).toFixed(2) + '?')) {
    //         e.preventDefault(); // Cancel form submission
    //     }
    // });
</script>

</body>
</html>

