<?php
require('config/dbcon.php');
// $id = intval($_GET['id']);
// $q = mysqli_query($con, "SELECT p.*, r.fname, r.lname, b.bill_no 
//                          FROM payments p 
//                          JOIN renters r ON p.renter_id=r.id 
//                          JOIN bills b ON p.bill_id=b.id 
//                          WHERE p.id='$id'");
// $receipt = mysqli_fetch_assoc($q);
// $cashier = $_SESSION['auth_user']['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Receipt #<?= $receipt['id'] ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { font-family: 'Courier New', monospace; }
    .receipt { width: 350px; margin: auto; border: 1px dashed #ccc; padding: 15px; }
    .title { text-align: center; font-weight: bold; font-size: 18px; }
    .info { font-size: 13px; }
  </style>
</head>
<body onload="window.print()">
  <div class="receipt">
    <div class="title">Moning’s Rental</div>
    <p class="text-center mb-0">1438-B M.J. Cuenco, Mabolo Cebu City</p>
    <p class="text-center mb-0">TIN: 123-456-789</p>
    <hr>
    <p class="info">
      <b>Receipt No:</b> <?= $receipt['id'] ?><br>
      <b>Date:</b> <?= date('M d, Y', strtotime($receipt['payment_date'])) ?><br>
      <b>Received From:</b> <?= $receipt['fname'].' '.$receipt['lname'] ?><br>
      <b>Amount:</b> ₱<?= number_format($receipt['amount'],2) ?><br>
      <b>Payment Method:</b> <?= ucfirst($receipt['payment_type']) ?><br>
      <b>Bill Ref:</b> <?= $receipt['bill_no'] ?><br>
    </p>
    <hr>
    <p class="text-center mt-4">
      <b>Cashier:</b> <?= $cashier ?: '_________________' ?><br>
      <small>Signature Over Printed Name</small>
    </p>
    <p class="text-center mt-3">Thank you for your payment!</p>
  </div>
</body>
</html>
