<?php
include('../config/dbcon.php'); // adjust path
header('Content-Type: application/json'); // Ensure JSON header

if(isset($_POST['unit_id'])) {
    $unit_id = intval($_POST['unit_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $area = intval($_POST['area']);
    $price = intval($_POST['price']);
    $unit_type_id = intval($_POST['unit_type_id']);
    $branch_id = intval($_POST['branch_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $adult = intval($_POST['adult']);
    $children = intval($_POST['children']);
    $desc = mysqli_real_escape_string($con, $_POST['desc']);

    $features = isset($_POST['features']) ? $_POST['features'] : [];
    $facilities = isset($_POST['facilities']) ? $_POST['facilities'] : [];

    mysqli_begin_transaction($con);

    try {
        // Update units
        $update_unit = mysqli_query($con, "
            UPDATE units SET
                name='$name',
                area='$area',
                price='$price',
                unit_type_id='$unit_type_id',
                branch_id='$branch_id',
                status='$status',
                adult='$adult',
                children='$children',
                description='$desc'
            WHERE id='$unit_id'
        ");
        if(!$update_unit) throw new Exception("Failed to update unit: ".mysqli_error($con));

        // Update features
        mysqli_query($con, "DELETE FROM unit_features WHERE unit_id='$unit_id'");
        foreach($features as $fid){
            if(!mysqli_query($con, "INSERT INTO unit_features(unit_id, features_id) VALUES('$unit_id','$fid')")) {
                throw new Exception("Feature update failed: ".mysqli_error($con));
            }
        }

        // Update facilities
        mysqli_query($con, "DELETE FROM unit_facilities WHERE unit_id='$unit_id'");
        foreach($facilities as $facid){
            if(!mysqli_query($con, "INSERT INTO unit_facilities(unit_id, facilities_id) VALUES('$unit_id','$facid')")) {
                throw new Exception("Facility update failed: ".mysqli_error($con));
            }
        }

        mysqli_commit($con);
        echo json_encode(['status'=>'success','message'=>'Unit updated successfully!']);
        exit;

    } catch(Exception $e){
        mysqli_rollback($con);
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
        exit;
    }

} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request!']);
    exit;
}
