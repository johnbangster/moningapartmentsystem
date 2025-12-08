<?php
session_start();
require '../config/dbcon.php';

if (!isset($_SESSION['auth_role']) || !in_array($_SESSION['auth_role'], ['admin', 'employee'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$cid = intval($_POST['complaint_id']);
$status = trim($_POST['status']);

$allowed = ['open', 'ongoing', 'resolved'];
if ($cid > 0 && in_array($status, $allowed)) {
    $stmt = mysqli_prepare($con, "UPDATE complaints SET status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $cid);
    if (mysqli_stmt_execute($stmt)) {
        echo "Success";
    } else {
        http_response_code(500);
        echo "DB Error";
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(400);
    echo "Invalid data";
}
?>
