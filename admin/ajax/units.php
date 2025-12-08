<?php
header('Content-Type: application/json');

    require('../config/dbcon.php');
    require('../config/code.php');
    date_default_timezone_set("Asia/Manila");

    
   if(isset($_POST['add_unit']))
   {
        $features = filteration(json_decode($_POST['features']));
        $facilities = filteration(json_decode($_POST['facilities']));

        $frm_data = filteration($_POST);
        $flag = 0;

        
        $q1 = "INSERT INTO `units`(`name`, `area`, `price`, `adult`, `children`, `description`, `unit_type_id`) VALUES (?,?,?,?,?,?,?)";
        $values = [$frm_data['name'], $frm_data['area'], $frm_data['price'], $frm_data['adult'], $frm_data['children'], $frm_data['desc'], $frm_data['unit_type_id']];

        if(insert($q1,$values,'siiiisi')){
            $flag = 1;
        }

        $unit_id = mysqli_insert_id($con);

        $q2 = "INSERT INTO `unit_facilities`(`unit_id`, `facilities_id`) VALUES (?,?)";
        
        if($stmt = mysqli_prepare($con,$q2))
        {
            foreach($facilities as $f){
                mysqli_stmt_bind_param($stmt,'ii',$unit_id,$f);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        else{
            $flag = 0;
            die('query cannot be prepared - insert');
        }

        $q3 = "INSERT INTO `unit_features` (`unit_id`, `features_id`) VALUES (?,?) ";
        if($stmt = mysqli_prepare($con,$q3))
        {
            foreach($features as $f){
                mysqli_stmt_bind_param($stmt,'ii',$unit_id,$f);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        else{
            $flag = 0;
            die('query cannot be prepared - insert');
        }

        if($flag){
            echo 1;
        }
        else{
            echo 0;
        }
   }

   if(isset($_POST['get_all_unit']))
   {
        // $res = select("SELECT u.*, ut.type_name FROM units u 
        // JOIN unit_type ut ON u.unit_type_id = ut.id WHERE `removed`=?",[0],'i');//this code is to delete unit in dashboard. Ommitting this code "delete button" whre unable o delete unit 
        $res = select("
            SELECT 
                u.*, 
                ut.type_name, 
                b.address AS branch_address 
            FROM units u
            JOIN unit_type ut ON u.unit_type_id = ut.id
            JOIN branch b ON u.branch_id = b.id
            WHERE u.removed = ?
        ", [0], 'i');

        $i=1;

        $data = "";

        while($row = mysqli_fetch_assoc($res))
        {
            $badge_color = match ($row['status']) {
            "Available" => "success",
            "Occupied" => "danger",
            "Under Maintenance" => "warning",
            default => "secondary",
            };

            $status_badge = "<span class='badge bg-{$badge_color}'>$row[status]</span>";


            // if($row['status']==1){
            //     $status = "<button onclick='toggleStatus($row[id],0)' class='btn btn-dark btn-sm shadow-none'>Active</button>";
            // }
            // else{
            //     $status = "<button onclick='toggleStatus($row[id],1)' class='btn btn-warning btn-sm shadow-none'>Inactive</button>
            //     ";

            // }

            $data.="
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>$row[name]</td>
                    <td>$row[branch_address]</td>
                    <td>{$row['type_name']}</td>
                    <td>$status_badge</td>
                    <td>
                        <button type='button' class='btn btn-primary shadow-none btn-sm editUnitBtn' data-id='{$row['id']}'>
                            <i class='fa-solid fa-pen-to-square'></i>
                        </button>
                        <button type='button' onclick=\"unit_images($row[id],'$row[name]')\" class='btn btn-info shadow-none btn-sm'data-bs-toggle='modal'data-bs-target='#unit-images'>
                            <i class='fa-solid fa-images'></i> 
                        </button>
                        <button type='button' onclick='remove_unit($row[id])' class='btn btn-danger shadow-none btn-sm'>
                            <i class='fa-solid fa-trash-can'></i>
                        </button>
                    </td>
                </tr>
            ";
            $i++;
        }

        echo $data;
   }

   if(isset($_POST['get_unit']))
   {
        $frm_data =  filteration($_POST);

        $res1 = select("SELECT * FROM `units` WHERE `id`=?", [$frm_data['get_unit']],'i');
        $res2 = select("SELECT * FROM `unit_features` WHERE `unit_id`=?", [$frm_data['get_unit']],'i');
        $res3 = select("SELECT * FROM `unit_facilities` WHERE `unit_id`=?", [$frm_data['get_unit']],'i');

        $unitData = mysqli_fetch_assoc($res1);
        $features = [];
        $facilities = [];

        while ($row = mysqli_fetch_assoc($res2)) {

            $features[] = $row['features_id'];
        }
        while ($row = mysqli_fetch_assoc($res3)) {
            $facilities[] = $row['facilities_id'];
        }
        
        echo json_encode(["unitData" => $unitData, "features" => $features, "facilities" => $facilities]);
    
        if(mysqli_num_rows($res2)>0)
        {
            while($row = mysqli_fetch_assoc($res2)){
                array_push($features,$row['features_id']);
            }
        }

        if(mysqli_num_rows($res3)>0)
        {
            while($row = mysqli_fetch_assoc($res3)){
                array_push($facilities,$row['facilities_id']);
            }
        }

        $data = ["unitData" => $unitData, "features" => $features, "facilities" => $facilities];

        $data = json_encode($data);

        echo $data;
   }
   
   
    if(isset($_POST['edit_unit']))
   {
        $features = filteration(json_decode($_POST['features']));
        $facilities = filteration(json_decode($_POST['facilities']));

        $frm_data = filteration($_POST);
        $flag = 0;

        $q1 = "UPDATE `units` SET `name`=?,`area`=?,`price`=?,`adult`=?,`children`=?,`description`=? WHERE `id`=?";     
        $values = [$frm_data['name'],$frm_data['area'],$frm_data['price'],$frm_data['qty'],$frm_data['adult'],$frm_data['children'],$frm_data['desc'],$frm_data['unit_id_type']];

        if(update($q1, $values, 'siiiisi')){
            $flag = 1;
        }

        $del_features = delete("DELETE FROM `unit_features` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');
        $del_facilities = delete("DELETE FROM `unit_facilities` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');

        if(!($del_facilities && $del_features)){
            $flag = 0;
        }

        $q2 = "INSERT INTO `unit_facilities` (`unit_id`, `facilities_id`) VALUES (?,?)";
        if($stmt = mysqli_prepare($con,$q2))
        {
            foreach($facilities as $f){
                mysqli_stmt_bind_param($stmt,'ii',$frm_data['unit_id'],$f);
                mysqli_stmt_execute($stmt);
            }
            $flag = 1;
            mysqli_stmt_close($stmt);
        }
        else{
            $flag = 0;
            die('query cannot be prepared - insert');
        }

        $q3 = "INSERT INTO `unit_features` (`unit_id`, `features_id`) VALUES (?,?) ";
        if($stmt = mysqli_prepare($con,$q3))
        {
            foreach($features as $f){
                mysqli_stmt_bind_param($stmt,'ii',$frm_data['unit_id'],$f);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        else{
            $flag = 0;
            die('query cannot be prepared - insert');
        }


        if($flag){
            echo 1;
        }
        else{
            echo 0;
        }
   }


    if(isset($_POST['add_image']))
   {
        $frm_data = filteration($_POST);

        $img_r = uploadImage($_FILES['image'],UNITS_FOLDER);

        if($img_r == 'inv_img'){
            echo $img_r;
        }
        else if($img_r == 'inv_size'){
            echo $img_r;
        }
        else if($img_r == 'upd_failed'){
            echo $img_r;
        }
        else{
            $q = "INSERT INTO `unit_images`( `unit_id`, `image`) VALUES  (?,?)";
            $values = [$frm_data['unit_id'],$img_r];
            $res = insert($q,$values,'is');
            echo $res;
        }   
   }

   if(isset($_POST['get_unit_images']))
   {
        $frm_data = filteration($_POST);
        $res = select("SELECT * FROM `unit_images` WHERE `unit_id`=?",[$frm_data['get_unit_images']],'i');

        $path = UNITS_IMG_PATH;

        while($row = mysqli_fetch_assoc($res))
        {
            if($row['thumb']==1){
                $thumb_btn = "<i class='fa-solid fa-check fa-lg text-light bg-success px-2 py-1 rounded fs-5'></i>";
            }
            else{
                $thumb_btn ="<button onclick='thumb_image($row[id],$row[unit_id])' class='btn btn-secondary shadow-none'>
                    <i class='fa-solid fa-check fa-lg'></i>
                </button>";
            }

            echo "
                <tr class='align-middle'>
                    <td><img src='$path$row[image]'class='img-fluid'></td>
                    <td>$thumb_btn</td>
                    <td>
                     <button onclick='rem_image($row[id],$row[unit_id])' class='btn btn-danger shadow-none'>
                        <i class='fa-solid fa-trash'></i>
                     </button>
                    </td>
                </tr>
                
         ";
        }
   }

    if(isset($_POST['rem_image']))
    {
        $frm_data = filteration($_POST);
        $values = [$frm_data['image_id'],$frm_data['unit_id']];

        $pre_q = "SELECT * FROM `unit_images` WHERE `id`=? AND `unit_id`=?";
        $res = select($pre_q,$values,'ii');
        $img = mysqli_fetch_assoc($res);

        if(deleteImage($img['image'],UNITS_FOLDER))
        {
            $q = "DELETE FROM `unit_images` WHERE `id`=? AND `unit_id`=?";
            $res = delete($q,$values,'ii');
            echo $res;
        }
        else{
            echo 0;
        }
    }

    if(isset($_POST['thumb_image']))
    {
        $frm_data = filteration($_POST);

        $pre_q = "UPDATE `unit_images` SET `thumb`=? WHERE `id`=?";
        $pre_v = [0,$frm_data['unit_id']];
        $pre_res = update($pre_q,$pre_v,'ii');

        $q = "UPDATE `unit_images` SET `thumb`=? WHERE `id`=? AND `unit_id`=? ";
        $v = [1,$frm_data['image_id'],$frm_data['unit_id']];
        $res = update($q,$v,'iii');

        echo $res;
    }

    if(isset($_POST['remove_unit']))
    {
        $frm_data = filteration($_POST);

        $res1 = select("SELECT * FROM `unit_images` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');
        
        while($row = mysqli_fetch_assoc($res1)){
            deleteImage($row['image'],UNITS_FOLDER);
        }

        $res2 = delete("DELETE FROM `unit_images` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');
        $res3 = delete("DELETE FROM `unit_features` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');
        $res4 = delete("DELETE FROM `unit_facilities` WHERE `unit_id`=?",[$frm_data['unit_id']],'i');
        $res5 = update("UPDATE `units` SET `removed`=? WHERE `id`=?",[1,$frm_data['unit_id']],'ii');
        
        if($res2 || $res3 || $res4 || $res5){
            echo 1;
        }
        else
        {
            echo 0;
        }
     
    }
    
   if(isset($_POST['toggleStatus']))
   {
    $frm_data = filteration($_POST);

    $q = "UPDATE `units` SET `status`=? WHERE `id`=? ";
    $v = [$frm_data['value'],$frm_data['toggleStatus']];

    if(update($q,$v,'ii'))
    {
        echo 1;
    }
    else{
        echo 0;
    }

   }

   //for filter unit in index

   if(isset($_GET['fetch_units']))
   {
        $chk_avail = json_decode($_GET['chk_avail'],true);

        if($chk_avail['movein']!='')
        {
            $today_date = new DateTime(datetime: date("Y-m-d"));
            $movein_date  = new DateTime(datetime: date($chk_avail['movein']));
            // $moveout_date  = new DateTime(datetime: date($chk_avail['moveout']));

            if($movein_date == $today_date){
               echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }
            else if($today_date < $movein_date){
                echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }else if($movein_date > $today_date){
                echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }

        }

        //guests data code
        $guests = json_decode($_GET['guests'],true);
        $adults = ($guests['adults']!='') ? $guests['adults'] : 0;
        $children = ($guests['children']!='') ? $guests['children'] : 0;

        //facilities data decode
        $facility_list = json_decode($_GET['facility_list'],true);

        //count no units and ouput variable tot store unit cards 
        $count_units = 0;
        $output = "";

        //fectching settings table to check wesite is not shutdown
        $settings_q = "SELECT * FROM `settings` WHERE `id`=1";
        $settings_r = mysqli_fetch_assoc(mysqli_query($con, $settings_q));

        //query for units cards WITH GUESTS FILTER
        $unit_res = select("SELECT * FROM `units` WHERE `adult`>=? AND `children`>=? AND `status`=? AND `removed`=?",[$adults,$children,1,0],'iiii');  

        while($unit_data = mysqli_fetch_assoc($unit_res))
        {
            
            //check availability
            if($chk_avail['movein']!='')
            {
                $today_date = new DateTime(datetime: date("Y-m-d"));
                $movein_date  = new DateTime(datetime: date($chk_avail['movein']));
                // $moveout_date  = new DateTime(datetime: date($chk_avail['moveout']));

            if($movein_date == $today_date){
               echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }
            else if($today_date < $movein_date){
                echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }else if($movein_date > $today_date){
                echo"<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
               exit;
            }
            }

             //get facilities of unit

            $fac_count = 0;

            $fac_q = mysqli_query($con,"SELECT f.name, f.id FROM `facilities` f 
                INNER JOIN `unit_facilities` ufac ON f.id = ufac.facilities_id 
                WHERE ufac.unit_id = '$unit_data[id]' ");

            $facilities_data = "";
            while($fac_row = mysqli_fetch_assoc($fac_q))
            {   
                if(in_array(($fac_row['id']),$facility_list['facilities']) ){
                    $fac_count++;

                }

                $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                    $fac_row[name]
                </span>";
            }

            if(count($facility_list['facilities'])!=$fac_count){
                continue;
            }



            //get features of unit

            $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
                INNER JOIN `unit_features`ufea ON f.id = ufea.features_id 
                WHERE ufea.unit_id = '$unit_data[id]'");

            $features_data = "";
            while($fea_row = mysqli_fetch_assoc($fea_q)){
                $features_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                    $fea_row[name]
                </span>";
            }
            
            //get facilities of unit
            $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                INNER JOIN `unit_facilities` ufac ON f.id = ufac.facilities_id 
                WHERE ufac.unit_id = '$unit_data[id]' ");
            $facilities_data = "";
            while($fac_row = mysqli_fetch_assoc($fac_q)){
                $facilities_data .="<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>
                    $fac_row[name]
                </span>";
            }

            //get thumbnail of unit
            $unit_thumb = UNITS_IMG_PATH."Thumbnails.jpg";//this serve as default picture if thumbnail deleted
            $thumb_q = mysqli_query($con,"SELECT * FROM `unit_images` 
                WHERE `unit_id`='$unit_data[id]' 
                AND `thumb`='1'");

            if(mysqli_num_rows($thumb_q)>0){
                $thumb_res = mysqli_fetch_assoc($thumb_q);
                $unit_thumb = UNITS_IMG_PATH.$thumb_res['image'];
            }
            $formatted_price = number_format($unit_data['price'], 2);
            $book_btn = "";

            if(!$settings_r['shutdown']){
                $book_btn ="<a href='unit_details.php?id=$unit_data[id]' class='btn btn-sm text-white w-100 mb-2 custom-bg shadow-none'>Book Now</a>";
                // $reserve_btn ="<a href='reservation.php?id=$unit_data[id]' class='btn btn-info  btn-sm text-dark w-100 mb-2 shadow-none'>Reserve Now</a>";

            }
            //print unit card
            $output.="      
                <div class='card mb-4 border-0 shadow'>
                    <div class='row g-0 p-3 align-items-center'>
                        <div class='col-md-5 mb-lg-0 mb-md-0 mb-3'>
                            <img src='$unit_thumb' class='img-fluid rounded' style='height: 250px;'>
                        </div>
                        <div class='col-md-5 px-lg-3 px-md-3 px-0'>
                            <h5 class='mb-3'>$unit_data[name]</h5>
                            <div class='features mb-3'>
                                <h6 class='mb-1'>Features</h6>
                                $features_data
                            </div>
                            <div class='facilities mb-3'>
                                <h6 class='mb-1'>Facilities</h6>
                                $facilities_data
                            </div>
                            <div class='guests'>
                                <h6 class='mb-1'>Capacity</h6>
                                <span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                                    $unit_data[adult] Adults
                                </span>
                                <span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                                    $unit_data[children] Children
                                </span>
                            </div>
                        </div>
                        <div class='col-md-2 text-center'>
                            <h6 class='mb-4'> ₱$unit_data[price] /Monthly</h6>
                            $book_btn
                            <a href='unit_details.php?id=$unit_data[id]'' class='btn btn-sm w-100 btn-outline-dark shadow-none'>More Details</a>
                        </div>
                    </div>
                </div>
            ";

            $count_units++;
        }

        if($count_units>0){
            echo $output;
        }
        else{
            echo"<h3 class='text-center text-danger'>No units to show</h3>";
        }
    }

    
?>