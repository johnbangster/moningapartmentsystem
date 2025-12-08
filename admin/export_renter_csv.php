<?php
session_start();
include('config/dbcon.php');

if (!isset($_GET['renter_id'])) {
    die("No renter selected.");
}

$renter_id = intval($_GET['renter_id']);

//Query renter bills
$query = "
    SELECT 
        b.reference_id, b.due_date, b.total_amount
    FROM bills b
    WHERE b.renter_id = '$renter_id'
    ORDER BY b.due_date ASC
";
$result = mysqli_query($con, $query);

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=renter_report_$renter_id.csv");

$output = fopen("php://output", "w");

//CSV Header
fputcsv($output, ['Reference #', 'Month', 'Due Date', 'Amount']);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['reference_id'],
        date('F', strtotime($row['due_date'])),
        date('M d, Y', strtotime($row['due_date'])),
        $row['total_amount']
    ]);
}

fclose($output);
exit();
