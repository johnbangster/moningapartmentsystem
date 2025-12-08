<?php
session_start();
require ('config/dbcon.php');
require ('config/code.php');
require ('includes/header.php');

if (!isset($_SESSION['auth_user']['user_id'])) {
    die("Access denied. Please log in as renter.");
}

$renter_id = $_SESSION['auth_user']['user_id'];

$exist = select("SELECT * FROM `users` WHERE `id`=? LIMIT 1",[$_SESSION['auth_user']['user_id']], 'i');

if(mysqli_num_rows($exist)==0){
    header("Location: index.php");
    exit(0);
}

$fetch = mysqli_fetch_assoc($exist);


//for view id

 $res = select("SELECT * FROM `renter_images` WHERE `renter_id`=?",[$_SESSION['auth_user']['user_id']], 'i');

$path = RENTERS_IMG_PATH;


// $picture = select("SELECT * FROM `renter_images` WHERE `renter_id` = ?", [$_SESSION['auth_user']['user_id']], 'i');

// if(mysqli_num_rows($picture)==0){
//     header("Location: index.php");
//     exit(0);
// }

// $fetch_picx = mysqli_fetch_assoc($picture);


?> 

<div class="container-fluid px-4">
    <?php include('message.php'); ?>

    <div class="row">

        <div class="col-12 my-5 px-4">
        </div>

        <div class="col-12 mb-5 px-4">
            <h2 class="fw-bold">PROFILE</h2>
            <div style="font-size: 14px;">
                <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                <span class="text-secondary"> > </span>
                <a href="#" class="text-secondary text-decoration-none">PROFILE</a>
            </div>
        </div>

        <div class="col-12 mb-5 px-4">
            <div class="bg-white p-3 p-md-4 rounded shadow-sm">
                <form id="info-form">
                    <h5 class="mb-3">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo $fetch['first_name']; ?>" class="form-control shadow-none" >
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="<?php echo $fetch['middle_name']; ?>" class="form-control shadow-none" >
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo $fetch['last_name']; ?>" class="form-control shadow-none" >
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="number" id="contact" name="contact" value="<?php echo $fetch['contact']; ?>" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo $fetch['email']; ?>" class="form-control shadow-none" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label" style="font-weight: 500;">Address</label>
                            <textarea id="address" name="address" class="form-control shadow-none" rows="1" required ><?php echo $fetch['address']; ?></textarea>
                        </div>
                        
                    </div>
                    <!-- <button type="submit" name="save_changes" class="btn text-white custom-bg shadow-none">Save Changes</button> -->

                </form>
            </div>
        </div>

        <!-- <div class="col-md-4 mb-5 px-4">
            <div class="bg-white p-3 p-md-4 rounded shadow-sm">
                <form id="profile-form">
                    <h5 class="mb-3 fw-bold">Profile Picture</h5>
                    <img src="#" alt="profile" class="img-fluid mb-3">
                    <label class="form-label fw-bold">New ID</label> -->
                    <!-- <input type="file" accept=".jpg, .jpeg" name="id" class="form-control shadow-none mb-4" required> 
                    <button type="submit" name="save_changes" class="btn text-white custom-bg shadow-none">Upload</button>
                </form> -->
            <!-- </div>
        </div>  -->

        <div class="col-md-8 mb-5 px-4">
            <div class="bg-white p-3 p-md-4 rounded shadow-sm">
                <form id="pass-form">
                    <h5 class="mb-3 fw-bold">Change Password</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" id="new_pass" name="new_pass" class="form-control shadow-none" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Confirm Password</label>
                            <input type="password" id="confirm_pass" name="confirm_pass" class="form-control shadow-none" >
                        </div>
                     </div>
                        <button type="submit" name="save_changes" class="btn text-white custom-bg shadow-none">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>

