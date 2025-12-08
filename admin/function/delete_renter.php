<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // ignore notices/warnings
session_start();
require('../config/dbcon.php');

// Force JSON output
header('Content-Type: application/json');

$response = [];

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Invalid request. No renter ID provided.');
    }

    $id = intval($_POST['id']);

    // Step 1: Get renter info (unit_id and email)
    $stmt = $con->prepare("SELECT unit_id, email FROM renters WHERE id = ? LIMIT 1");
    if (!$stmt) throw new Exception("Prepare failed: ".$con->error);

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        throw new Exception("Renter not found.");
    }

    $row = $result->fetch_assoc();
    $unit_id = $row['unit_id'];
    $email = $row['email'];
    $stmt->close();

    // Step 2: Make the unit available
    if (!empty($unit_id)) {
        $update_unit = $con->prepare("UPDATE units SET status='Available' WHERE id = ?");
        if (!$update_unit) throw new Exception("Prepare failed: ".$con->error);

        $update_unit->bind_param("i", $unit_id);
        $update_unit->execute();
        $update_unit->close();
    }

    // Step 3: Delete corresponding user
    $delete_user = $con->prepare("DELETE FROM users WHERE email = ? AND role='renter'");
    if (!$delete_user) throw new Exception("Prepare failed: ".$con->error);

    $delete_user->bind_param("s", $email);
    $delete_user->execute();
    $delete_user->close();

    // Step 4: Delete renter
    $delete_renter = $con->prepare("DELETE FROM renters WHERE id = ?");
    if (!$delete_renter) throw new Exception("Prepare failed: ".$con->error);

    $delete_renter->bind_param("i", $id);
    if (!$delete_renter->execute()) {
        throw new Exception("Failed to delete renter: ".$delete_renter->error);
    }
    $delete_renter->close();

    // Step 5: Log deletion
    $user = $_SESSION['auth_user']['username'] ?? 'system';
    $role = $_SESSION['auth_role'] ?? 'unknown';

    $log_stmt = $con->prepare("INSERT INTO deletion_logs (entity_type, entity_id, deleted_by, role, deleted_at) VALUES (?, ?, ?, ?, NOW())");
    if (!$log_stmt) throw new Exception("Prepare failed: ".$con->error);

    $entity_type = 'renter';
    $log_stmt->bind_param("siss", $entity_type, $id, $user, $role);
    $log_stmt->execute();
    $log_stmt->close();

    $response = [
        'success' => true,
        'message' => 'Renter and corresponding user deleted successfully. Unit is now available.'
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Return JSON
echo json_encode($response);
exit;
