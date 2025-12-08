<?php
ob_start();
require('../config/dbcon.php');
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

$response = ['success' => false, 'message' => 'Unknown error'];

if (!empty($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    
    // Start transaction for data consistency
    mysqli_begin_transaction($con);
    
    // First, get the unit_id associated with this renter
    $get_unit_query = "SELECT unit_id FROM renters WHERE id='$id'";
    $get_unit_result = mysqli_query($con, $get_unit_query);
    
    if (!$get_unit_result) {
        mysqli_rollback($con);
        $response = [
            'success' => false,
            'message' => 'Failed to fetch renter data: ' . mysqli_error($con)
        ];
    } else {
        $renter_data = mysqli_fetch_assoc($get_unit_result);
        
        if (!$renter_data) {
            mysqli_rollback($con);
            $response = [
                'success' => false,
                'message' => 'Renter not found'
            ];
        } else {
            $unit_id = $renter_data['unit_id'];
            
            // Update renter status to 'De-Activate'
            $update_renter_query = "UPDATE renters SET status='De-Activate' WHERE id='$id'";
            $update_renter_result = mysqli_query($con, $update_renter_query);
            
            if (!$update_renter_result) {
                mysqli_rollback($con);
                $response = [
                    'success' => false,
                    'message' => 'Failed to deactivate renter: ' . mysqli_error($con)
                ];
            } else {
                // Update the unit status to 'available' in the units table
                if ($unit_id && $unit_id > 0) {
                    $update_unit_query = "UPDATE units SET status='available' WHERE id='$unit_id'";
                    $update_unit_result = mysqli_query($con, $update_unit_query);
                    
                    if (!$update_unit_result) {
                        mysqli_rollback($con);
                        $response = [
                            'success' => false,
                            'message' => 'Failed to update unit status: ' . mysqli_error($con)
                        ];
                    } else {
                        // Commit transaction if all queries succeeded
                        mysqli_commit($con);
                        $response = [
                            'success' => true,
                            'message' => 'Renter has been deactivated successfully. Unit has been set to available.',
                            'status' => 'De-Activate'
                        ];
                    }
                } else {
                    // Commit transaction (only renter update was needed)
                    mysqli_commit($con);
                    $response = [
                        'success' => true,
                        'message' => 'Renter has been deactivated successfully. No unit assigned to this renter.',
                        'status' => 'De-Activate'
                    ];
                }
            }
        }
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request — renter ID missing.'
    ];
}

ob_end_clean();
echo json_encode($response);
exit;
?>