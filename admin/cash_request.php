<?php
session_start();
require('config/dbcon.php'); // adjust path if needed
require('includes/header.php');
date_default_timezone_set('Asia/Manila');

// Fetch all requests
$query = "SELECT cr.*, r.first_name, r.last_name 
          FROM cash_requests cr 
          JOIN renters r ON cr.renter_id = r.id 
          ORDER BY cr.requested_at DESC";
$result = mysqli_query($con, $query);
?>
<div class="container mt-4">
    <h3>Cash Payment Requests</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Renter</th>
                <th>Bill IDs</th>
                <th>Amounts</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['reference_number']; ?></td>
                <td><?= $row['first_name'].' '.$row['last_name']; ?></td>
                <td><?= $row['bill_ids']; ?></td>
                <td><?= $row['amounts']; ?></td>
                <td>
                    <?php if($row['status'] == 'pending'): ?>
                        <span class="badge bg-warning">Pending</span>
                    <?php elseif($row['status'] == 'approved'): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php endif; ?>
                </td>
                <td><?= $row['requested_at']; ?></td>
                <td>
                    <?php if($row['status']=='pending'): ?>
                        <form action="update_cash_request.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <button name="approve" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form action="update_cash_request.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <button name="reject" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('includes/footer.php'); ?>
