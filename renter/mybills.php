<?php
session_start();
require ('../admin/config/dbcon.php');
require ('includes/header.php');

$renter_id = $_SESSION['auth_user']['renter_id'];
?>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        text-align: left;
        padding: 8px;
    }
</style>

<div class="container-fluid px-4">
    <h1 class="mt-2">Payment History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active"></li>
    </ol>
    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="table-responsive-md" style="height:250px; overflow-y: scroll;" id="eReceipt">
                    <table class="table table-hover border">
                        <thead class="sticky-top">
                            <tr class="bg-dark text-light">
                                <th>Reference ID</th>
                                <th>Paid Amount</th>
                                <th>Month</th>
                                <th>Bill Amount</th>
                                <th>Due Date</th>
                                <th>Payment Type</th>
                                <th>Date Paid</th>
                                <th>Remarks</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            //Fetch only paid or overpaid bills
                            $query = "SELECT 
                                    p.id AS payment_id,
                                    b.reference_id,
                                    b.remarks,
                                    p.amount AS paid_amount,
                                    b.billing_month,
                                    b.total_amount AS bill_amount,
                                    b.due_date,
                                    b.payment_date,
                                    p.payment_type,
                                    b.status,
                                    b.id AS bill_id
                                FROM payments p
                                JOIN bills b ON p.bill_id = b.id
                                WHERE p.renter_id = '$renter_id'
                                AND (b.status = 'paid' OR b.status = 'overpaid')
                                ORDER BY b.payment_date DESC";

                            $result = mysqli_query($con, $query) or die("Query Failed: " . mysqli_error($con));

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?= $row['reference_id']; ?></td>
                                        <td>₱<?= number_format($row['paid_amount'], 2); ?></td>
                                        <td><?= date('M', strtotime($row['due_date'])); ?></td>
                                        <td>₱<?= number_format($row['bill_amount'], 2); ?></td>
                                        <td><?= date('M d, Y', strtotime($row['due_date'])); ?></td>
                                        <td><?= $row['payment_type']; ?></td>
                                        <td><?= $row['payment_date']; ?></td>
                                        <td><?= $row['remarks']; ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'paid') { ?>
                                                <span class='badge bg-success'>Paid</span>
                                            <?php } elseif ($row['status'] == 'overpaid') { ?>
                                                <span class='badge bg-success'>Overpaid</span>
                                            <?php } else { ?>
                                                <span class='badge bg-secondary'><?= $row['status']; ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <!-- Only allow E-Receipt if fully paid or overpaid -->
                                            <a href="generate_receipt.php?bill_id=<?= $row['bill_id']; ?>" target="_blank" class="btn btn-sm btn-info">E-Receipt</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='10' class='text-center'>No paid payments found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
