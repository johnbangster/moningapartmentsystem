<?php
require_once __DIR__.'/../config/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../renter.php'); exit; }
$id = intval($_POST['id']);
mysqli_query($con, "UPDATE renters SET status='De-Activate' WHERE id='$id'");
echo "<script src='https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js'></script>";
echo "<script>alertify.success('Renter deactivated'); setTimeout(()=>window.location='../renter.php',1200);</script>";
