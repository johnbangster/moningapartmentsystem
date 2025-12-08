<?php
session_start();
require('../config/dbcon.php');
require('../config/code.php');

date_default_timezone_set("Asia/Manila");



if(isset($_POST['check_availability']))
{
  $frm_data = filteration($_POST);
  $status = "";
  $result = "";

  //check move-in & out validations

  $today_date = new DateTime(date("Y-m-d"));
  $movein_date = new DateTime($frm_data['move_in']);
  $moveout_date = new DateTime($frm_data['move_out']);

  if($movein_date == $moveout_date){
    $status = 'move_in_out_equal';
    $result = json_encode(["status"=>$status]);
  }
  else if($moveout_date <  $movein_date){
    $status = 'move_out_earlier';
    $result = json_encode(["status"=>$status]);
  }else if($movein_date < $today_date){
    $status = 'move_in_earlier';
    $result = json_encode(["status"=>$status]);
  }

  //check booking availability if status is blank else return the error

  if($status!=''){
    echo $result;
  }
  else{
    session_start();
    $_SESSION['unit'];
  

  // run query to checkunit is available or not

  $count_days = date_diff($movein_date,$moveout_date)->days;
  $payment = $_SESSION['unit']['price'] * $count_days;

  $_SESSION['unit']['payment'] = $payment;
  $_SESSION['unit']['available'] = true;

  $result = json_encode(["status"=>'available',"days"=>$count_days, "payment"=>$payment]);
  echo $result;

  }


}



?>