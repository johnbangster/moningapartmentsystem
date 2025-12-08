<?php
include('authentication.php');
require ('config/code.php');
require ('includes/header.php');

if(isset($_GET['seen']))
{
    $frm_data = filteration($_GET);

    if($frm_data['seen'] =='all'){
        $q = "UPDATE `user_query` SET `seen`=?";
        $values = [1];
        if(update($q,$values,'i')){
            alert('success', 'Marked all as read!');
        }
        else{
            alert('error', 'Something went wrong!');
        }

    }
    else{
        $q = "UPDATE `user_query` SET `seen`=? WHERE `id`=?";
        $values = [1,$frm_data['seen']];
        if(update($q,$values,'ii')){
            alert('success', 'Marked as read!');
        }
        else{
            alert('error', 'Something went wrong!');
        }
    }
}

if(isset($_GET['del']))
{
    $frm_data = filteration($_GET);

    if($frm_data['del'] =='all')
    {
        $q = "DELETE FROM `user_query`";
        if(mysqli_query($con,$q)){
            alert('success', 'Deleted all inquiries!');
        }
        else{
            alert('error', 'Something went wrong!');
        }
    }
    else{
        $q = "DELETE FROM `user_query` WHERE `id`=?";
        $values = [$frm_data['del']];
        if(delete($q,$values,'i')){
            alert('success', 'Data deleted!');
        }
        else{
            alert('error', 'Something went wrong!');
        }
    }
}
?> 


<div class="container-fluid px-4">
    <h1 class="mt-4">INQUIRIES</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol>

    <div class="row">
        
        <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <div class="text-end mb-4">
                    <a href="?seen=all" class="btn btn-dark rounded-pill shadow-none btn-sm">
                     <i class="fa-solid fa-check-double"></i> Mark all read
                    </a>
                    <a href="?del=all" class="btn btn-danger rounded-pill shadow-none btn-sm">
                     <i class="fa-regular fa-trash-can"></i> Remove all
                    </a>
                </div>
                
              <div class="table-responsive-md" style="height:250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead class="sticky-top table-dark text-center">
                            <tr class="text-light bg-dark">
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Contact</th>
                                <th scope="col" width="20%">Subject</th>
                                <th scope="col" width="30%">Message</th>
                                <th scope="col">Date</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $q = "SELECT * FROM `user_query` ORDER BY `id` DESC";
                                $data = mysqli_query($con,$q);
                                $i=1;

                                while($row = mysqli_fetch_assoc($data))
                                {
                                    $seen='';
                                    if($row['seen'] !=1){
                                        $seen = "<a href='?seen=$row[id]' class='btn btn-sm rounded-pill btn-primary'>Mark as read</a>  <br/>";
                                    }
                                    $seen .="<a href='?del=$row[id]' class='btn btn-sm rounded-pill btn-danger mt-2'>Remove</a>";
                                    echo<<<query
                                        <tr>
                                            <td>$i</td>
                                            <td>$row[name]</td>
                                            <td>$row[email]</td>
                                            <td>$row[contact]</td>
                                            <td>$row[subject]</td>
                                            <td>$row[message]</td>
                                            <td>$row[date]</td>
                                            <td>$seen</td>
                                        </tr>
                                    query;
                                     $i++;
                                }
                            ?>
                        </tbody>
                    </table>
              </div> 
            </div>

        </div>

    </div>
</div>


<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>





