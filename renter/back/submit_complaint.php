<?php
session_start();
require('../../admin/config/dbcon.php');

header('Content-Type: application/json');

if (isset($_POST['submit_complaint'])) {
    $renter_id = $_SESSION['auth_user']['user_id'];
    $type      = mysqli_real_escape_string($con, $_POST['complaint_type']);
    $remarks   = mysqli_real_escape_string($con, trim($_POST['remarks']));

    //  Get renter's full name
    $renter_query = mysqli_query($con, "SELECT CONCAT(first_name, ' ',last_name) AS renter_name FROM users WHERE id = $renter_id");
    $renter_data = mysqli_fetch_assoc($renter_query);
    $renter_name = $renter_data['renter_name'] ?? "Unknown Renter";

    // Validate remarks if type is "others"
    if ($type === 'others' && empty($remarks)) {
        echo json_encode([
            "status" => "error",
            "message" => "Please provide specific details in Remarks."
        ]);
        exit;
    }

        // Insert complaint
        $stmt = mysqli_prepare($con, "INSERT INTO complaints (renter_id, complaint_type, remarks, created_by) VALUES (?, ?, ?, 'renter')");
        mysqli_stmt_bind_param($stmt, "iss", $renter_id, $type, $remarks);

        if (mysqli_stmt_execute($stmt)) {
        $complaint_id = mysqli_insert_id($con);

        // Notification message
        $message = "Renter $renter_name submitted a new complaint.";

        // Fetch all users who are admin or employee
        // $role_sql = "SELECT id FROM users WHERE role IN ('admin', 'employee')";
        // $role_res = mysqli_query($con, $role_sql);

        // if($role_res && mysqli_num_rows($role_res) > 0){
        //     while($row = mysqli_fetch_assoc($role_res)){
        //         $user_id = $row['id'];

        //         // Insert notification for each admin/employee
        //         $notif_sql = "INSERT INTO notifications (user_id, message, type, is_read, created_at) 
        //                     VALUES ($user_id, '".mysqli_real_escape_string($con, $message)."', 'complaint', 0, NOW())";
        //         mysqli_query($con, $notif_sql);
        //     }
        // }

        // Create ONE shared notification for all admins & employees
        $notif_sql = "
            INSERT INTO notifications (user_id, message, type, is_read, created_at)
            VALUES (NULL, '".mysqli_real_escape_string($con, $message)."', 'complaint', 0, NOW())
        ";
        mysqli_query($con, $notif_sql);


        echo json_encode([
            "status" => "success",
            "message" => "Complaint submitted successfully.",
            "complaint_id" => $complaint_id
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . mysqli_error($con)
        ]);
    }

    mysqli_stmt_close($stmt);

    // if (mysqli_stmt_execute($stmt)) {
    //     $complaint_id = mysqli_insert_id($con);

    //     // Notification using renter name instead of ID
        
    //     $message = "Renter $renter_name submitted a new complaint.";

    //     $notif_sql = "INSERT INTO notifications (user_id, message, type, is_read, created_at) 
    //                   VALUES ($renter_id, '".mysqli_real_escape_string($con, $message)."', 'complaint', 0, NOW())";
    //     mysqli_query($con, $notif_sql);

    //     echo json_encode([
    //         "status" => "success",
    //         "message" => "Complaint submitted successfully.",
    //         "complaint_id" => $complaint_id
    //     ]);
    // } else {
    //     echo json_encode([
    //         "status" => "error",
    //         "message" => "Database error: " . mysqli_error($con)
    //     ]);
    // }

    // mysqli_stmt_close($stmt);
    
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request."
    ]);
}
