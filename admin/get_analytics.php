<?php
require('config/dbcon.php');

$response = ["branch" => [], "monthly" => []];

//Revenue Per Branch
$branchQuery = "
    SELECT br.name AS branch_name,
           SUM(b.amount_paid) AS total_paid,
           SUM(b.total_amount - b.amount_paid) AS total_unpaid
    FROM bills b
    JOIN branch br ON br.id = b.branch_id
    GROUP BY b.branch_id
";
$branchResult = mysqli_query($con, $branchQuery);
while ($row = mysqli_fetch_assoc($branchResult)) {
    $response['branch'][] = $row;
}

//Monthly Income vs Unpaid Bills
$monthlyQuery = "
    SELECT DATE_FORMAT(b.payment_date, '%Y-%m') AS month,
           SUM(b.amount_paid) AS total_income,
           SUM(b.total_amount - b.amount_paid) AS total_unpaid
    FROM bills b
    GROUP BY DATE_FORMAT(b.payment_date, '%Y-%m')
    ORDER BY month
";
$monthlyResult = mysqli_query($con, $monthlyQuery);
while ($row = mysqli_fetch_assoc($monthlyResult)) {
    $response['monthly'][] = $row;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
