<?php
session_start();
require('../admin/config/dbcon.php');
date_default_timezone_set('Asia/Manila');

$data = json_decode(file_get_contents('php://input'), true);
$bill_ids = $data['bill_ids'] ?? [];

if (!$bill_ids) {
    echo json_encode(['success' => false, 'message' => 'No bills selected']);
    exit;
}

// Get renter_id from session user
$user_id = (int)($_SESSION['auth_user']['user_id'] ?? 0);
$stmt = mysqli_prepare($con, "SELECT id FROM renters WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $renter_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$renter_id) {
    echo json_encode(['success' => false, 'message' => 'Renter not found']);
    exit;
}

// Generate unique cash reference number
$reference_number = 'CASH-' . time();

mysqli_begin_transaction($con);
$success = true;

try {
    foreach ($bill_ids as $bill_id) {

        // Validate if bill belongs to user
        $stmt = mysqli_prepare($con, "SELECT id FROM bills WHERE id = ? AND renter_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $bill_id, $renter_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $bill = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$bill) {
            $success = false;
            break;
        }

        // Only update the status to "pending" / "awaiting_confirmation"
        $stmt = mysqli_prepare($con, "
            UPDATE bills 
            SET status = 'awaiting_confirmation', 
                reference_id = ?, 
                payment_date = NOW() 
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "si", $reference_number, $bill_id);
        if (!mysqli_stmt_execute($stmt)) {
            $success = false;
        }
        mysqli_stmt_close($stmt);
    }

    if ($success) {
        mysqli_commit($con);
        echo json_encode([
            'success' => true,
            'message' => 'Cash payment request sent successfully. Please wait for admin confirmation.',
            'reference_number' => $reference_number
        ]);
    } else {
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Failed to update bill status.']);
    }

} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
