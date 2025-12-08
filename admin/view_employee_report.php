<?php
session_start();
require 'config/dbcon.php';
require('includes/header.php');

// Check admin access
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true || $_SESSION['auth_role'] !== 'admin') {
    die("Access denied. Admins only.");
}

// Fetch cash reports + employee + renter + bill info
$sql = "SELECT 
            cr.id AS cash_id,
            cr.amount_paid,
            cr.payment_date,
            cr.receipt_path,
            cr.notes,
            cr.verified,
            cr.bill_id AS cr_bill_id,
            u.first_name AS emp_first, 
            u.last_name AS emp_last,
            r.first_name AS renter_first, 
            r.last_name AS renter_last,
            b.id AS bill_id,
            b.billing_month,
            b.reference_id
        FROM cash_reports cr
        INNER JOIN users u ON cr.employee_id = u.id
        INNER JOIN renters r ON cr.renter_id = r.id
        LEFT JOIN bills b ON cr.bill_id = b.id
        ORDER BY cr.created_at DESC ";
$res = mysqli_query($con, $sql);
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cash Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
</head>

<body class="bg-light">
<div class="container py-4">
    <h3 class="mb-4">Employee Cash Payment Reports</h3>
<div class="card">
    <div class="mb-3">
        <a href="export_cash_report.php" class="btn btn-danger">
            PDF
        </a>
        <a href="employee_excel_report.php" class="btn btn-success" target="_blank">
            EXCEL
        </a>

        <a href="print_cash_report.php" class="btn btn-info" target="_blank">
            PRINT
        </a>
    </div>
   
    <div class="card-body">
        <table id="cashTable" class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <!-- <th>Cash ID</th> -->
                    <th>Employee</th>
                    <th>Renter</th>
                    <th>Amount Paid (PHP)</th>
                    <th>Payment Date</th>
                    <th>Billing Month</th>
                    <th>Bill Reference</th>
                    <th>Receipt</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(mysqli_num_rows($res) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <!-- <td><?= $row['cash_id'] ?></td> -->
                        <td><?= htmlspecialchars($row['emp_first'] . ' ' . $row['emp_last']) ?></td>
                        <td><?= htmlspecialchars($row['renter_first'] . ' ' . $row['renter_last']) ?></td>
                        <td><?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= $row['payment_date'] ?></td>
                        <td><?= $row['billing_month'] ?: 'N/A' ?></td>
                        <td><?= $row['reference_id'] ?: 'N/A' ?></td>
                        <td>
                            <?php if(!empty($row['receipt_path']) && file_exists($row['receipt_path'])): 
                                $ext = strtolower(pathinfo($row['receipt_path'], PATHINFO_EXTENSION));
                                if(in_array($ext, ['jpg','jpeg','png'])): ?>
                                    <a href="<?= $row['receipt_path'] ?>" target="_blank">
                                        <img src="<?= $row['receipt_path'] ?>" width="80" alt="Receipt">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $row['receipt_path'] ?>" target="_blank">View PDF</a>
                                <?php endif; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['notes']) ?></td>
                        <td>
                            <?php if(!empty($row['bill_id']) && $row['verified'] == 0): ?>
                                <button class="btn btn-success btn-verify" 
                                        data-payment-id="<?= $row['cash_id'] ?>" 
                                        data-bill-id="<?= $row['bill_id'] ?>">
                                    Verify Payment
                                </button>
                            <?php elseif($row['verified'] == 1): ?>
                                <span class="text-success">Verified</span>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">No cash reports found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


</div>

<script>
$(document).ready(function() {

    // Initialize DataTable with export buttons
    $('#cashTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            
            {
                text: 'Reset',
                action: function ( e, dt, node, config ) {
                    dt.search('').columns().search('').draw();
                }
            }
        ],
        pageLength: 25,
        responsive: true
    });

    // Verify button click
    $('.btn-verify').on('click', function() {
        let payment_id = $(this).data('payment-id');
        let bill_id    = $(this).data('bill-id');

        if(!payment_id || !bill_id){
            Swal.fire('Error','Invalid payment or bill ID','error');
            return;
        }

        Swal.fire({
            title: 'Verify this payment?',
            text: "Once verified, it will update the bill and payment status.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, verify',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: 'verify_payment.php',
                    type: 'POST',
                    data: { payment_id: payment_id, bill_id: bill_id, verify_payment: true },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success'){
                            let msg = `Payment verified successfully!\n\n` +
                                      `Bill: #${response.reference_number}\n` +
                                      `Amount Paid: ₱${response.amount_paid}\n` +
                                      `Status: ${response.bill_status}`;
                            if(response.bill_status === 'overpaid' && response.carry_balance > 0){
                                msg += `\nCarry Balance: ₱${response.carry_balance}`;
                            }
                            Swal.fire('Success', msg, 'success');
                            $('[data-payment-id="'+payment_id+'"]').prop('disabled', true).text('Verified');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr){
                        Swal.fire('Error','AJAX request failed: '+xhr.responseText,'error');
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
