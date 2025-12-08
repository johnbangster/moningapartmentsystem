<?php
session_start();

require 'config/dbcon.php';

// function validate($inputData)
// {
//     global $con;

//     return mysqli_real_escape_string($con, $inputData);
// }

// function redirect($url, $status)
// {
//     $_SESSION[$status] = "Please fill all input fields";
//     header('Location:'.$url);
//     exit(0);
// }

// function getAll($tableName)
// {
//     global $con;

//     $table = validate( $tableName );

//     $query = "SELECT * FROM $table";
//     $result = mysqli_query($con,$query);
//     return $result;
// }

// function checkParamId($paramType){

//     if(isset($_GET[$paramType])){
//         if($_GET[$paramType] != null){
//             return $_GET[$paramType];
//         }else{
//             return 'No id given';
//         }
//     }else{
//         return 'No id given';
//     }
// }

// function getById($tableName, $id){

//     global $con;

//     $table = validate($tableName);
//     $id = validate($id);

//     $query = "SELECT * FROM $table WHERE id='$id' LIMIT 1";
//     $result = mysqli_query($con, $query);

//     if($result)
//     {
//         if(mysqli_num_rows($result) ==1 )
//         {
//             $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
//             $response = [
//             'status' => 200,
//             'message' => 'Fecthed Data',
//             'data' => $row
//             ];
//             return $response;

//         }else{
//             $response = [
//             'status' => 404,
//             'message' => 'No data record found'
//         ];
//         return $response;
//         }

//     }else{
//         $response = [
//             'status' => 500,
//             'message' => 'Something went wrong'
//         ];
//         return $response;
//     }
// }

?>