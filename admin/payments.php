<?php
require('config/dbcon.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - PayPal Payments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

<h2 class="mb-4">Transactions</h2>

<table class="table table-bordered table-hover">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Renter Name</th>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>Payment Type</th>
            <th>Bill IDs</th>
            <th>Date Paid</th>
            <th>Receipt</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $query = mysqli_query($con, "
            SELECT p.*, r.first_name, r.last_name 
            FROM payments p 
            JOIN renters r ON p.renter_id = r.id 
            WHERE p.payment_type = 'paypal'
            ORDER BY p.payment_date DESC
        ");
        $i = 1;
        while ($row = mysqli_fetch_assoc($query)) {
            echo "<tr>
                <td>{$i}</td>
                <td>{$row['first_name']} {$row['last_name']}</td>
                <td>{$row['transaction_id']}</td>
                <td>₱{$row['amount']}</td>
                <td><span class='badge bg-success'>{$row['payment_type']}</span></td>
                <td>{$row['bill_id']}</td>
                <td>" . date('Y-m-d h:i A', strtotime($row['payment_date'])) . "</td>
                <td>
                    <a href='generate_receipt.php?payment_id={$row['id']}' class='btn btn-sm btn-outline-primary' target='_blank'>PDF</a>
                </td>
            </tr>";
            $i++;
        }
        ?>
    </tbody>
</table>

</body>
</html>
