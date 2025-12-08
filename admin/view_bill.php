view_bill.php

<?php
session_start();
require('authentication.php');
require('config/dbcon.php');
require('includes/header.php');
// require('includes/message.php');

// Get bill ID from URL
$bill_id = intval($_GET['id'] ?? 0);
if ($bill_id <= 0) {
    echo "<script>alert('Invalid Bill ID!'); location.href='billing.php';</script>";
    exit();
}

// Fetch bill info + renter + unit
$bill_query = mysqli_query($con, "
    SELECT b.*,  r.first_name, r.last_name, r.email, r.contacts, u.name, b.generated_role 
    FROM bills b
    INNER JOIN renters r ON b.renter_id = r.id
    LEFT JOIN units u ON b.unit_id = u.id
    LEFT JOIN payments p ON p.bill_id = b.id
    WHERE b.id = $bill_id
    LIMIT 1
");

if (!$bill_query || mysqli_num_rows($bill_query) == 0) {
    echo "<script>alert('Bill not found!'); location.href='billing.php';</script>";
    exit();
}

$bill = mysqli_fetch_assoc($bill_query);

// Fetch addons
$addons_query = mysqli_query($con, "SELECT * FROM bill_addons WHERE bill_id = $bill_id");
$addons = [];
$total_addons = 0.00;
while ($a = mysqli_fetch_assoc($addons_query)) {
    $addons[] = $a;
    $total_addons += floatval($a['amount']);
}

// Compute totals
$total_amount = floatval($bill['total_amount']);

// Fetch actual total paid
$sum_payments_query = mysqli_query($con, "
    SELECT SUM(amount) AS total_paid 
    FROM payments 
    WHERE bill_id = $bill_id
");
$sum_result = mysqli_fetch_assoc($sum_payments_query);

$total_paid = floatval($sum_result['total_paid'] ?? 0);
$balance    = $total_amount - $total_paid;

// Status badge
if ($balance < 0) {
    $statusBadge = '<span class="badge bg-success">Overpaid</span>';
} elseif ($balance == 0) {
    $statusBadge = '<span class="badge bg-primary">Paid</span>';
} elseif ($total_paid > 0 && $total_paid < $total_amount) {
    $statusBadge = '<span class="badge bg-info text-dark">Partial</span>';
} else {
    $statusBadge = '<span class="badge bg-danger">Unpaid</span>';
}


// Fetch payments
$payments_query = mysqli_query($con, "
    SELECT * FROM payments WHERE bill_id = $bill_id ORDER BY payment_date ASC
");
$payments = [];
while ($p = mysqli_fetch_assoc($payments_query)) {
    $payments[] = $p;
}
?>

<div class="container mt-4">
    <h3>Bill Details</h3>

    <!-- Bill Info -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row mb-2">
                <!-- <div class="col-md-6">
                    <strong>Bill Reference:</strong> <?= htmlspecialchars($bill['reference_id']); ?><br>
                    <strong>Cash Reference(s):</strong>
                    <?php if ($payments): ?>
                        <?php foreach ($payments as $pay): ?>
                            <?= htmlspecialchars($pay['reference_number']); ?> (₱<?= number_format($pay['amount'],2) ?>)<br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        — 
                    <?php endif; ?>
                </div> -->
                <div class="col-md-6">
                    <strong>Renter:</strong> <?= ucwords($bill['first_name'] . ' ' . $bill['last_name']); ?><br>
                    <strong>Contacts:</strong> <?= htmlspecialchars($bill['contacts']); ?><br>
                    <strong>Unit:</strong> <?= htmlspecialchars($bill['name'] ?? '—'); ?>
                </div>
            </div>

            <!-- Bill + Addons Table -->
            <?php
            // Fetch unit price from units table
            $unit_price = 0.00;
            if (!empty($bill['unit_id'])) {
                $unit_res = mysqli_query($con, "SELECT price FROM units WHERE id = {$bill['unit_id']} LIMIT 1");
                if ($unit_res && mysqli_num_rows($unit_res) > 0) {
                    $unit_price = floatval(mysqli_fetch_assoc($unit_res)['price']);
                }
            }

            // Recompute total amount including addons
            $total_amount = $unit_price + $total_addons;
            ?>

            <!-- Bill + Addons Table -->
            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Item</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Main Bill (Unit Price)</td>
                        <td>₱<?= number_format($unit_price,2) ?></td>
                    </tr>
                    <?php foreach($addons as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['name']); ?></td>
                        <td>₱<?= number_format($a['amount'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th>Total Amount</th>
                        <th>₱<?= number_format($total_amount,2) ?></th>
                    </tr>
                    <tr>
                        <th>Total Paid</th>
                        <th>₱<?= number_format($total_paid,2) ?></th>
                    </tr>
                    <tr>
                        <th>Balance</th>
                        <th>₱<?= number_format($balance,2) ?></th>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <th><?= $statusBadge ?></th>
                    </tr>
                </tbody>
            </table>


            <a href="transaction.php" class="btn btn-secondary">Back</a>
            <?php if ($balance <= 0): ?>
                <a href="generate_receipt.php?id=<?= $bill_id ?>" target="_blank" class="btn btn-warning">Print Receipt</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header"><strong>Payment History</strong></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Amount Paid</th>
                        <th>Reference</th>
                        <th>Remarks</th>
                        <!-- <th>Issued By</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($payments)): ?>
                        <?php foreach($payments as $p): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($p['payment_date'])); ?></td>
                                <td><?= ucfirst($p['payment_type']); ?></td>
                                <td class="text-end">₱<?= number_format($p['amount'],2); ?></td>
                                <td><?= htmlspecialchars($p['reference_id'] ?? $p['reference_number']); ?></td>
                                <td><?= htmlspecialchars($p['remarks']); ?></td>
                                <!-- <td><?= htmlspecialchars($p['payer_name']); ?></td> -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No payments yet.</td></tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
require('includes/footer.php');
include('includes/scripts.php');
?>
