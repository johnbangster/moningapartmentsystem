<?php
session_start();
require_once __DIR__ . '/../config/dbcon.php';

// Only allow GET with renter ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid Renter ID.";
    header("Location: ../renter.php");
    exit;
}

$renter_id = intval($_GET['id']);

mysqli_begin_transaction($con);

try {
    // Step 1: Get renter info
    $res = mysqli_query($con, "SELECT unit_id, user_id FROM renters WHERE id = $renter_id LIMIT 1");
    if (mysqli_num_rows($res) === 0) {
        throw new Exception("Renter not found.");
    }
    $renter = mysqli_fetch_assoc($res);
    $unit_id = $renter['unit_id'];
    $user_id = $renter['user_id'];

    // Step 2: Delete child tables first
    mysqli_query($con, "DELETE FROM renter_members WHERE renter_id = $renter_id");
    mysqli_query($con, "DELETE FROM rental_agreements WHERE renter_id = $renter_id");
    mysqli_query($con, "DELETE FROM renter_images WHERE renter_id = $renter_id"); // if you have renter images

    // Step 3: Delete renter
    mysqli_query($con, "DELETE FROM renters WHERE id = $renter_id");

    // Step 4: Delete associated user
    mysqli_query($con, "DELETE FROM users WHERE id = $user_id");

    // Step 5: Update unit status to 'Available'
    mysqli_query($con, "UPDATE units SET status = 'Available' WHERE id = $unit_id");

    mysqli_commit($con);

    $_SESSION['success'] = "Renter and user deleted successfully!";
    header("Location: ../renter.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($con);
    $_SESSION['error'] = "Failed to delete renter: " . $e->getMessage();
    header("Location: ../renter.php");
    exit;
}
?>
