<?php
require('../config/dbcon.php'); // adjust path as needed

if (!isset($_POST['payment_id'])) {
    echo "❌ Missing receipt ID!";
    exit;
}

$payment_id = intval($_POST['payment_id']);

$query = "
    SELECT 
        p.*,
        r.first_name, r.last_name,
        b.total_amount AS bill_total
    FROM payments p
    JOIN renters r ON p.renter_id = r.id
    JOIN bills b ON p.bill_id = b.id
    WHERE p.id = '$payment_id'
    LIMIT 1
";

$result = mysqli_query($con, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "❌ Receipt not found!";
    exit;
}

$data = mysqli_fetch_assoc($result);
?>

<div class="p-3">
    <h4 class="text-center">🏠 Official Receipt</h4>
    <hr>

    <p><strong>Renter Name:</strong> <?= $data['first_name'] . ' ' . $data['last_name']; ?></p>
    <p><strong>Reference No.:</strong> <?= $data['reference_number']; ?></p>
    <p><strong>Payment Method:</strong> <?= ucfirst($data['payment_method']); ?></p>
    <p><strong>Payment Date:</strong> <?= date("F d, Y h:i A", strtotime($data['payment_date'])); ?></p>

    <p><strong>Total Bill:</strong> ₱<?= number_format($data['bill_total'], 2); ?></p>
    <p><strong>Amount Paid:</strong> ₱<?= number_format($data['amount_paid'], 2); ?></p>
    <p><strong>Payment Status:</strong> <?= ucfirst($data['status']); ?></p>

    <?php if (!empty($data['carry_balance']) && $data['carry_balance'] != 0): ?>
        <p><strong>Carry Balance (Credit/Overpay):</strong> ₱<?= number_format($data['carry_balance'], 2); ?></p>
    <?php endif; ?>

    <?php if (!empty($data['remarks'])): ?>
        <p><strong>Remarks:</strong> <?= $data['remarks']; ?></p>
    <?php endif; ?>

    <p><strong>Issued By:</strong> <?= $data['issued_by']; ?></p>

    <hr>
    <p class="text-center">✅ Thank you for your payment!</p>
</div>
