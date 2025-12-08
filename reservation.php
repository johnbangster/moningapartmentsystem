
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?> - CONFIRM BOOKING</title>
    <link rel="stylesheet" href="css/custom.css">

    
    
</head>
<body class="bg-light">
    <?php
        /*
         check unit id from url is present or not .
         Shutdown mode is active or not . 
         User login or not
        */
        

        if(!isset($_GET['id']) || $settings_r['shutdown']==true){
         header("Location: units.php");
        }

        $data =  filteration($_GET);

        $unit_res = select("SELECT * FROM `units` WHERE `id`=? AND `status`=? AND `removed`=?",[$data['id'],1,0],'iii');

        // if(mysqli_num_rows($unit_res)==0){
        //   header("Location: confirm_booking.php");
        // }

        $unit_data = mysqli_fetch_assoc($unit_res);

        $_SESSION['unit'] = [
            "id" => $unit_data['id'],
            "name" => $unit_data['name'],
            "price" => $unit_data['price'],
            "payment" => null,
            "available" => false,

        ];

        //chapter20 review if users need login

       
    ?>

    <div class="container">
        <div class="row">

            <div class="col-12 my-5 mb-4 px-4">
                <h2 class="fw-bold">CONFIRM BOOKING</h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="units.php"  class="text-secondary text-decoration-none">UNITS</a>
                    <span class="text-secondary"> > </span>
                    <a href="confirm_booking.php"  class="text-secondary text-decoration-none">CONFIRM</a>
                </div>
            </div>
            <div class="col-lg-7 col-md-12 px-4">
               <?php

                    $unit_thumb = UNITS_IMG_PATH."Thumbnails.jpg";//this serve as default pictre if thumnail deleted
                    $thumb_q = mysqli_query($con,"SELECT * FROM `unit_images` 
                        WHERE `unit_id`='$unit_data[id]' 
                        AND `thumb`='1'");

                    if(mysqli_num_rows($thumb_q)>0){
                        $thumb_res = mysqli_fetch_assoc($thumb_q);
                        $unit_thumb = UNITS_IMG_PATH.$thumb_res['image'];
                    }

                    echo<<<data
                        <div class="cardd p-3 shadow-sm rounded">
                            <img src="$unit_thumb" class="img-fluid rounded mb-3">
                            <h5>$unit_data[name]</h5>
                            <h5>$unit_data[price] per month</h5>

                        </div>
                    data;
               ?>
            </div>
            
            <!--add "value" here if users login needed
             value="<?php echo $user_data['name']?>"
              -->
            <div class="col-lg-5 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <form id="booking_form">
                            <input type="hidden" name="unit_id" value="<?= $unit_data['id'] ?>">
                            <input type="hidden" name="payment_type" value="full_payment"> <!-- Default to full payment -->

                            <h6 class="mb-3">BOOKING DETAILS</h6>
                            <div class="row">
                                <div class="alert alert-dark" role="alert">
                                    NOTE: For reservation of units are only given 5 days to pay. Failed to do, 
                                     Our system automatically cancelled your reservation after five days(5) without prior info.
                                </div>

                                

                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input name="fname" type="text" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input name="lname" type="text" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input name="email" type="email" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone number</label>
                                    <input name="contact" type="text" class="form-control shadow-none">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Move-in</label>
                                    <input name="move_in" type="date" class="form-control shadow-none" required>
                                </div>
                                <div class="col-md-12"><br>
                                    <select name="move_out" id="move_out" class="form-select shadow-none" required disabled>
                                        <option value="">Select Move-in Date First</option>
                                    </select>
                                </div>
                                    <!-- <button type="button" class="btn btn-primary w-100" onclick="confirmReservation()">Reserve Now</button> -->
                                <div class="col-md-12">
                                    <button type="submit" name="send" class="btn btn-primary w-100 text-white custom-bg mt-2">RESERVE NOW</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <?php require('includes/footer.php'); ?>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= $success ?>',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'thank_you.php';
        });
    </script>
    <?php elseif ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: '<?= $error ?>',
            confirmButtonText: 'Try Again'
        });
    </script>
    <?php endif; ?>

    <script>
        document.querySelector('[name="move_in"]').addEventListener('change', function () {
        const moveInDate = new Date(this.value);
        const moveOutSelect = document.getElementById('move_out');

        if (!this.value || isNaN(moveInDate.getTime())) {
            moveOutSelect.innerHTML = '<option value="">Select Move-in Date First</option>';
            moveOutSelect.disabled = true;
            return;
        }

        // Function to format date to YYYY-MM-DD
        function formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        // Calculate +6 months and +12 months
        const sixMonths = new Date(moveInDate);
        sixMonths.setMonth(sixMonths.getMonth() + 6);

        const twelveMonths = new Date(moveInDate);
        twelveMonths.setMonth(twelveMonths.getMonth() + 12);

        // Update dropdown options
        moveOutSelect.innerHTML = `
            <option value="${formatDate(sixMonths)}">6 Months - ${formatDate(sixMonths)}</option>
            <option value="${formatDate(twelveMonths)}">12 Months - ${formatDate(twelveMonths)}</option>
        `;
        moveOutSelect.disabled = false;
        });

        function showToast(type, message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type, // 'success', 'error', 'info', 'warning'
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }


        document.querySelector('[name="move_in"]').addEventListener('change', function () {
        const moveIn = new Date(this.value);
        const moveOut = document.getElementById('move_out');
        if (!this.value || isNaN(moveIn.getTime())) {
            moveOut.innerHTML = '<option value="">Select move-in first</option>';
            moveOut.disabled = true;
            return;
        }

        const formatDate = d => d.toISOString().split('T')[0];

        const sixMonths = new Date(moveIn);
        sixMonths.setMonth(sixMonths.getMonth() + 6);
        const twelveMonths = new Date(moveIn);
        twelveMonths.setMonth(twelveMonths.getMonth() + 12);

        moveOut.innerHTML = `
            <option value="${formatDate(sixMonths)}">6 Months (${formatDate(sixMonths)})</option>
            <option value="${formatDate(twelveMonths)}">12 Months (${formatDate(twelveMonths)})</option>
        `;
        moveOut.disabled = false;
    });

    function confirmReservation() {
            Swal.fire({
                title: "Confirm Reservation?",
                text: "You’ll have 5 days to pay or it will be canceled.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, proceed",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("booking_form").submit();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            const moveInInput = document.querySelector('[name="move_in"]');
            const today = new Date().toISOString().split('T')[0];
            moveInInput.setAttribute("min", today);
        });
                    
    </script>
    
   

</body>
</html>
