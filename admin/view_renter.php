<?php
session_start();
include('config/dbcon.php');
require ('includes/header.php');


$renter_id = isset($_GET['renter_id']) ? $_GET['renter_id'] : null;
$data = null;

if ($renter_id) {
    $query = "
        SELECT 
            r.*,
            u.name AS unit_name,
            u.description AS unit_description,
            u.area,
            u.price,
            u.status AS unit_status,
            u.adult,
            u.children,
            ut.type_name AS unit_type_name,
            ut.description AS unit_type_description,
            b.name AS branch_name,
            b.address AS branch_address,
            a.id AS agreement_id,
            a.term_months,
            a.monthly_rent,
            a.start_date,
            a.end_date
        FROM renters r
        LEFT JOIN units u ON r.unit_id = u.id
        LEFT JOIN unit_type ut ON u.unit_type_id = ut.id
        LEFT JOIN branch b ON u.branch_id = b.id
        LEFT JOIN rental_agreements a ON a.renter_id = r.id
        WHERE r.id = '$renter_id'
        LIMIT 1
    ";
    $run = mysqli_query($con, $query);

    if ($run && mysqli_num_rows($run) > 0) {
        $data = mysqli_fetch_assoc($run);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Renter Information</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
<div class="container my-5">

<?php if (!$renter_id): ?>
    <div class="alert alert-warning text-center">
        <strong>No renter selected.</strong><br>
        Add renter_id in the URL:<br>
        <code>?renter_id=1</code>
    </div>

<?php elseif (!$data): ?>
    <div class="alert alert-danger text-center">
        <strong>No data found for renter ID:</strong> <?= htmlspecialchars($renter_id); ?>
    </div>

<?php else: ?>

    <div class="mb-4">
        <h3 class="fw-bold">Renter Profile</h3>
        <p class="text-muted">Complete renter information</p>
    </div>

    <div class="row g-4">

        <!-- Renter Info -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-user me-2"></i>Renter Information
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= $data['first_name'].' '.$data['middle_name'].' '.$data['last_name']; ?></p>
                    <p><strong>Email:</strong> <?= $data['email']; ?></p>
                    <p><strong>Phone:</strong> <?= $data['contacts']; ?></p>
                </div>
            </div>
        </div>

        <!-- Unit Info -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fa fa-building me-2"></i>Unit Information
                </div>
                <div class="card-body">
                    <?php if ($data['unit_name']): ?>
                        <p><strong>Unit:</strong> <?= $data['unit_name']; ?></p>
                        <p><strong>Type:</strong> <?= $data['unit_type_name']; ?> (<?= $data['unit_type_description']; ?>)</p>
                        <p><strong>Description:</strong> <?= $data['unit_description'] ?? 'N/A'; ?></p>
                        <p><strong>Area:</strong> <?= $data['area']; ?> sqm</p>
                        <p><strong>Rent Price:</strong> ₱ <?= number_format($data['price'],2); ?></p>
                        <p><strong>Status:</strong> <?= $data['unit_status']; ?></p>
                        <p><strong>Capacity:</strong> Adults: <?= $data['adult']; ?>, Children: <?= $data['children']; ?></p>
                        <p><strong>Branch:</strong> <?= $data['branch_name'] ?? 'N/A'; ?></p>
                        <p><strong>Branch Address:</strong> <?= $data['branch_address'] ?? 'N/A'; ?></p>
                    <?php else: ?>
                        <div class="alert alert-warning">No Unit Assigned</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lease Info -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-file-signature me-2"></i>Lease Agreement
                </div>
                <div class="card-body">
                    <?php if ($data['agreement_id']): ?>
                        <p><strong>Term:</strong> <?= $data['term_months']; ?> months</p>
                        <p><strong>Monthly Rent:</strong> ₱ <?= number_format($data['monthly_rent'], 2); ?></p>
                        <p><strong>Start:</strong> <?= date('F d, Y', strtotime($data['start_date'])); ?></p>
                        <p><strong>End:</strong> <?= date('F d, Y', strtotime($data['end_date'])); ?></p>
                    <?php else: ?>
                        <div class="alert alert-warning">No Lease Agreement Found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ BILLS -->
    <div class="card shadow border-0 mt-5">
        <div class="card-header bg-warning">
            <i class="fa fa-receipt me-2"></i>Billing History
        </div>
        <div class="card-body p-0">
            <?php
            $billQuery = "SELECT reference_id, due_date, total_amount, status 
                          FROM bills WHERE renter_id = '$renter_id' ORDER BY due_date ASC";
            $bill_run = mysqli_query($con, $billQuery);
            ?>

            <?php if ($bill_run && mysqli_num_rows($bill_run) > 0): ?>
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Reference</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($bill = mysqli_fetch_assoc($bill_run)): ?>
                        <tr>
                            <td><?= $bill['reference_id']; ?></td>
                            <td><?= date('M d, Y', strtotime($bill['due_date'])); ?></td>
                            <td>₱<?= number_format($bill['total_amount'], 2); ?></td>
                            <td><?= strtoupper($bill['status']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-3 text-center">
                    <em>No billing records found.</em>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

</div>
</body>
</html>
