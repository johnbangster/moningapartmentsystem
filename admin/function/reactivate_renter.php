<?php
require('../config/dbcon.php');
header('Content-Type: application/json');

$response = [];

if (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);

    // First, get the renter's unit_id
    $getUnitQuery = "SELECT unit_id FROM renters WHERE id='$id' LIMIT 1";
    $getUnitResult = mysqli_query($con, $getUnitQuery);

    if ($getUnitResult && mysqli_num_rows($getUnitResult) > 0) {
        $renter = mysqli_fetch_assoc($getUnitResult);
        $unit_id = $renter['unit_id'];

        // Reactivate renter
        $updateRenter = "UPDATE renters SET status='Active' WHERE id='$id'";
        $runRenter = mysqli_query($con, $updateRenter);

        if ($runRenter && mysqli_affected_rows($con) > 0) {
            // Update the corresponding unit to occupied
            if (!empty($unit_id)) {
                $updateUnit = "UPDATE units SET status='Occupied' WHERE id='$unit_id'";
                mysqli_query($con, $updateUnit);
            }

            $response = [
                'success' => true,
                'message' => 'Renter reactivated successfully and unit marked as occupied.'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Renter not found or already active.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Renter not found.'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request.'
    ];
}

echo json_encode($response);
exit;
?>
