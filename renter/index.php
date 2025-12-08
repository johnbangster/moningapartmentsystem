<?php
session_start();
require('../admin/config/dbcon.php');
require('../admin/config/code.php');
require('includes/header.php');

// Ensure renter is logged in
if (!isset($_SESSION['auth_user']['user_id'])) {
    die("Access denied. Please log in as renter.");
}

$user_id = (int)$_SESSION['auth_user']['user_id'];

// Map user_id -> renter_id
$renter_id = null;
$stmt = mysqli_prepare($con, "SELECT id FROM renters WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $renter_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$renter_id) die("No renter profile found.");


// Total Bills

$stmt = mysqli_prepare($con, "SELECT COUNT(*) FROM bills WHERE renter_id = ?");
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);
$totalBills = mysqli_stmt_get_result($stmt)->fetch_row()[0];
$stmt->close();

//Total payments
$stmt = mysqli_prepare($con, "
    SELECT IFNULL(SUM(amount),0)
    FROM payments
    WHERE renter_id = ?
      AND status = 'completed'
");
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);

$totalPayment = number_format(mysqli_stmt_get_result($stmt)->fetch_row()[0], 2);
$stmt->close();


$stmt = mysqli_prepare($con, "SELECT COUNT(*) AS total FROM complaints WHERE renter_id = ?");
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$totalComplaints = $row['total'];
$stmt->close();


// Total Complaints
$stmt = mysqli_prepare($con, "SELECT COUNT(*) AS total FROM complaints WHERE renter_id = ?");
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$totalComplaints = $row['total'] ?? 0;
$stmt->close();

// Next Upcoming Due Bill

$stmt = mysqli_prepare($con, "
    SELECT reference_id, total_amount, due_date 
    FROM bills 
    WHERE renter_id = ? AND status IN ('open','partial','awaiting_confirmation')
    ORDER BY due_date ASC 
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$nextBill = mysqli_fetch_assoc($result);
$stmt->close();

$nextRef = $nextBill['reference_id'] ?? "N/A";
$nextAmount = isset($nextBill['total_amount']) ? number_format($nextBill['total_amount'], 2) : "0.00";
$nextDue = isset($nextBill['due_date']) ? date('F j, Y', strtotime($nextBill['due_date'])) : "No upcoming bills";


//total complaints
//total complaints
$renter_id = $_SESSION['auth_user']['user_id'];

$query = "SELECT COUNT(*) AS total_complaints FROM complaints WHERE renter_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $renter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$total_complaints = $row['total_complaints'] ?? 0;
?>

<div class="container-fluid px-4">
    <h2 class="mt-4">Renter Dashboard</h2>
    <div class="row">

        <!-- Total Payment -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary mb-4">
                <div class="card-body text-secondary">Total Payment
                    <h5 class="fw-bold mb-0">₱<?= $totalPayment; ?></h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="mybills.php">View All Payments</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Bills -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary mb-4">
                <div class="card-body text-secondary">Total Bills
                    <h5 class="fw-bold mb-0"><?= $totalBills; ?></h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="bills.php">View All Bills</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Total Complaints -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary mb-4">
                <div class="card-body text-secondary">Total Complaints
                    <h5 class="fw-bold mb-0"><?= $total_complaints; ?></h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="complaint.php">View All Complaints</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <!-- Next Upcoming Due -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-secondary mb-4">
                <div class="card-body text-secondary">Incoming Due
                    <h5 class="fw-bold mb-0">
                        <?= $nextAmount; ?> – Due: <?= $nextDue; ?>
                    </h5>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-secondary stretched-link" href="bills.php">View Bills</a>
                    <div class="small text-secondary"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
