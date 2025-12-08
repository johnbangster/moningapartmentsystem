<?php
require('config/dbcon.php');

$year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;
$branch_id = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;

// Base conditions
$where = [];
if ($year) $where[] = "YEAR(b.due_date) = $year";
if ($branch_id) $where[] = "b.branch_id = $branch_id";

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

//Fetch billed data 
$billedQuery = "
    SELECT DATE_FORMAT(b.due_date, '%Y-%m') AS month, SUM(b.total_amount) AS total_billed
    FROM bills b
    $whereSQL
    GROUP BY DATE_FORMAT(b.due_date, '%Y-%m')
    ORDER BY month ASC
";
$billedResult = mysqli_query($con, $billedQuery);

$billedData = [];
while ($row = mysqli_fetch_assoc($billedResult)) {
    $billedData[$row['month']] = $row['total_billed'];
}

//Fetch paid data 
$paidQuery = "
    SELECT DATE_FORMAT(p.payment_date, '%Y-%m') AS month, SUM(p.amount) AS total_paid
    FROM payments p
    LEFT JOIN bills b ON b.id = p.bill_id
    " . ($whereSQL ? str_replace('WHERE', 'WHERE', $whereSQL) : '') . "
    GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
    ORDER BY month ASC
";
$paidResult = mysqli_query($con, $paidQuery);

$paidData = [];
while ($row = mysqli_fetch_assoc($paidResult)) {
    $paidData[$row['month']] = $row['total_paid'];
}

//Merge months 
$allMonths = array_unique(array_merge(array_keys($billedData), array_keys($paidData)));
sort($allMonths);

$billedValues = [];
$paidValues = [];

foreach ($allMonths as $month) {
    $billedValues[] = isset($billedData[$month]) ? (float)$billedData[$month] : 0;
    $paidValues[] = isset($paidData[$month]) ? (float)$paidData[$month] : 0;
}

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'months' => $allMonths,
    'billed' => $billedValues,
    'paid' => $paidValues
]);
?>
