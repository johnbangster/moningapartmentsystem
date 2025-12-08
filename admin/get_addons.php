<?php
require 'config/dbcon.php';


// if(isset($_POST['bill_id']) && isset($_POST['addons'])) {
//     $bill_id =  mysqli_real_escape_string($con, $_POST['bill_id']);
//     $addons = $_POST['addons'];

//     foreach($addons as $addon) {
//         $name =  mysqli_real_escape_string($con, $addon['name']);
//         $amount =  mysqli_real_escape_string($con, $addon['amount']);

//         $sql = "INSERT INTO bill_addons (bill_id, name, amount)
//                 VALUES('$bill_id','$name','$amount')";
//         mysqli_query($con,$sql);

//     }
// }



if(isset($_GET['bill_id'])) {
    $bill_id = mysqli_real_escape_string($con, $_GET['bill_id']);
    $result = mysqli_query($con, "SELECT id, name, amount FROM bill_addons WHERE bill_id='$bill_id'");

    $addons = [];
    while($row = mysqli_fetch_assoc($result)) {
        $addons[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($addons);
}