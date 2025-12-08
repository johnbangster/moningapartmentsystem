
<?php
session_start();
    require ('../admin/config/dbcon.php');
    // require ('config/code.php');
    require ('includes/header.php');
    $renter_id = $_SESSION['auth_user']['user_id'];
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">PAID</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active"></li>
    </ol>
    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
            <div class="text-end mb-4">
                <a href='index.php' class='btn btn-sm btn-danger'>BACK</a>
            </div> 
            <div class="table-responsive-md" style="height:250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead class="sticky-top">
                            <tr class="bg-dark text-light">
                            <th scope="col">Reference ID</th>
                            <th scope="col">Paid Amount</th>
                            <th scope="col">Month</th>
                            <th scope="col">Bill Amount</th>
                            <th scope="col">Due Date</th>
                            <th scope="col">Payment Type</th>
                            <th scope="col">Date Paid</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                            </tr>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query = "SELECT 
                                b.reference_id,
                                p.amount AS paid_amount,
                                b.billing_month,
                                b.total_amount AS bill_amount,
                                b.due_date,
                                p.payment_type,
                                p.payment_date,
                                b.status
                                FROM payments p
                                JOIN bills b ON p.bill_id = b.id
                                WHERE p.renter_id = $renter_id
                                ORDER BY p.payment_date DESC";

                                $result = mysqli_query($con, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                                <td>{$row['reference_id']}</td>
                                                <td>₱" . number_format($row['paid_amount'], 2) . "</td>
                                                <td>". date('M', strtotime($row['due_date'])) ."</td>
                                                <td>₱" . number_format($row['bill_amount'], 2) . "</td>
                                                <td>". date('M, d, Y', strtotime($row['due_date']))."</td>
                                                <td>{$row['payment_type']}</td>
                                                <td>{$row['payment_date']}</td>
                                                <td><span class='badge bg-" . ($row['status'] == 'paid' ? 'success' : 'danger') . "'>{$row['status']}</span></td>
                                                <td>
                                                    <a href='print_invoice.php?ref={$row['reference_id']}' class='btn btn-sm btn-primary'>View</a>
                                                    <a href='print_invoice.php?ref={$row['reference_id']}' class='btn btn-sm btn-warning'>Invoice</a>
                                                    <a href='print_invoice.php?ref={$row['reference_id']}' class='btn btn-sm btn-info'>Receipt</a>

                                                </td>
                                            </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center'>No payments found.</td></tr>";
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





