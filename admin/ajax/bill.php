<?php

require('../config/dbcon.php');

if(isset($_POST['get_all_bills']))
   {
        $res = selectAll('bills');//this code is to delete unit in dashboard. Ommitting this code "delete button" whre unable o delete unit 
        $i=1;

        $data = "";

        while($row = mysqli_fetch_assoc($res))
        {
            // Get unit name separately
            $unit_name = "N/A";
            if(!empty($row['id'])) {
                $unit_res = mysqli_query($con, "SELECT name FROM units WHERE id = '".mysqli_real_escape_string($con, $row['id'])."'");
                if($unit_res && mysqli_num_rows($unit_res) > 0) {
                    $unit_row = mysqli_fetch_assoc($unit_res);
                    $unit_name = $unit_row['name'];
                }
            }

            if($row['status' ] == 'unpaid'){
                $status = "<button onclick='toggleStatus($row[id],0)' class='btn btn-danger btn-sm shadow-none'>UNPAID</button>";
            }
            else{
                $status = "<button onclick='toggleStatus($row[id],1)' class='btn btn-success btn-sm shadow-none'>PAID</button>
                ";

            }

            $data.="
                <tr class='align-middle'>
                    <td>$row[reference_id]</td>
                    <td>$unit_name</td>
                    <td>$row[end_period]</td>
                    <td>$row[total_amount]</td>
                    <td>$status</td>

                    <td>
                        <button type='button' onclick='edit_details($row[id])' class='btn btn-primary shadow-none btn-sm'data-bs-toggle='modal'data-bs-target='#edit-unit'>
                            <i class='fa-solid fa-pen-to-square'></i> 
                        </button>
                        <button type='button' onclick='' class='btn btn-info shadow-none btn-sm'data-bs-toggle='modal'data-bs-target='#unit-images'>
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

     
   if(isset($_POST['toggleStatus']))
   {
    $frm_data = filteration($_POST);

    $q = "UPDATE `bills` SET `status`=? WHERE `id`=? ";
    $v = [$frm_data['value'],$frm_data['toggleStatus']];

    if(update($q,$v,'si'))
    {
        echo 1;
    }
    else{
        echo 0;
    }

   }
