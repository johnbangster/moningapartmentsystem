<?php
session_start();
require 'config/dbcon.php';

// Check admin access
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true || $_SESSION['auth_role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Fetch cash reports
$sql = "SELECT 
            cr.id AS cash_id,
            cr.amount_paid,
            cr.payment_date,
            cr.notes,
            cr.verified,
            u.first_name AS emp_first, 
            u.last_name AS emp_last,
            r.first_name AS renter_first, 
            r.last_name AS renter_last,
            b.billing_month,
            b.reference_id
        FROM cash_reports cr
        INNER JOIN users u ON cr.employee_id = u.id
        INNER JOIN renters r ON cr.renter_id = r.id
        LEFT JOIN bills b ON cr.bill_id = b.id
        ORDER BY cr.created_at DESC";
$res = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Cash Reports</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .header { text-align: center; }
    .header img { width: 100px; }
    .header h2, .header p { margin: 5px; }
</style>
</head>
<body>

<div class="header">
    <img src="images/logo.png" alt="Logo">
    <h2>Monings Rental Services</h2>
    <p>1438-B M.J.Cueanco Avenue, Brgy Mabolo, Cebu City</p>
    <p>TIN: 123-456-789</p>
    <h3>Employee Cash Payment Reports</h3>
</div>

<table>
    <thead>
        <tr>
            <!-- <th>Cash ID</th> -->
            <th>Employee</th>
            <th>Renter</th>
            <th>Amount Paid (PHP)</th>
            <th>Payment Date</th>
            <th>Billing Month</th>
            <th>Bill Ref</th>
            <th>Verified</th>
            <th>Notes</th>
        </tr>
    </thead>
    <tbody>
        <?php if(mysqli_num_rows($res) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <!-- <td><?= $row['cash_id'] ?></td> -->
                    <td><?= htmlspecialchars($row['emp_first'].' '.$row['emp_last']) ?></td>
                    <td><?= htmlspecialchars($row['renter_first'].' '.$row['renter_last']) ?></td>
                    <td><?= number_format($row['amount_paid'], 2) ?></td>
                    <td><?= $row['payment_date'] ?></td>
                    <td><?= $row['billing_month'] ?: 'N/A' ?></td>
                    <td><?= $row['reference_id'] ?: 'N/A' ?></td>
                    <td><?= $row['verified'] == 1 ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;">No cash reports found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>
