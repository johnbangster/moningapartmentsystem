<?php
include('config/dbcon.php');

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : "";

$query = "
    SELECT 
        r.id AS renter_id, r.first_name, r.middle_name, r.last_name, r.email, r.contacts,
        u.name AS unit_name, t.type_name AS unit_type,
        a.term_months, a.monthly_rent, a.start_date, a.end_date
    FROM renters r
    LEFT JOIN rental_agreements a ON a.renter_id = r.id
    LEFT JOIN units u ON a.unit_id = u.id
    LEFT JOIN unit_type t ON u.unit_type_id = t.id
";

if ($search != "") {
    $query .= " WHERE r.first_name LIKE '%$search%' 
                OR r.last_name LIKE '%$search%'
                OR r.email LIKE '%$search%'
                OR r.contacts LIKE '%$search%'
                OR u.name LIKE '%$search%'";
}

$result = mysqli_query($con, $query);

//CSV Headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=renter_report_' . date("Ymd") . '.csv');

$output = fopen('php://output', 'w');

//CSV column headers
fputcsv($output, ['ID', 'Full Name', 'Email', 'Phone', 'Unit', 'Unit Type', 'Term', 'Rent', 'Start Date', 'End Date']);

//Rows
while($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['renter_id'],
        $row['first_name'].' '.$row['middle_name'].' '.$row['last_name'],
        $row['email'],
        $row['contacts'],
        $row['unit_name'],
        $row['unit_type'],
        $row['term_months'],
        $row['monthly_rent'],
        $row['start_date'],
        $row['end_date']
    ]);
}

fclose($output);
exit();
