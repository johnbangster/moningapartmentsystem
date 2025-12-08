<?php
session_start();
require_once __DIR__ . '/../config/dbcon.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../renter.php');
    exit;
}

// Sanitize inputs
$first      = trim(mysqli_real_escape_string($con, $_POST['first_name'] ?? ''));
$last       = trim(mysqli_real_escape_string($con, $_POST['last_name'] ?? ''));
$middle     = trim(mysqli_real_escape_string($con, $_POST['middle_name'] ?? ''));
$contact    = trim(mysqli_real_escape_string($con, $_POST['contact'] ?? ''));
$email      = trim(mysqli_real_escape_string($con, $_POST['email'] ?? ''));
$address    = trim(mysqli_real_escape_string($con, $_POST['address'] ?? ''));
$password   = "renter123"; // default password
$move_in    = trim($_POST['move_in_date'] ?? '');
$lease_term = intval($_POST['lease_term'] ?? 0);
$unit_id    = intval($_POST['unit_id'] ?? 0);

// Check required fields
if (!$first || !$last || !$contact || !$email || !$address || !$move_in || !$lease_term || !$unit_id) {
    $_SESSION['error'] = "All required fields must be filled!";
    header("Location: ../renter.php");
    exit;
}

// Check for duplicate contact/email
$dup = mysqli_query($con, "SELECT id FROM users WHERE email='$email' OR contact='$contact' LIMIT 1");
if (mysqli_num_rows($dup) > 0) {
    $_SESSION['error'] = "Email or Contact Number already exists!";
    header("Location: ../renter.php");
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Start transaction
mysqli_begin_transaction($con);

try {
    // Step 1: Insert into users
    $user_sql = "INSERT INTO users 
        (first_name, last_name, middle_name, contact, email, address, password, role, status, created_at)
        VALUES ('$first', '$last', '$middle', '$contact', '$email', '$address', '$hashed_password', 'renter', 'Active', NOW())";

    if (!mysqli_query($con, $user_sql)) {
        throw new Exception("Error inserting user: " . mysqli_error($con));
    }

    $user_id = mysqli_insert_id($con);
    if (!$user_id) {
        throw new Exception("Failed to retrieve user ID");
    }

    // Step 2: Insert into renters
    $renter_sql = "INSERT INTO renters 
        (user_id, first_name, last_name, middle_name, contacts, email, address, password, status, carry_balance, move_in_date, lease_term, unit_id, image, created_at)
        VALUES ($user_id, '$first', '$last', '$middle', '$contact', '$email', '$address', '$password', 'Active', 0.00, '$move_in', $lease_term, $unit_id, '', NOW())";

    if (!mysqli_query($con, $renter_sql)) {
        throw new Exception("Error inserting renter: " . mysqli_error($con));
    }

    $renter_id = mysqli_insert_id($con);

    // Step 3: Insert family members if submitted
    if (!empty($_POST['member_name']) && !empty($_POST['member_type_id'])) {
        $members = $_POST['member_name'];
        $types   = $_POST['member_type_id'];

        for ($i = 0; $i < count($members); $i++) {
            $m_name = trim(mysqli_real_escape_string($con, $members[$i]));
            $m_type = intval($types[$i]);
            if ($m_name !== '' && $m_type > 0) {
                mysqli_query($con, "INSERT INTO renter_members (renter_id, member_name, member_type_id) VALUES ($renter_id, '$m_name', $m_type)");
            }
        }
    }

    // Step 4: Update unit status
    mysqli_query($con, "UPDATE units SET status='Occupied' WHERE id=$unit_id");

    // Commit transaction
    mysqli_commit($con);

    $_SESSION['success'] = "Renter added successfully!";
    header("Location: ../renter.php");
    exit;

} catch (Exception $e) {
    mysqli_rollback($con);
    $_SESSION['error'] = "Failed to add renter: " . $e->getMessage();
    header("Location: ../renter.php");
    exit;
}
