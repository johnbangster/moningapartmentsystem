<?php
session_start();
include('authentication.php');
require_once('config/code.php');

//  Allow only Admin or Super Admin
if (!isset($_SESSION['auth']) || 
    !in_array($_SESSION['auth_role'], ['admin', 'superadmin'])) {
    $_SESSION['alert_type'] = 'error';
    $_SESSION['alert_msg'] = 'Access Denied!';
    header("Location: index.php"); // Redirect to dashboard or login
    exit(0);
}

require('includes/header.php');



// Fetch all payments with 'pending' status and payment_type='cash'
$payments_q = mysqli_query($con, "
    SELECT p.*, r.first_name, r.last_name, b.reference_id, b.total_amount, b.balance
    FROM payments p
    JOIN renters r ON p.renter_id = r.id
    JOIN bills b ON p.bill_id = b.id
    WHERE p.payment_type='cash' AND p.status='pending'
    ORDER BY p.payment_date ASC
");
?>

<div class="container mt-4">
    <h2>Pending Cash Payments</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th>#</th>
                <th>Renter</th>
                <th>Bill Reference</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Payment Date</th>
                <th>Remarks</th>
                <th>Reference #</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($payments_q)>0): $i=1; ?>
                <?php while($p = mysqli_fetch_assoc($payments_q)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></td>
                        <td><?= htmlspecialchars($p['reference_id']) ?></td>
                        <td>₱<?= number_format($p['amount_paid'],2) ?></td>
                        <td>₱<?= number_format($p['balance'],2) ?></td>
                        <td><?= date('M-d-Y H:i', strtotime($p['payment_date'])) ?></td>
                        <td><?= htmlspecialchars($p['remarks']) ?></td>
                        <td><?= htmlspecialchars($p['reference_number']) ?></td>
                        <td class="text-center">
                            <button class="btn btn-success btn-sm confirmBtn" data-id="<?= $p['id'] ?>">Confirm</button>
                            <button class="btn btn-danger btn-sm rejectBtn" data-id="<?= $p['id'] ?>">Reject</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">No pending cash payments</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.confirmBtn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const paymentId = btn.dataset.id;
        Swal.fire({
            title: 'Confirm this payment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm'
        }).then(res=>{
            if(res.isConfirmed){
                fetch('process_cash_payment.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({payment_id: paymentId, action:'confirm'})
                })
                .then(r=>r.json())
                .then(resp=>{
                    if(resp.success){
                        Swal.fire('Confirmed!', resp.message, 'success').then(()=> location.reload());
                    }else{
                        Swal.fire('Error', resp.message,'error');
                    }
                });
            }
        });
    });
});

document.querySelectorAll('.rejectBtn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        const paymentId = btn.dataset.id;
        Swal.fire({
            title: 'Reject this payment?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject'
        }).then(res=>{
            if(res.isConfirmed){
                fetch('process_cash_payment.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({payment_id: paymentId, action:'reject'})
                })
                .then(r=>r.json())
                .then(resp=>{
                    if(resp.success){
                        Swal.fire('Rejected!', resp.message, 'success').then(()=> location.reload());
                    }else{
                        Swal.fire('Error', resp.message,'error');
                    }
                });
            }
        });
    });
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
