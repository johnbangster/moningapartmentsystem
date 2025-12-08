<?php

$host = "localhost";
$username ="root";
$password = "";
$dbname = "monings_db";

$con = mysqli_connect("$host", "$username", "$password", "$dbname");

if(!$con){

    header("Location: ../error/dberror.php");
    die();
}



// function filteration($data)
// {
//     foreach($data as $key => $value){
//         $value = trim($value);
//         $value = stripslashes($value);
//         $value = htmlspecialchars($value);
//         $value = strip_tags($value);
//         $data[$key] = $value;
//     }
//     return $data;
// }

// function selectAll($table)
// {
//     $con = $GLOBALS['con'];
//     $res = mysqli_query($con,"SELECT * FROM $table");
//     return $res;
// }

// function select($sql,$values,$datatypes)
// {
//     $con = $GLOBALS['con'];

//     if($stmt = mysqli_prepare($con,$sql))
//     {
//         mysqli_stmt_bind_param($stmt,$datatypes,...$values);
//         if(mysqli_stmt_execute($stmt))
//         {
//             $res = mysqli_stmt_get_result($stmt);
//             mysqli_stmt_close($stmt);
//             return $res;
//         }
//         else{
//             mysqli_stmt_close($stmt);
//             die("Query cannot be executed =  Select");
//         }
//     }
//     else{
//         die("Query cannot be prepared =  Select");
//     }
// }

// function update($sql,$values,$datatypes)
// {
//     $con = $GLOBALS['con'];

//     if($stmt = mysqli_prepare($con,$sql))
//     {
//         mysqli_stmt_bind_param($stmt,$datatypes,...$values);
//         if(mysqli_stmt_execute($stmt)){
//             $res = mysqli_stmt_affected_rows($stmt);
//             mysqli_stmt_close($stmt);
//             return $res;
//         }
//         else{
//             mysqli_stmt_close($stmt);
//             die("Query cannot be executed = Update");
//         }
//     }
//     else{
//         die("Query cannot be prepared = Update");
//     }
// }

// function insert($sql,$values,$datatypes)
// {
//     $con = $GLOBALS['con'];
//     if($stmt = mysqli_prepare($con,$sql))
//     {
//         mysqli_stmt_bind_param($stmt,$datatypes,...$values);
//         if(mysqli_stmt_execute($stmt)){
//             $res = mysqli_stmt_affected_rows($stmt);
//             mysqli_stmt_close($stmt);
//             return $res;
//         }
//         else{
//             mysqli_stmt_close($stmt);
//             die("Query cannot be executed = Insert");
//         }
//     }
//     else{
//         die("Query cannot be prepared = Insert");
//     }
// }

// function delete($sql,$values,$datatypes)
// {
//     $con = $GLOBALS['con'];

//     if($stmt = mysqli_prepare($con,$sql))
//     {
//         mysqli_stmt_bind_param($stmt,$datatypes,...$values);
//         if(mysqli_stmt_execute($stmt)){
//             $res = mysqli_stmt_affected_rows($stmt);
//             mysqli_stmt_close($stmt);
//             return $res;
//         }
//         else{
//             mysqli_stmt_close($stmt);
//             die("Query cannot be executed = Delete");
//         }
//     }
//     else{
//         die("Query cannot be prepared = Delete");
//     }
// }

// function sendAMessage($number, $message)
// {
//     $ch = curl_init();
//     $parameters = array(
//         'apikey' => '5fa6af02d001789f9ded2ed396fd3720',
//         'number' => $number,
//         'message' => $message,
//         'sendername' => 'Monings Rental',
//     );
//     curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
//     curl_setopt($ch, CURLOPT_POST, 1);

//     //Send the parameters set above with the request
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

//     // Receive response from server
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     $output = curl_exec($ch);
//     curl_close($ch);

//     //Show the server response
//     return $output;
// }

?>