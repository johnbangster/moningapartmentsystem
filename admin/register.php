
<?php

session_start();
include('authentication.php');
require ('config/code.php');
require ('includes/header.php');
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">Create /Add User</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol> 

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="text-end mb-4">
                    <?php if($_SESSION['auth_role'] == 'admin') : ?>
                        <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-user">
                            <i class="fa-regular fa-square-plus"></i> Add
                        </button>
                    <?php endif; ?>
                    <div class="row" id="team-data">
                    </div>
                </div>  

                <div class="table-responsive-lg" style="height:450px; overflow-y: scroll;">
                    <table class="table table-hover border text-center">
                        <thead>
                            <tr class="bg-dark text-light">
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Role</th>
                            <?php if($_SESSION['auth_role'] == 'admin') : ?>
                                <th scope="col">Action</th>
                            <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody id="renter-data">
                            <?php
                                $result  = selectAll('users');
                                $i = 1;
                            
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                            echo "<td>{$i}</td>";
                                            echo "<td>
                                                    <span class='badge rounded-pill bg-light text-dark'>
                                                        First Name: $row[first_name]
                                                    </span><br>
                                                    <span class='badge rounded-pill bg-light text-dark'>
                                                            Last Name: $row[last_name]
                                                        </span><br>
                                                        <span class='badge rounded-pill bg-light text-dark'>
                                                            Middle Name: $row[middle_name]
                                                        </span><br>
                                                    </td>";
                                            echo "<td>{$row['contact']}</td>";
                                            echo "<td>{$row['email']}</td>";
                                            echo "<td>{$row['status']}</td>";
                                            echo "<td>{$row['role']}</td>";
                                            echo "<td>";
                                            
                                            if ($_SESSION['auth_role'] == 'admin') {
                                                        echo "<button class='btn btn-primary ' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'> 
                                                            <i class='fa-solid fa-pen-to-square'></i> 
                                                        </button>";
                                                        echo "<button type='button'  onclick=\"renter_images({$row['id']}, '{$row['first_name']} {$row['last_name']}')\" class='btn btn-info shadow-none 'data-bs-toggle='modal'data-bs-target='#renter-images'>
                                                            <i class='fa-solid fa-images'></i> 
                                                        </button>";
                                                        echo "<button onclick='remove_renter($row[id])' class='btn btn-danger'>
                                                            <i class='fa-solid fa-trash-can'></i>
                                                        </button>";
                                                
                                            include('modals/edit_renter_modal.php');
                                    }
                                    echo "</td></tr>";          
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

<!--add user Modal -->
<div class="modal fade" id="add-user" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="registercode.php" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel"><i class="fa-solid fa-house-circle-check"></i> Add User</h5>
                </div>

                <?php include('message.php'); ?>


                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">M.I</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="number" id="contact" name="contact" class="form-control shadow-none" required>
                        </div><div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" id="email" name="email" class="form-control shadow-none" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" style="font-weight: 500;">Address</label>
                            <textarea name="address" class="form-control shadow-none" rows="1" required ></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <select name="role" class="form-select shadow-none" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="employee">Employee</option>
                                <option value="renter">Renter</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" id="password" name="password" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Confirm Password</label>
                            <input type="cpass" id="cpass" name="cpass" class="form-control shadow-none" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="register_btn" class="btn custom-bg text-white shadow-none">ADD</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!--edit unit Modal -->
<!--manage unit image Modal -->



<script src="scripts/add_renter.js"></script>

<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>

<script>
    function validateForm() {
            var password = document.forms["regForm"]["password"].value;
            var confirm = document.forms["regForm"]["confirm_password"].value;
            var contact = document.forms["regForm"]["contact"].value;

            if (password !== confirm) {
                alert("Passwords do not match.");
                return false;
            }

            if (!/^\d{10,12}$/.test(contact)) {
                alert("Contact number must be 10 to 12 digits.");
                return false;
            }

            return true;
        }
</script>




   





