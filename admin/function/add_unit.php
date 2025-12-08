<?php
header('Content-Type: application/json');
session_start();
require('../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = mysqli_real_escape_string($con, $_POST['name']);
    $area        = (int)$_POST['area'];
    $price       = (int)$_POST['price'];
    $adult       = (int)$_POST['adult'];
    $children    = (int)$_POST['children'];
    $desc        = mysqli_real_escape_string($con, $_POST['desc']);
    $branch_id   = (int)$_POST['branch_id'];
    $unit_type   = (int)$_POST['unit_type_id'];

    $query = "INSERT INTO units 
                (name, area, price, qty, adult, children, address, description, branch_id, unit_type_id) 
              VALUES 
                ('$name', '$area', '$price', 1, '$adult', '$children', '', '$desc', '$branch_id', '$unit_type')";

    if (mysqli_query($con, $query)) {
        $unit_id = mysqli_insert_id($con);

        // save features
        if (isset($_POST['features'])) {
            foreach ($_POST['features'] as $fid) {
                mysqli_query($con, "INSERT INTO unit_features (unit_id, features_id) VALUES ('$unit_id', '$fid')");
            }
        }

        // save facilities
        if (isset($_POST['facilities'])) {
            foreach ($_POST['facilities'] as $faid) {
                mysqli_query($con, "INSERT INTO unit_facilities (unit_id, facilities_id) VALUES ('$unit_id', '$faid')");
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Unit added successfully!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . mysqli_error($con)
        ]);
    }
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