<script>
    let info_form = document.getElementById('info-form');

    info_form.addEventListener('submit', function(e){
        e.preventDefault();

        let data = new FormData();
        data.append('info_form','');
        data.append('first_name',info_form.elements['first_name'].value);
        data.append('middle_name',info_form.elements['middle_name'].value);
        data.append('last_name',info_form.elements['last_name'].value);
        data.append('contact',info_form.elements['contact'].value);
        data.append('email',info_form.elements['email'].value);
        data.append('address',info_form.elements['address'].value);

        let xhr = new XMLHttpRequest();
        xhr.open("POST","../function/profile.php",true);

        xhr.onload = function(){
            if(this.responseText == 'phone_already'){
                alert('error', "Phone number is already registered!");
            }
            else if(this.responseText == 0){
                alert('error', 'No Changes Made!');

            }else
            {
                alert('success', 'Changes Saved!');
            }
        }

        xhr.send(data);


    });


    // let profile_form = document.getElementById('profile-form');
    
    // profile_form.addEventListener('submit', function(e){
    //     e.preventDefault();

    //     let data = new FormData();
    //     data.append('profile_form','');
    //     data.append('image',profile_form.elements['image'].files[0]);
        

    //     let xhr = new XMLHttpRequest();
    //     xhr.open("POST","../function/profile.php",true);
        
    //     xhr.onload = function()
    //     {

    //         if(this.responseText == 'inv_img'){
    //             alert('error', 'Only JPG and JPEG images are allowed!', 'image-alert');
    //         }else if(this.responseText == 'upd_failed'){
    //             alert('error', 'Image upload failed. Server down!', 'image-alert');
    //         }else if(this.responseText == 0){
    //             alert('error', 'Id Failed to Update');
    //         }
    //         else{
    //             window.location.href=window.location.pathname;
    //         }
    //     }
    // });

    let pass_form = document.getElementById('pass-form');

    // Create a reusable SweetAlert2 Toast
    const Toast = Swal.mixin({   //Swal.mixin creates a reusable toast configuration.
        toast: true,        //toast: true → Makes it a small popup (like a notification).
        position: 'top-end', //position: 'top-end' → Appears at the top-right corner.
        showConfirmButton: false, //showConfirmButton: false → No OK button needed.
        timer: 2500,        //timer: 2500 → Toast disappears automatically after 2.5 seconds.
        timerProgressBar: true, //
    });

    //Attach submit event listener
    //Prevents the default form submission (page reload).
    //Allows us to handle the form via AJAX instead.
    pass_form.addEventListener('submit', function(e){
        e.preventDefault();

        //Get password values
        //Retrieves the values from the form fields:
            //new_pass → New password
            //confirm_pass → Confirm password
        let new_pass = pass_form.elements['new_pass'].value;
        let confirm_pass = pass_form.elements['confirm_pass'].value;

        //Validate password length
        //Ensures password is at least 6 characters long.
        //Shows an error toast if the check fails and stops execution (return).
        if (new_pass.length < 6) {
            Toast.fire({
                icon: 'error',
                title: 'Password must be at least 6 characters!'
            });
            return;
        }

        //Validate password match
        //Checks if new_pass and confirm_pass are identical.
        //Shows an error toast if they don’t match.
        if (new_pass !== confirm_pass) {
            Toast.fire({
                icon: 'error',
                title: 'Password does not match!'
            });
            return;
        }

        //Prepare data to send
        //Creates a FormData object to send via AJAX.
        //pass_form key signals the server that this is a password update request.
        //Includes the new password and confirmation password.
        let data = new FormData();
        data.append('pass_form', '');
        data.append('new_pass', new_pass);
        data.append('confirm_pass', confirm_pass);

        //Send AJAX request
        //Creates a new AJAX request to ajax/profile.php.
        //Uses POST method.
        //true → asynchronous request.
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/profile.php", true);

        //Handle server response
            //Trims server response to remove extra spaces.
            //Checks server response:
            //'mismatch' → Passwords didn’t match on the server side.
            //0 → Failed to update password (database error).
            //Any other value → Success.
        xhr.onload = function(){
            let response = this.responseText.trim();

            if (response === 'mismatch') {
                Toast.fire({
                    icon: 'error',
                    title: 'Password does not match (server)!'
                });
            }
            else if (response == 0) {
                Toast.fire({
                    icon: 'error',
                    title: 'Failed to update password!'
                });
            }
            else {
                Toast.fire({
                    icon: 'success',
                    title: 'Password updated successfully!'
                });
                pass_form.reset();
            }
        }

        xhr.send(data);//Send the data .Sends the FormData object to the server via AJAX. Server (profile.php) processes the request and returns a response (1, 0, or 'mismatch').
    });

    // working let pass_form = document.getElementById('pass-form');

    // pass_form.addEventListener('submit', function(e)
    // {
    //     e.preventDefault();

    //     let new_pass = pass_form.elements['new_pass'].value;
    //     let confirm_pass = pass_form.elements['confirm_pass'].value;

    //     if (new_pass.length < 6) {
    //         alert('error', 'Password must be at least 6 characters!');
    //         return;
    //     }

    //     if (new_pass !== confirm_pass) {
    //         alert('error', "Password does not match!");
    //         return;
    //     }

    //     let data = new FormData();
    //     data.append('pass_form', '');
    //     data.append('new_pass', new_pass);
    //     data.append('confirm_pass', confirm_pass);

    //     let xhr = new XMLHttpRequest();
    //     xhr.open("POST", "ajax/profile.php", true);

    //     xhr.onload = function(){
    //         if (this.responseText === 'mismatch') {
    //             alert('error', "Password does not match!");
    //         }
    //         else if (this.responseText == 0) {
    //             alert('error', "Failed to update password!");
    //         }
    //         else {
    //             alert('success', "Password updated successfully!");
    //             pass_form.reset();
    //         }
    //     }

    //     xhr.send(data);
    // });

    // let pass_form = document.getElementById('pass-form');

    // pass_form.addEventListener('submit', function(e){
    //     e.preventDefault();

    //     let new_pass = pass_form.elements['new_pass'].value;
    //     let confirm_pass = pass_form.elements['confirm_pass'].value;

    //     if(new_pass!=confirm_pass){
    //         alert('error', 'Password does not match!');
    //         return false;
    //     }

    //     let data = new FormData();
    //     data.append('pass_form','');
    //     data.append('new_pass', new_pass);
    //     data.append('confirm_pass', confirm_pass);

    //     let xhr = new XMLHttpRequest();
    //     xhr.open("POST","ajax/profile.php",true);
        
    //     xhr.onload = function()
    //     {

    //         if(this.responseText == 'mismatch'){
    //             alert('error', "Password does not match!");
    //         }else if(this.responseText == 0){
    //             alert('error', "Failed to update!");
    //         }else{
    //            alert('success', "Password Updated Successfully!");
    //            pass_form.reset();
    //         }
    //     }
    //     xhr.send(data);
    // });

</script>





