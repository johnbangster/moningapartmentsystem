<?php
session_start();
require('../admin/config/dbcon.php');

if (!isset($_SESSION['renter_id'])) {
    http_response_code(403);
    exit("Not logged in.");
}

$renter_id = $_SESSION['renter_id'];

$type = $_POST['complaint_type'] ?? '';
$remarks = trim($_POST['remarks'] ?? '');

if ($type === 'others' && empty($remarks)) {
    http_response_code(400);
    exit("Please provide remarks.");
}

$stmt = mysqli_prepare($con, "INSERT INTO complaints (renter_id, complaint_type, remarks, created_by) VALUES (?, ?, ?, 'renter')");
mysqli_stmt_bind_param($stmt, "iss", $renter_id, $type, $remarks);

if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    exit("DB Error: " . mysqli_error($con));
}

echo "OK";
