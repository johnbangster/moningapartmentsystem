<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
session_start();
require('../config/dbcon.php'); 

if (!isset($_GET['unit_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unit ID missing']);
    exit;
}

$unit_id = intval($_GET['unit_id']);

// Fetch unit
$unit_sql = "SELECT * FROM units WHERE id=? AND removed=0 LIMIT 1";
$stmt = mysqli_prepare($con, $unit_sql);
mysqli_stmt_bind_param($stmt, "i", $unit_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unit not found']);
    exit;
}

$unit = mysqli_fetch_assoc($result);

// Fetch features (already correct)
$f_sql = "SELECT features_id FROM unit_features WHERE unit_id=?";
$stmt_f = mysqli_prepare($con, $f_sql);
if(!$stmt_f) { die("Prepare failed: " . mysqli_error($con)); }
mysqli_stmt_bind_param($stmt_f, "i", $unit_id);
mysqli_stmt_execute($stmt_f);
$res_f = mysqli_stmt_get_result($stmt_f);
$features = [];
while($row = mysqli_fetch_assoc($res_f)) {
    $features[] = $row['features_id'];
}

// Fetch facilities (fix column name)
$fa_sql = "SELECT facilities_id FROM unit_facilities WHERE unit_id=?";
$stmt_fa = mysqli_prepare($con, $fa_sql);
if(!$stmt_fa) { die("Prepare failed: " . mysqli_error($con)); }
mysqli_stmt_bind_param($stmt_fa, "i", $unit_id);
mysqli_stmt_execute($stmt_fa);
$res_fa = mysqli_stmt_get_result($stmt_fa);
$facilities = [];
while($row = mysqli_fetch_assoc($res_fa)) {
    $facilities[] = $row['facilities_id'];
}


// Return JSON
echo json_encode([
    'status' => 'success',
    'data' => [
        'id' => $unit['id'],
        'name' => $unit['name'],
        'area' => $unit['area'],
        'price' => $unit['price'],
        'unit_type_id' => $unit['unit_type_id'],
        'branch_id' => $unit['branch_id'],
        'adult' => $unit['adult'],
        'children' => $unit['children'],
        'description' => $unit['description'],
        'features' => $features,
        'facilities' => $facilities
    ]
]);
