<?php
session_start();
require('../config/dbcon.php');

header('Content-Type: application/json');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
    exit;
}

$id = (int) $_POST['id'];

$stmt = mysqli_prepare($con, "UPDATE renters SET status = 'Active' WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($con)]);
}
