<?php
include('../config/dbcon.php'); // adjust path

if(isset($_POST['unit_id'])){
    $unit_id = $_POST['unit_id'];

    // Fetch unit
    $query = mysqli_query($con, "SELECT * FROM units WHERE id='$unit_id'");
    if(mysqli_num_rows($query) > 0){
        $unit = mysqli_fetch_assoc($query);

        // Fetch features
        $features_res = mysqli_query($con, "SELECT feature_id FROM unit_features WHERE unit_id='$unit_id'");
        $features = [];
        while($f = mysqli_fetch_assoc($features_res)){
            $features[] = $f['feature_id'];
        }

        // Fetch facilities
        $facilities_res = mysqli_query($con, "SELECT facility_id FROM unit_facilities WHERE unit_id='$unit_id'");
        $facilities = [];
        while($f = mysqli_fetch_assoc($facilities_res)){
            $facilities[] = $f['facility_id'];
        }

        echo json_encode([
            'status' => 200,
            'data' => [
                'id' => $unit['id'],
                'name' => $unit['name'],
                'area' => $unit['area'],
                'price' => $unit['price'],
                'unit_type_id' => $unit['unit_type_id'],
                'branch_id' => $unit['branch_id'],
                'status' => $unit['status'],
                'adult' => $unit['adult'],
                'children' => $unit['children'],
                'desc' => $unit['description'],
                'features' => $features,
                'facilities' => $facilities
            ]
        ]);
    } else {
        echo json_encode(['status'=>404,'message'=>'Unit not found']);
    }
}
