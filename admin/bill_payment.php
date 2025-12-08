<?php
include('authentication.php');
require('config/dbcon.php');
include('includes/header.php');

// Fetch current bill
$bill_id = $_GET['id'];
$query = mysqli_query($con, "SELECT b.*, r.first_name, r.last_name, u.name 
                             FROM bills b 
                             JOIN renters r ON b.renter_id = r.id 
                             JOIN units u ON b.unit_id = u.id 
                             WHERE b.id='$bill_id'");

if (mysqli_num_rows($query) == 0) {
    echo "<div class='alert alert-danger'>Bill not found!</div>";
    exit;
}

$bill = mysqli_fetch_assoc($query);
$addons = json_decode($bill['addon_total'], true);
$reference_no = 'INV-' . date('Ym') . str_pad($bill['id'], 4, '0', STR_PAD_LEFT);

// Fetch payments
$payments = mysqli_query($con, "SELECT * FROM payments WHERE bill_id='$bill_id' ORDER BY payment_date ASC");
?>

<div class="container mt-4">
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bill Payment - Reference No: <?= $reference_no ?></h5>
            <span class="badge bg-<?= $bill['status'] == 'paid' ? 'success' : ($bill['status'] == 'partial' ? 'warning' : 'danger') ?>">
                <?= strtoupper($bill['status']) ?>
            </span>
        </div>
        <div class="card-body">
            <p><strong>Renter:</strong> <?= $bill['first_name'] . ' ' . $bill['last_name'] ?></p>
            <p><strong>Unit:</strong> <?= $bill['name'] ?></p>
            <p><strong>Month Covered:</strong><?= date('F', timestamp: strtotime($bill['due_date'])) ?></p>
            <p><strong>Due Date:</strong> <?= date('F d, Y', timestamp: strtotime($bill['due_date'])) ?></p>

            <h6 class="mt-4">Add-ons</h6>
            <ul>
                <?php if (!empty($addons)) {
                    foreach ($addons as $a) {
                        echo "<li>{$a['name']} - ₱" . number_format($a['price'], 2) . "</li>";
                    }
                } else echo "<li>No add-ons</li>"; ?>
            </ul>

            <h4 class="mt-3 text-end">Total Amount: <span class="text-danger">₱<?= number_format($bill['total_amount'], 2) ?></span></h4>

            <form action="function/process_payment.php" method="POST" class="mt-4">
                <input type="hidden" name="bill_id" value="<?= $bill['id'] ?>">
                <input type="hidden" name="renter_id" value="<?= $bill['renter_id'] ?>">
                <input type="hidden" name="reference_no" value="<?= $reference_no ?>">

                <div class="form-group mb-3">
                    <label for="amount_paid">Enter Payment Amount</label>
                    <input type="number" step="0.01" class="form-control" name="amount_paid" id="amount_paid" required>
                </div>

                <div class="form-group mb-3">
                    <label for="payment_method">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">Select method</option>
                        <option value="cash">Cash</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="remarks">Remarks / Notes</label>
                    <textarea name="remarks" id="remarks" class="form-control" placeholder="Optional note..."></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment History Section -->
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Payment History</h5>
        </div>
        <div class="card-body table-responsive">
            <?php if (mysqli_num_rows($payments) > 0): ?>
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Remarks</th>
                            <th>Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $running_balance = $bill['amount_paid'];
                        while ($row = mysqli_fetch_assoc($payments)) {
                            $running_balance -= $row['amount_paid'];
                            if ($running_balance < 0) $running_balance = 0;

                            echo "<tr>
                                <td>" . date('M d, Y h:i A', strtotime($row['payment_date'])) . "</td>
                                <td>₱" . number_format($row['amount_paid'], 2) . "</td>
                                <td><span class='badge bg-info text-dark'>{$row['payment_method']}</span></td>
                                <td>{$row['remarks']}</td>
                                <td><strong>₱" . number_format($running_balance, 2) . "</strong></td>
                              </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No payment history available for this bill.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<form method="POST" action="function/add_payment.php">
  <input type="hidden" name="bill_id" value="BILL_ID">

  <div class="form-group">
    <label>Reference No:</label>
    <input type="text" name="payment_reference" value="PAY-<?=date('Y')?>-<?=rand(1000,9999)?>" readonly class="form-control">
  </div>

  <div class="form-group">
    <label>Payment Method</label>
    <select name="payment_method" class="form-control" required>
      <option value="cash">Cash</option>
      <option value="paypal">PayPal</option>
    </select>
  </div>

  <div class="form-group">
    <label>Amount Paid</label>
    <input type="number" step="0.01" name="amount_paid" class="form-control" required>
  </div>

  <div class="form-group">
    <label>Remarks</label>
    <textarea name="remarks" class="form-control" placeholder="Partial, full, or overpayment notes"></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Save Payment</button>
</form>


<?php include('includes/footer.php'); ?>
