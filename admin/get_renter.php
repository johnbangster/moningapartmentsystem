<?php
require 'config/dbcon.php';
header('Content-Type: application/json');

$renter_id = isset($_GET['renter_id']) ? intval($_GET['renter_id']) : 0;

if (!$renter_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid renter ID']);
    exit;
}

// Get renter with unit and latest agreement
// $query = "
//     SELECT 
//         r.id AS renter_id,
//         r.unit_id,
//         u.name AS unit_name,
//         u.price AS unit_price,
//         ra.start_date,
//         ra.end_date,
//         ra.term_months,
//         ra.monthly_rent,
//         ra.deposit
//     FROM renters r
//     LEFT JOIN units u ON r.unit_id = u.id
//     LEFT JOIN rental_agreements ra ON ra.renter_id = r.id
//     WHERE r.id = '$renter_id'
//     ORDER BY ra.id DESC
//     LIMIT 1
// ";

// Fetch renter data including lease info and unit
$sql = "SELECT r.id, r.first_name, r.last_name, r.move_in_date, r.lease_term, r.carry_balance, 
               r.unit_id, u.price AS unit_price
        FROM renters r
        LEFT JOIN units u ON r.unit_id = u.id
        WHERE r.id = $renter_id
        LIMIT 1";

$result = mysqli_query($con, $sql);

// if ($row = mysqli_fetch_assoc($result)) {
//     // Compute missing values
//     $unit_price = $row['unit_price'] ?? 0;
//     $deposit = $row['deposit'] ?? $unit_price;
//     $term = $row['term_months'] ?? 6;

//     echo json_encode([
//         'success' => true,
//         'unit_id' => $row['unit_id'],
//         'unit_name' => $row['unit_name'],
//         'unit_price' => $unit_price,
//         'deposit' => $deposit,
//         'term_months' => $term,
//         'start_date' => $row['start_date'],
//         'end_date' => $row['end_date']
//     ]);
// } else {
//     echo json_encode(['success' => false, 'error' => 'Renter not found']);
// }


if ($row = mysqli_fetch_assoc($result)) {
    $start_date = $row['move_in_date'];
    $term_months = $row['lease_term'];

    // Compute end date
    $end_date = null;
    if ($start_date && $term_months) {
        $end = new DateTime($start_date);
        $end->modify("+{$term_months} months");
        $end_date = $end->format('Y-m-d');
    }

    echo json_encode([
        'success' => true,
        'unit_id' => $row['unit_id'],
        'unit_price' => $row['unit_price'],
        'deposit' => $row['unit_price'],   // use carry_balance if deposit
        'term_months' => $term_months,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Renter not found']);
}

?>