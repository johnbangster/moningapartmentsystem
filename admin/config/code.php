<?php
// paypal integration
define('PAYPAL_ID', 'sb-tjeu4717141281@business.example.com'); 
define('PAYPAL_SANDBOX', TRUE); //TRUE or FALSE 
 
define('PAYPAL_RETURN_URL', 'http://www.example.com/success.php'); 
define('PAYPAL_CANCEL_URL', 'http://www.example.com/cancel.php'); 
define('PAYPAL_NOTIFY_URL', 'http://www.example.com/ipn.php'); 
define('PAYPAL_CURRENCY', 'USD'); 

//frontend purpose data
define('SITE_URL','http://127.0.0.1/moningsrental/');
define('ABOUT_IMG_PATH', SITE_URL.'images/about/');
define('CAROUSEL_IMG_PATH', SITE_URL.'images/carousel/');
define('FEATURES_IMG_PATH', SITE_URL.'images/features/');
define('UNITS_IMG_PATH', SITE_URL.'images/units/');
define('USERS_IMG_PATH', SITE_URL.'images/users/');
// define('RENTERS_IMG_PATH', SITE_URL . 'images/renters/'); // browser display

define('RENTERS_IMG_PATH', SITE_URL.'images/renters/');





//backend upload process needs this data
define('UPLOAD_IMAGE_PATH',$_SERVER['DOCUMENT_ROOT'].'/moningsrental/images/');
define('ABOUT_FOLDER','about/');
define('CAROUSEL_FOLDER','carousel/');
define('FEATURES_FOLDER','features/');
define('UNITS_FOLDER','units/');
define('USERS_FOLDER','users/');
// define('RENTERS_FOLDER', __DIR__ . '/images/renters/'); // filesystem

define('RENTERS_FOLDER','renters/');



function getCount($tableName)
{
     global $con; 

     $table = htmlspecialchars($tableName);

     $query = "SELECT * FROM $table";
     $query_run = mysqli_query($con, $query);

     if($query_run){

        $totalCount = mysqli_num_rows($query_run);
        return $totalCount;

     }else{
        return 'Something went wrong!';
     }
    
}

function uploadImage($image,$folder)
{
    $valid_mime = ['image/jpeg','image/jpg', 'image/png'];
    $img_mime = $image['type'];

    if(!in_array($img_mime,$valid_mime)){
        return 'inv_img'; //invalid image format
    }
    else if(($image['size']/(1024*1024))>2){
        return 'inv_size'; //invalid size greater than 2mb
    }
    else{
        $ext = pathinfo($image['name'],PATHINFO_EXTENSION);
        $rname = 'IMG_'.random_int(11111,99999).".$ext";
        
        $img_path = UPLOAD_IMAGE_PATH.$folder.$rname;
        if(move_uploaded_file($image['tmp_name'],$img_path)){
            return $rname;
        }
        else{
            return 'upd_failed';
        }

    }
}

function uploadSVGImage($image,$folder)
{
    $valid_mime = ['image/svg+xml'];
    $img_mime = $image['type'];

    if(!in_array($img_mime,$valid_mime)){
        return 'inv_img'; //invalid image format
    }
    else if(($image['size']/(1024*1024))>1){
        return 'inv_size'; //invalid size greater than 2mb
    }
    else{
        $ext = pathinfo($image['name'],PATHINFO_EXTENSION);
        $rname = 'IMG_'.random_int(11111,99999).".$ext";
        
        $img_path = UPLOAD_IMAGE_PATH.$folder.$rname;
        if(move_uploaded_file($image['tmp_name'],$img_path)){
            return $rname;
        }
        else{
            return 'upd_failed';
        }

    }
}

function deleteImage($image,$folder)
{
    if(unlink(UPLOAD_IMAGE_PATH.$folder.$image)){
        return true;
    }
    else{
        return false;
    }
}



// function toast($type, $msg) {
//     echo "<script>showToast('$type', `" . addslashes($msg) . "`);</script>";
// }


function alert($type,$msg)
{
    $bs_class = ($type == "success") ?  "alert-success" : "alert-danger";

    echo <<<alert
        <div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
            <strong class="me-3">$msg</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>"
    alert;
}



function filteration($data)
{
    foreach($data as $key => $value){
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        $value = strip_tags($value);
        $data[$key] = $value;
    }
    return $data;
}

