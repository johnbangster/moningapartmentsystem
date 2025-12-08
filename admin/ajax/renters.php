<?php
    require('../config/dbcon.php');
    require('../config/code.php');



  if (isset($_POST['get_all_renter'])) 
    {
    $query = "
        SELECT 
            r.*, 
            u.name AS renter_name,
            ra.id AS agreement_id,
            ra.term_months,
            ra.start_date,
            ra.end_date,
            ra.monthly_rent,
            ra.deposit,
            ra.status AS agreement_status
        FROM renters r
        JOIN renters u ON r.renter_id = u.id
        LEFT JOIN rental_agreements ra 
            ON r.id = ra.renter_id AND r.renter_id = ra.renter_id
        ORDER BY r.created_at DESC
    ";

    $result = mysqli_query($con, $query);
    $i = 1;
    $data = "";

    while ($row = mysqli_fetch_assoc($result)) {
        $badge_color = match ($row['status']) {
            "Available" => "success",
            "Occupied" => "danger",
            "Under Maintenance" => "warning",
            default => "secondary",
        };

        $status_badge = "<span class='badge bg-{$badge_color}'>{$row['status']}</span>";

        $data .= "
            <tr class='align-middle'>
                <td>{$i}</td>
                <td>
                    <span class='badge rounded-pill bg-light text-dark'>
                        First Name: {$row['first_name']}
                    </span><br>
                    <span class='badge rounded-pill bg-light text-dark'>
                        Last Name: {$row['last_name']}
                    </span><br>
                    <span class='badge rounded-pill bg-light text-dark'>
                        Middle Name: {$row['middle_name']}
                    </span><br>
                </td>
                <td>{$row['contacts']}</td>                
                <td>{$row['email']}</td>
                <td>{$row['renter_name']}</td>
                <td>{$status_badge}</td>
                <td>
                    <button class='btn btn-primary shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>
                        <i class='fa-solid fa-pen-to-square'></i> 
                    </button>
                    <button type='button' onclick=\"renter_images({$row['id']}, '{$row['first_name']} {$row['last_name']}')\" class='btn btn-info shadow-none btn-sm' data-bs-toggle='modal' data-bs-target='#renter-images'>
                        <i class='fa-solid fa-images'></i> 
                    </button>
                    <button type='button' onclick='remove_renter({$row['id']})' class='btn btn-danger shadow-none btn-sm'>
                        <i class='fa-solid fa-trash-can'></i>
                    </button>
                </td>
            </tr>
        ";

        // Correct place to include modal PHP
        ob_start();
        include('../modals/edit_renter_modal.php');
        $data .= ob_get_clean();

        $i++;
    }

    echo $data;
  }

  if(isset($_POST['add_image']))
   {
        $frm_data = filteration($_POST);

        $img_r = uploadImage($_FILES['image'],RENTERS_FOLDER);

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
            $q = "INSERT INTO `renter_images`( `renter_id`, `image`) VALUES  (?,?)";
            $values = [$frm_data['renter_id'],$img_r];
            $res = insert($q,$values,'is');
            echo $res;
        }   
   }

   if(isset($_POST['get_renter_images']))
   {
        $frm_data = filteration($_POST);
        $res = select("SELECT * FROM `renter_images` WHERE `renter_id`=?",[$frm_data['get_renter_images']],'i');

        $path = RENTERS_IMG_PATH;

        while($row = mysqli_fetch_assoc($res))
        {
            // if($row['thumb']==1){
            //     $thumb_btn = "<i class='fa-solid fa-check fa-lg text-light bg-success px-2 py-1 rounded fs-5'></i>";
            // }
            

            echo "
                <tr class='align-middle'>
                    <td><img src='$path$row[image]'class='img-fluid'></td>
                    <td>
                     <button onclick='rem_image($row[id],$row[renter_id])' class='btn btn-danger shadow-none'>
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
        $values = [$frm_data['image_id'],$frm_data['renter_id']];

        $pre_q = "SELECT * FROM `renter_images` WHERE `id`=? AND `renter_id`=?";
        $res = select($pre_q,$values,'ii');
        $img = mysqli_fetch_assoc($res);

        if(deleteImage($img['image'],RENTERS_FOLDER))
        {
            $q = "DELETE FROM `renter_images` WHERE `id`=? AND `renter_id`=?";
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

        $pre_q = "UPDATE `renter_images` SET `thumb`=? WHERE `id`=?";
        $pre_v = [0,$frm_data['renter_id']];
        $pre_res = update($pre_q,$pre_v,'ii');

        $q = "UPDATE `renter_images` SET `thumb`=? WHERE `id`=? AND `renter_id`=? ";
        $v = [1,$frm_data['image_id'],$frm_data['renter_id']];
        $res = update($q,$v,'iii');

        echo $res;
    }

    if(isset($_POST['remove_renter']))
    {
        $frm_data = filteration($_POST);

        $res1 = select("SELECT * FROM `renter_images` WHERE `renter_id`=?",[$frm_data['renter_id']],'i');
        
        while($row = mysqli_fetch_assoc($res1)){
            deleteImage($row['image'],RENTERS_FOLDER);
        }

        $res2 = delete("DELETE FROM `renter_images` WHERE `renter_id`=?",[$frm_data['renter_id']],'i');
        // $res3 = delete("DELETE FROM `renter_features` WHERE `renter_id`=?",[$frm_data['renter_id']],'i');
        // $res4 = delete("DELETE FROM `renter_facilities` WHERE `renter_id`=?",[$frm_data['renter_id']],'i');
        $res3 = update("UPDATE `renters` SET `removed`=? WHERE `id`=?",[1,$frm_data['renter_id']],'ii');
        
        if($res2 || $res3){
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

    $q = "UPDATE `renters` SET `status`=? WHERE `id`=? ";
    $v = [$frm_data['value'],$frm_data['toggleStatus']];

    if(update($q,$v,'ii'))
    {
        echo 1;
    }
    else{
        echo 0;
    }

   }



// Utility: upload image
// function uploadImageRenter($file) {
//     $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
//     if (!in_array($file['type'], $allowed)) return 'inv_img';
//     if ($file['size'] > 2 * 1024 * 1024) return 'inv_size'; // 2MB

//     $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
//     $filename = uniqid('img_', true) . '.' . $ext;
//     $destination = RENTERS_FOLDER . $filename;

//     if (move_uploaded_file($file['tmp_name'], $destination)) {
//         return $filename;
//     }

//     return 'upd_failed';
// }


