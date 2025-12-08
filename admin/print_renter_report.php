<?php
session_start();
include('config/dbcon.php');

if (!isset($_GET['renter_id'])) {
    die("No renter selected.");
}

$renter_id = intval($_GET['renter_id']);
include('renter_report.php'); 

echo "<script>window.print();</script>";
