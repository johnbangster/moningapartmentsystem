<?php
include('../config/dbcon.php');

// Safely get POST data
$bill_id = intval($_POST['bill_id'] ?? 0);
$method = $_POST['payment_method'] ?? '';
$amount_paid = floatval($_POST['amount_paid'] ?? 0);
$remarks = $_POST['remarks'] ?? '';

// Validate required fields
if ($bill_id <= 0 || $amount_paid <= 0) {
    die("Invalid bill ID or amount.");
}

// Fetch current bill info safely
$bill_q = mysqli_query($con, "SELECT * FROM bills WHERE id='$bill_id' LIMIT 1");
if (!$bill_q || mysqli_num_rows($bill_q) == 0) {
    die("Bill not found or database error: " . mysqli_error($con));
}
$bill = mysqli_fetch_assoc($bill_q);
$amount_due = floatval($bill['amount_due']);

// Compare payments
if ($amount_paid < $amount_due) {
    $new_status = 'partial';
    $note = "Partial payment made. Remaining balance will be added to next bill.";
    $carry_over = $amount_due - $amount_paid;
} elseif ($amount_paid > $amount_due) {
    $new_status = 'overpaid';
    $overpaid = $amount_paid - $amount_due;
    $note = "Overpayment detected. ₱" . number_format($overpaid,2) . " will be credited next month.";
    $carry_over = -$overpaid; // negative for deduction
} else {
    $new_status = 'paid';
    $note = "Fully paid.";
    $carry_over = 0;
}

// Insert payment record
mysqli_query($con, "INSERT INTO payments (bill_id, payment_method, amount_paid, remarks, created_at)
VALUES ('$bill_id','$method','$amount_paid','$remarks',NOW())");

// Update bill
mysqli_query($con, "UPDATE bills SET status='$new_status', note='$note', updated_at=NOW() WHERE id='$bill_id'");

// Handle carry-over (store in renter’s credit/debit)
$renter_id = intval($bill['renter_id']);
mysqli_query($con, "UPDATE renters SET balance = balance + $carry_over WHERE id='$renter_id'");

echo "Payment recorded successfully.";
?>
