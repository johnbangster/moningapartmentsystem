<?php
session_start();
require '../config/dbcon.php';

header('Content-Type: application/json');

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

$name = trim($_POST['name'] ?? '');
$area = trim($_POST['area'] ?? '');
$price = trim($_POST['price'] ?? '');
$unit_type_id = intval($_POST['unit_type_id'] ?? 0);
$branch_id = intval($_POST['branch_id'] ?? 0);

if ($name == '' || $area == '' || $price == '' || $unit_type_id == 0 || $branch_id == 0) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Prevent duplicate unit names
$check = mysqli_query($con, "SELECT id FROM units WHERE name='$name' AND removed = 0");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(["status" => "error", "message" => "Unit name already exists."]);
    exit;
}

// Insert new unit
$query = "INSERT INTO units (name, area, price, unit_type_id, branch_id, status, removed) 
          VALUES ('$name', '$area', '$price', '$unit_type_id', '$branch_id', 'Available', 0)";

if (mysqli_query($con, $query)) {
    $insert_id = mysqli_insert_id($con);

    echo json_encode([
        "status" => "success",
        "message" => "Unit added successfully!",
        "unit" => [
            "id" => $insert_id,
            "name" => $name,
            "status" => "Available"
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($con)]);
}
