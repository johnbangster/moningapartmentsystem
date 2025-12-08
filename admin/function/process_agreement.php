<?php
require_once('../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $renter_id = intval($_POST['renter_id']);
    $unit_id   = intval($_POST['unit_id']);
    $term      = intval($_POST['term_months']); 
    $start     = $_POST['start_date'];
    $end       = $_POST['end_date'];
    // $rent      = isset($_POST['monthly_rent']) ? floatval($_POST['monthly_rent']) : 0;
    // $deposit   = floatval($_POST['deposit']);
    // $rent    = isset($_POST['monthly_rent']) ? floatval($_POST['monthly_rent']) : 0.00;
    // $deposit = isset($_POST['deposit']) ? floatval($_POST['deposit']) : 0.00;
    $rent    = isset($_POST['monthly_rent']) ? floatval(str_replace(',', '', $_POST['monthly_rent'])) : 0.00;
    $deposit = isset($_POST['deposit']) ? floatval(str_replace(',', '', $_POST['deposit'])) : 0.00;

    $terms     = $_POST['term_conditions'];
    
    $stmt = mysqli_prepare($con, "INSERT INTO rental_agreements 
        (renter_id, unit_id, term_months, start_date, end_date, monthly_rent, deposit, term_conditions, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    if (!$stmt) {
        die("SQL Error: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "iiissdds", 
        $renter_id, $unit_id, $term, $start, $end, $rent, $deposit, $terms
    );


    // mysqli_stmt_bind_param($stmt, "iiissdds", 
    //     $renter_id, $unit_id, $term, $start, $end, $rent, $deposit, $terms
    // );


    //  Correct INSERT with proper bind types
    
    // iiissdds → correct order:
    // renter_id (i), unit_id (i), term_months (i), start_date (s), end_date (s), monthly_rent (d), deposit (d), term_conditions (s)
    
    
    if (mysqli_stmt_execute($stmt)) {
        //  Get last inserted ID (now it should NOT be 0)
        $agreement_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);

        // Redirect to view_agreement.php with agreement_id
        header("Location: ../view_agreement.php?agreement_id=" . $agreement_id);
        exit;
    } else {
        die("Insert Failed: " . mysqli_stmt_error($stmt));
    }
}
?>
