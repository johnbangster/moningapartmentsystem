<?php
session_start();
require('config/dbcon.php');

// Only allow admins (adjust this check depending on your auth system)
if (!isset($_SESSION['auth_role']) || $_SESSION['auth_role'] != 'admin') {
    die("Access denied");
}

// Handle Approve/Reject actions
if (isset($_POST['action'], $_POST['payment_id'])) {
    $payment_id = (int)$_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Approve cash payment
        $update_payment = mysqli_query($con, "UPDATE payments SET status='confirmed' WHERE id=$payment_id");

        if ($update_payment) {
            // Also mark the bill as paid if fully covered
            $q = mysqli_query($con, "SELECT bill_id, amount FROM payments WHERE id=$payment_id");
            $payment = mysqli_fetch_assoc($q);

            if ($payment) {
                $bill_id = $payment['bill_id'];

                // get bill total
                $bq = mysqli_query($con, "SELECT total_amount FROM bills WHERE id=$bill_id");
                $bill = mysqli_fetch_assoc($bq);

                if ($bill && $payment['amount'] >= $bill['total_amount']) {
                    mysqli_query($con, "UPDATE bills SET status='paid', payment_date=NOW() WHERE id=$bill_id");
                } else {
                    mysqli_query($con, "UPDATE bills SET status='open' WHERE id=$bill_id"); // partial
                }
            }
        }

    } elseif ($action === 'reject') {
        // Reject payment
        $update_payment = mysqli_query($con, "UPDATE payments SET status='rejected' WHERE id=$payment_id");

        if ($update_payment) {
            // Reopen the bill
            $q = mysqli_query($con, "SELECT bill_id FROM payments WHERE id=$payment_id");
            $payment = mysqli_fetch_assoc($q);
            if ($payment) {
                $bill_id = $payment['bill_id'];
                mysqli_query($con, "UPDATE bills SET status='open' WHERE id=$bill_id");
            }
        }
    }

    header("Location: confirm_cash.php");
    exit;
}

// Fetch pending cash payments
$result = mysqli_query($con, "
    SELECT p.id AS payment_id, p.amount, p.payment_date, b.id AS bill_id, r.first_name, r.last_name
    FROM payments p
    JOIN bills b ON p.bill_id = b.id
    JOIN renters r ON p.renter_id = r.id
    WHERE p.payment_type='cash' AND p.status='pending'
    ORDER BY p.payment_date ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Cash Payments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Pending Cash Payments</h2>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Renter</th>
                    <th>Bill ID</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td>#<?= $row['bill_id'] ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td><?= $row['payment_date'] ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No pending cash payments.</div>
    <?php endif; ?>
</body>
</html>
