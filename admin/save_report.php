<?php
session_start();
require 'config/dbcon.php';

// Check if logged in
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    die("Access denied. Please log in.");
}

// Check role
if ($_SESSION['auth_role'] !== 'employee') {
    die("Access denied. Only employees can create cash reports.");
}

// Employee ID comes from logged-in user
$employee_id = (int) $_SESSION['auth_user']['user_id'];

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$renter_id   = (int) $_POST['renter_id'];
$bill_id     = (int) $_POST['bill_id'];
$amount_paid = $_POST['amount_paid'];
$payment_date = $_POST['payment_date'];
$notes       = mysqli_real_escape_string($con, $_POST['notes'] ?? '');

// Validate required fields
if (!$renter_id || !$bill_id || !$amount_paid || !$payment_date) {
    die("All required fields must be filled.");
}

// Handle receipt file upload
if (!isset($_FILES['receipt_file'])) {
    die("Receipt file is required.");
}

$file = $_FILES['receipt_file'];
$maxSize = 2 * 1024 * 1024; // 2MB

// Validate file size
if ($file['size'] > $maxSize) {
    die("Receipt file exceeds 2MB limit.");
}

// Validate file type (image or PDF)
$allowed = ['image/jpeg','image/png','image/jpg','application/pdf'];
if (!in_array($file['type'], $allowed)) {
    die("Invalid file type. Only JPG, PNG, or PDF allowed.");
}

// Upload folder
$folder = "uploads/receipt_cash/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "receipt_" . time() . "_" . rand(1000,9999) . "." . $ext;
$filepath = $folder . $filename;

move_uploaded_file($file['tmp_name'], $filepath);

// Insert into cash_reports
$sql = "INSERT INTO cash_reports 
        (employee_id, renter_id, bill_id, amount_paid, payment_date, receipt_path, notes)
        VALUES 
        ($employee_id, $renter_id, $bill_id, '$amount_paid', '$payment_date', '$filepath', '$notes')";

if (mysqli_query($con, $sql)) {
    echo "<script>
        alert('Cash report created successfully!');
        window.location.href='employee_report.php';
    </script>";
} else {
    echo "<script>
        alert('Database error: ".mysqli_error($con)."');
        window.history.back();
    </script>";
}
