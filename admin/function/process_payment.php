<?php
// function/process_payment.php
session_start();
require_once __DIR__ . '../config/dbcon.php';

header('Content-Type: application/json; charset=utf-8');

// Helper to return JSON and exit
function json_out($arr) {
    echo json_encode($arr);
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Read & sanitize inputs
$bill_id        = intval($_POST['bill_id'] ?? 0);
$renter_id      = intval($_POST['renter_id'] ?? 0);
$amount_paid    = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0.0;
$payment_method = trim($_POST['payment_method'] ?? 'cash');
$remarks        = trim($_POST['remarks'] ?? '');

if ($bill_id <= 0 || $renter_id <= 0 || $amount_paid <= 0) {
    json_out(['status' => 'error', 'message' => 'Invalid payment data.']);
}

// Start transaction
mysqli_begin_transaction($con);

try {
    // 1) Fetch the current bill (for latest amount_paid / total_amount)
    $stmt = mysqli_prepare($con, "SELECT id, renter_id, total_amount, amount_paid, status, payment_date, reference_id, note FROM bills WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $bill_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $bill = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$bill) {
        mysqli_rollback($con);
        json_out(['status' => 'error', 'message' => 'Bill not found.']);
    }

    // Ensure renter_id matches bill (basic sanity)
    if (intval($bill['renter_id']) !== $renter_id) {
        // continue but warn (you may change to error)
        // For safety, we'll enforce match:
        mysqli_rollback($con);
        json_out(['status' => 'error', 'message' => 'Renter does not match bill.']);
    }

    $total_amount = round(floatval($bill['total_amount']), 2);
    $current_paid = round(floatval($bill['amount_paid']), 2);
    $current_balance = round($total_amount - $current_paid, 2);
    $new_paid_total = round($current_paid + $amount_paid, 2);

    // Build note fragment and variables for carry
    $note_parts = [];
    $carry_to_insert = 0.00;
    $carry_type = null;
    $carry_warning = null;

    // CASES
    if (abs($new_paid_total - $total_amount) < 0.01) {
        // Full payment
        $status = 'paid';
        $payment_status = 'paid';
        $note_parts[] = "Paid in full ₱" . number_format($amount_paid, 2);
    } elseif ($new_paid_total < $total_amount) {
        // Partial / underpayment -> create unpaid carry for remaining
        $status = 'partial';
        $payment_status = 'partial';
        $remaining = round($total_amount - $new_paid_total, 2);
        $note_parts[] = "Partial payment ₱" . number_format($amount_paid, 2) . " | Remaining ₱" . number_format($remaining, 2);

        $carry_to_insert = $remaining;
        $carry_type = 'unpaid';
    } else {
        // Overpayment: try to apply to next bills, leftover becomes carry overpaid
        $status = 'overpaid';
        $payment_status = 'overpaid';
        $over = round($new_paid_total - $total_amount, 2);
        $note_parts[] = "Overpaid ₱" . number_format($over, 2) . " (will be applied to next bill(s))";

        // Apply overpayment to next bills
        $remaining_over = $over;

        // Select next open/partial bills for this renter (oldest first), excluding current bill
        $stmt = mysqli_prepare($con, "
            SELECT id, total_amount, amount_paid
            FROM bills
            WHERE renter_id = ? AND id <> ? AND (status IN ('open','partial') OR payment_status IN ('open','partial'))
            ORDER BY id ASC
        ");
        mysqli_stmt_bind_param($stmt, 'ii', $renter_id, $bill_id);
        mysqli_stmt_execute($stmt);
        $resNext = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if ($resNext) {
            while ($next = mysqli_fetch_assoc($resNext)) {
                if ($remaining_over <= 0) break;

                $next_id = intval($next['id']);
                $next_total = round(floatval($next['total_amount']), 2);
                $next_paid = round(floatval($next['amount_paid']), 2);
                $next_need = round($next_total - $next_paid, 2);

                if ($next_need <= 0) continue;

                $apply_amt = ($remaining_over >= $next_need) ? $next_need : $remaining_over;
                $apply_amt = round($apply_amt, 2);

                // Update next bill amount_paid and set status/payment_status accordingly
                $stmtUpd = mysqli_prepare($con, "UPDATE bills SET amount_paid = amount_paid + ?, payment_date = NOW() WHERE id = ?");
                mysqli_stmt_bind_param($stmtUpd, 'di', $apply_amt, $next_id);
                mysqli_stmt_execute($stmtUpd);
                mysqli_stmt_close($stmtUpd);

                // Recalculate if fully paid now
                $stmtChk = mysqli_prepare($con, "SELECT total_amount, amount_paid FROM bills WHERE id = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtChk, 'i', $next_id);
                mysqli_stmt_execute($stmtChk);
                $resChk = mysqli_stmt_get_result($stmtChk);
                $nb = mysqli_fetch_assoc($resChk);
                mysqli_stmt_close($stmtChk);

                $new_next_paid = round(floatval($nb['amount_paid']), 2);
                $next_total_amount = round(floatval($nb['total_amount']), 2);

                if (abs($new_next_paid - $next_total_amount) < 0.01) {
                    $stmtSet = mysqli_prepare($con, "UPDATE bills SET status = 'paid', payment_status = 'paid' WHERE id = ?");
                    mysqli_stmt_bind_param($stmtSet, 'i', $next_id);
                    mysqli_stmt_execute($stmtSet);
                    mysqli_stmt_close($stmtSet);
                } else {
                    $stmtSet = mysqli_prepare($con, "UPDATE bills SET status = 'partial', payment_status = 'partial' WHERE id = ?");
                    mysqli_stmt_bind_param($stmtSet, 'i', $next_id);
                    mysqli_stmt_execute($stmtSet);
                    mysqli_stmt_close($stmtSet);
                }

                // Decrement remaining_over
                $remaining_over = round($remaining_over - $apply_amt, 2);
            }
        }

        // After trying to apply to next bills, whatever remains becomes carry overpaid
        if ($remaining_over > 0) {
            $carry_to_insert = $remaining_over;
            $carry_type = 'overpaid';
        }
    }

    // 2) Update current bill: amount_paid, status, payment_status, note, payment_date
    $note_str = implode(' | ', $note_parts);
    // Use prepared stmt
    $stmt = mysqli_prepare($con, "
        UPDATE bills
        SET amount_paid = amount_paid + ?, status = ?, payment_status = ?, note = CONCAT(IFNULL(note, ''), ' | ', ?), payment_date = NOW()
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'dsssi', $amount_paid, $status, $payment_status, $note_str, $bill_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        mysqli_rollback($con);
        json_out(['status' => 'error', 'message' => 'Failed to update bill: ' . mysqli_error($con)]);
    }

    // ✅ 6. APPLY CARRY BALANCES (Overpaid / Unpaid)

    $carry_result = mysqli_query($con, "SELECT * FROM create_balances 
        WHERE renter_id='$renter_id' AND applied_to_bill_id IS NULL");

    $carry_adjustment = 0;
    $carry_note = '';

    while ($carry = mysqli_fetch_assoc($carry_result)) {
        if ($carry['carry_type'] === 'overpaid') {
            // Overpaid balance → subtract from current bill
            $carry_adjustment -= $carry['amount'];
            $carry_note .= "Overpaid balance applied: -₱" . number_format($carry['amount'], 2) . "\\n";
        } elseif ($carry['carry_type'] === 'unpaid') {
            // Unpaid balance → add to current bill
            $carry_adjustment += $carry['amount'];
            $carry_note .= "Previous unpaid balance added: +₱" . number_format($carry['amount'], 2) . "\\n";
        }

        // Mark as applied to this bill
        mysqli_query($con, "UPDATE create_balances SET applied_to_bill_id='$bill_id' WHERE id='{$carry['id']}'");
    }

    // Update bill amount based on carry-over adjustments
    if ($carry_adjustment != 0) {
        $updated_amount = $total_amount + $carry_adjustment;

        mysqli_query($con, "UPDATE bills SET 
            amount_due='$updated_amount', 
            remarks = CONCAT(IFNULL(remarks, ''), '\\nCarry Notes: ', '$carry_note')
            WHERE id='$bill_id'");
    }

    // 3) Insert into payments table (generate reference)
    $ref_prefix = 'CASH-' . date('Ymd') . '-';
    $countRow = mysqli_query($con, "SELECT COUNT(*) AS c FROM payments WHERE DATE(payment_date) = CURDATE()");
    $countR = mysqli_fetch_assoc($countRow);
    $serial = intval($countR['c'] ?? 0) + 1;
    $reference_number = $ref_prefix . str_pad($serial, 4, '0', STR_PAD_LEFT);

    $stmt = mysqli_prepare($con, "
        INSERT INTO payments (bill_id, renter_id, payment_method, reference_number, amount_paid, remarks, status, payment_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    mysqli_stmt_bind_param($stmt, 'iissdss', $bill_id, $renter_id, $payment_method, $reference_number, $amount_paid, $remarks, $payment_status);
    $insOk = mysqli_stmt_execute($stmt);
    if (!$insOk) {
        mysqli_stmt_close($stmt);
        mysqli_rollback($con);
        json_out(['status' => 'error', 'message' => 'Failed to record payment: ' . mysqli_error($con)]);
    }
    $payment_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // 4) Insert create_balances if needed
    if ($carry_to_insert > 0 && !is_null($carry_type)) {
        $stmt = mysqli_prepare($con, "
            INSERT INTO create_balances (renter_id, bill_id, carry_type, amount, applied_to_bill_id)
            VALUES (?, ?, ?, ?, NULL)
        ");
        mysqli_stmt_bind_param($stmt, 'iisd', $renter_id, $bill_id, $carry_type, $carry_to_insert);
        $carryOk = mysqli_stmt_execute($stmt);
        if (!$carryOk) {
            // record carry error but don't rollback whole transaction; include warning in response
            $carry_warning = 'Failed to insert create_balances: ' . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }

    // Optional: call SMS sender if exists
    // get renter contact (if function uses it)
    $contact_phone = null;
    $stmt = mysqli_prepare($con, "SELECT contacts, first_name, last_name FROM renters WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $renter_id);
    mysqli_stmt_execute($stmt);
    $resR = mysqli_stmt_get_result($stmt);
    $rrow = mysqli_fetch_assoc($resR);
    mysqli_stmt_close($stmt);
    if ($rrow) {
        $contact_phone = $rrow['contacts'] ?? null;
        $rname = trim(($rrow['first_name'] ?? '') . ' ' . ($rrow['last_name'] ?? ''));
    } else {
        $rname = '';
    }

    if (function_exists('send_sms_direct') && !empty($contact_phone)) {
        // Be careful: send_sms_direct should be non-blocking or have its own error handling
        $sms = "Payment received. Ref: {$reference_number}. Amount: ₱" . number_format($amount_paid, 2) . ". Status: {$payment_status}.";
        // Optional: suppress exceptions
        try {
            @send_sms_direct($contact_phone, $sms);
        } catch (Exception $e) {
            // ignore SMS failure
        }
    }

    // Commit transaction
    mysqli_commit($con);

    // Optionally set session alert
    $_SESSION['alert_type'] = 'success';
    $_SESSION['alert_msg'] = "Payment recorded (Ref: {$reference_number})";

    // Build response
    $response = [
        'status' => 'success',
        'message' => "Payment recorded (Ref: {$reference_number})",
        'payment_id' => $payment_id,
        'reference_number' => $reference_number
    ];
    if (!empty($carry_warning)) $response['carry_warning'] = $carry_warning;

    json_out($response);
}
catch (Exception $ex) {
    mysqli_rollback($con);
    json_out(['status' => 'error', 'message' => 'Exception: ' . $ex->getMessage()]);
}
