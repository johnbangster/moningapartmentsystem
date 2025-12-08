<?php
require('config/dbcon.php');
require('authentication.php');

$bill_id = intval($_GET['id'] ?? 0);
if($bill_id <= 0) exit;

$query = mysqli_query($con, "
    SELECT b.*, r.first_name, r.last_name, COALESCE(SUM(p.amount),0) AS total_paid, p.remarks
    FROM bills b
    INNER JOIN renters r ON b.renter_id = r.id
    LEFT JOIN payments p ON p.bill_id = b.id
    WHERE b.id = $bill_id
    GROUP BY b.id
");
if(mysqli_num_rows($query)==0) exit;

$row = mysqli_fetch_assoc($query);
$fullname = ucwords($row['first_name'].' '.$row['last_name']);
$total_amount = floatval($row['total_amount']);
$total_paid = floatval($row['total_paid']);
$carry_balance = floatval($row['carry_balance']);
$balance = $total_amount - $total_paid;

if($balance < 0) $statusBadge = '<span class="badge bg-success">Overpaid</span>';
elseif($balance == 0) $statusBadge = '<span class="badge bg-primary">Paid</span>';
else $statusBadge = '<span class="badge bg-info text-dark">Partial</span>';

$due_date = date('M d, Y', strtotime($row['due_date']));
$payment_date = !empty($row['payment_date']) ? date('M d, Y', strtotime($row['payment_date'])) : '—';
?>
<tr id="billRow<?= $bill_id ?>">
    <td><strong><?= htmlspecialchars($row['reference_id']); ?></strong></td>
    <td><?= htmlspecialchars($fullname); ?></td>
    <td><?= number_format($total_amount,2) ?></td>
    <td><?= number_format($total_paid,2) ?></td>
    <td class="<?= $balance < 0 ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
        <?= number_format($balance,2) ?>
    </td>
    <td class="<?= $carry_balance < 0 ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
        <?= number_format($carry_balance,2) ?>
    </td>
    <td class="text-center"><?= $statusBadge ?></td>
    <td><?= $due_date ?></td>
    <td><?= $payment_date ?></td>
    <td><strong><?= htmlspecialchars($row['remarks']) ?></strong></td>
    <td>
        <?php if($balance>0): ?>
            <button class="btn btn-sm btn-warning cashBtn" data-id="<?= $bill_id ?>">
                <i class='fas fa-money-bill-wave'></i>
            </button>
        <?php else: ?>
            <a href="generate_receipt.php?id=<?= $bill_id ?>" target="_blank" class="btn btn-sm btn-success">
                <i class="fa-solid fa-receipt fa-lg"></i>
            </a>
        <?php endif; ?>
    </td>
</tr>
