<?php
require('config/dbcon.php');


//check if ajax request is made to delete renter
if(isset($_POST['remove_renter']) && isset($_POST['renter_id'])) {
    //sanitize the renter id
    $renter_id = (int)$_POST['renter_id'];


mysqli_begin_transaction($con);

try{
    //delete associated images
    $delete_images = "SELECT image FROM renter_images WHERE renter_id =?";
    $stmt = mysqli_prepare($con, $delete_images);
    mysqli_stmt_bind_param($stmt, 'i', $renter_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    //delete image
    while($row = mysqli_fetch_assoc($result)) {
        $image_path = RENTERS_FOLDER . $row['image'];
        if(file_exists($image_path)) {
            unlink($image_path);  //delete image file
        }
    }

    //delete image from db
    $delete_img_db = "DELETE FROM renter_images WHERE renter_id =?";
    $stmt_del_img = mysqli_prepare($con, $delete_img_db);
    mysqli_stmt_bind_param($stmt_del_img, 'i', $renter_id);
    mysqli_stmt_execute($stmt_del_img);


    //delete any agreement related to renter
    $delete_agreement = "DELETE FROM rental_agreements WHERE renter_id =?";
    $stmt_del_agree = mysqli_prepare($con, $delete_agreement);
    mysqli_stmt_bind_param($stmt_del_agree, 'i', $renter_id);
    mysqli_stmt_execute($stmt_del_agree);

    //delete renter from db
    $delete_renter = "DELETE FROM renters WHERE id =?";
    $stmt_del_renter = mysqli_prepare($con, $delete_renter);
    mysqli_stmt_bind_param($stmt_del_renter, 'i', $renter_id);
    mysqli_stmt_execute($stmt_del_renter);

    mysqli_commit($con);

    //respond with success
    echo json_encode(['status' => 'success', 'message' => 'Renter deleted successfully']);
}catch (Exception $e) {
    mysqli_rollback($con);

    //return error message
    echo json_encode (['status' => 'error', 'message' => $e->getMessage()]);
 
}

}