function selectAll($table)
{
    $con = $GLOBALS['con'];
    $res = mysqli_query($con,"SELECT * FROM $table");
    return $res;
}

function select($sql,$values,$datatypes)
{
    $con = $GLOBALS['con'];

    if($stmt = mysqli_prepare($con,$sql))
    {
        mysqli_stmt_bind_param($stmt,$datatypes,...$values);
        if(mysqli_stmt_execute($stmt))
        {
            $res = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        }
        else{
            mysqli_stmt_close($stmt);
            die("Query cannot be executed =  Select");
        }
    }
    else{
        die("Query cannot be prepared =  Select");
    }
}

function update($sql,$values,$datatypes)
{
    $con = $GLOBALS['con'];

    if($stmt = mysqli_prepare($con,$sql))
    {
        mysqli_stmt_bind_param($stmt,$datatypes,...$values);
        if(mysqli_stmt_execute($stmt)){
            $res = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        }
        else{
            mysqli_stmt_close($stmt);
            die("Query cannot be executed = Update");
        }
    }
    else{
        die("Query cannot be prepared = Update");
    }
}

function insert($sql,$values,$datatypes)
{
    $con = $GLOBALS['con'];
    if($stmt = mysqli_prepare($con,$sql))
    {
        mysqli_stmt_bind_param($stmt,$datatypes,...$values);
        if(mysqli_stmt_execute($stmt)){
            $res = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        }
        else{
            mysqli_stmt_close($stmt);
            die("Query cannot be executed = Insert");
        }
    }
    else{
        die("Query cannot be prepared = Insert");
    }
}

function delete($sql,$values,$datatypes)
{
    $con = $GLOBALS['con'];

    if($stmt = mysqli_prepare($con,$sql))
    {
        mysqli_stmt_bind_param($stmt,$datatypes,...$values);
        if(mysqli_stmt_execute($stmt)){
            $res = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $res;
        }
        else{
            mysqli_stmt_close($stmt);
            die("Query cannot be executed = Delete");
        }
    }
    else{
        die("Query cannot be prepared = Delete");
    }
}

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



// function toast($type, $msg) {
//     echo "<script>showToast('$type', `" . addslashes($msg) . "`);</script>";
// }


// function alert($type,$msg)
// {
//     $bs_class = ($type == "success") ?  "alert-success" : "alert-danger";

//     echo <<<alert
//         <div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
//             <strong class="me-3">$msg</strong>
//             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//         </div>"
//     alert;
// // }
// function alert($type, $msg)
// {
//     $allowed = ['success', 'danger', 'warning', 'info', 'primary', 'secondary', 'dark', 'light'];
//     $bs_class = in_array($type, $allowed) ? "alert-$type" : "alert-info";

//     echo <<<alert
//         <div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
//             <strong class="me-3">$msg</strong>
//             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
//         </div>
//     alert;
// }










// if(isset($_POST['saveSetting']))
// {
//     $title = validate($_POST['site_title']);
//     $about = validate($_POST['site_about']);
//     // $shutdown validate($_POST['shutdown']) == true ? 1:0;

//     $settingId =  validate($_POST['settingId']);

//     if($settingId =='insert')
//     {
//         $query = "INSERT INTO `settings`( `site_title`, `site_about`) VALUES ($title, $about)";
//         $result = mysqli_query($con,$query);
//     }

//     if(is_numeric($settingId))
//     {
//         $query = "UPDATE settings SET site_title='$title', site_about='$about' WHERE id='$settingId' ";
//         $result = mysqli_query($con,$query);

//     }

//     if($result)
//     {
//         redirect('settings.php', 'Settings saved');
//     }else{
//         redirect('settings.php', 'Something went wrong');
//     }

// }

// function filteration($data)
// {
//     foreach($data as $key=> $value){
//         $data[$key] = trim($value);
//         $data[$key] = stripslashes($value);
//         $data[$key] = htmlspecialchars($value);
//         $data[$key] = strip_tags($value);
//     }
//     return $data;
// }

// function select($sql,$values,$datatypes)
// {
//     $con = $GLOBALS['con'];
//     if($stmt = mysqli_prepare($con,$sql)){
//         mysqli_stmt_bind_param($stmt,$datatypes,...$values);
//         if(mysqli_stmt_execute($stmt)){
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
//         die("Query cannot be prepapred =  Select");
//     }
// }

?>