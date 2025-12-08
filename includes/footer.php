   <!-- footer-->
    <?php 
        $contact_q = "SELECT * FROM `contact_details` WHERE `id`=? ";
        $settings_q = "SELECT * FROM `settings` WHERE `id`=?";
        $values = [1];
        $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
        $settings_r = mysqli_fetch_assoc(select($settings_q,$values,'i'));
    ?>
   <div class="container-fluid bg-white mt-5">
        <div class="row">
            <div class="col-lg-4">
                <h3 class="h-font fw-bold fs-3 mb-2"><?php echo $settings_r['site_title']; ?></h3>
                <p>
                    <?php echo $settings_r['site_about']; ?>
                </p>
            </div>
            <div class="col-lg-4 p-4">
                <h5 class="mb-3">Links</h5>
                <a href="index.php" class="d-inline-block mb-2 text-dark text-decoration-none">Home</a> <br>
                <!-- <a href="facilities.php" class="d-inline-block mb-2 text-dark text-decoration-none">Facilities</a> <br> -->
                <!-- <a href="units.php" class="d-inline-block mb-2 text-dark text-decoration-none">Units</a> <br> -->
                <a href="contact.php" class="d-inline-block mb-2 text-dark text-decoration-none">Contact us</a> <br>
                <a href="about.php" class="d-inline-block mb-2 text-dark text-decoration-none">About us</a>
            </div>
            <!-- <div class="col-lg-4 p-4">
                <h5 class="mb-3">Follow us</h5>
                <a href="<?php echo $contact_r['fb']; ?>" class="d-inline-block text-dark text-decoration-none mb-2"><i class="bi bi-facebook me-1"></i> Facebook</a>
                <br>
                <a href="<?php echo $contact_r['msgr']; ?>" class="d-inline-block text-dark text-decoration-none mb-2"><i class="bi bi-messenger me-1"></i> Messenger</a>
            </div> -->
        </div>
    </div>
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    

    <!--this js code is set to active link-->
    <!-- <script>
        function setActive()
        {
            let navbar = document.getElementById('nav-bar');
            let a_tags = navbar.getElementsByTagName('a');

            for(i=0; i<a_tags.length; i++)
            {
                let file = a_tags[i].href.split('/').pop();
                let file_name = file.split('.')[0];

                if(document.location.href.indexOf(file_name) >= 0){
                    a_tags[i].classList.add('active');
                }
            }
        }

        setActive(); -->

         <!-- setTimeout(remAlert,2000);
        }

        function remAlert()
        {
            document.getElementsByClassName('alert')[0].remove();
        } -->

    </script>