
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?>- CONTACT</title>
    <link rel="stylesheet" href="css/custom.css">


    <!-- <style>
        .pop:hover{
            border-top-color: var(--teal) !important;
            transform: scale(1.03);
            transition: all 0.3s;
        }
    </style> -->
</head>
<body class="bg-light">
    
    <?php require('includes/navbar.php'); ?>


    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">CONTACT US</h2>
            <div class="h-line bg-dark"></div>
            <p class="text-center mt-3">
                We’d love to hear from you! Please send us a message, 
                and we’ll get back to you as soon as we can. 
                
                Thank you for reaching out!
            </p>
        </div>
    </div>

    <?php
        $contact_q = "SELECT * FROM `contact_details` WHERE `id`=? ";
        $values = [1];
        $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    ?>

    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 mb-5 px-4">
                <div class="bg-white rounded shadow p-4">
                    <iframe class="w-100 rounded mb-4" height="320px" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3925.3674234401365!2d123.9101525!3d10.312451499999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a9991792b3c98f%3A0x1a7b0a2683dca20e!2sMonings%20Apartment!5e0!3m2!1sen!2sph!4v1746603296405!5m2!1sen!2sph" loading="lazy"></iframe>
                    
                    <h5>Address</h5>
                    <a href="<?php echo $contact_r['gmap']; ?>" target="_blank" class="d-inline-block text-decoration-none text-dark mb-2">
                        <i class="bi bi-geo-alt-fill"></i> <?php echo $contact_r['address']; ?>
                    </a>

                    <h5 class="mt-4">Call us</h5>
                    <a href="tel: +<?php echo $contact_r['pn1']; ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
                        <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn1']; ?>
                    </a>
                    <br>
                    <a href="tel: +<?php echo $contact_r['pn2']; ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
                        <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn2']; ?>
                    </a>       
                    
                    <h5 class="mt-4">Email</h5>
                    <a href="mailto: <?php echo $contact_r['email']; ?>" class="d-inline-block text-decoration-none text-dark">
                        <i class="bi bi-envelope-fill"></i> <?php echo $contact_r['email']; ?>
                    </a>

                    <!-- <h5 class="mt-4">Follow us</h5>
                    <a href="<?php echo $contact_r['fb']; ?>" class="d-inline-block mb-3 text-dark fs-5 me-2">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="bi bi-facebook me-1"></i>
                        </span> 
                    </a>
                    <a href="<?php echo $contact_r['msgr']; ?>" class="d-inline-blocktext-dark fs-5">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="bi bi-messenger me-1"></i> 
                        </span> 
                    </a> -->
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-5 px-4">
                <div class="bg-white rounded shadow p-4">
                    <form method="POST">
                        <h5>Send a message</h5>
                        <div class="mt-3">
                            <label class="form-label" style="font-weight: 500;">Name</label>
                            <input type="text" name="name" required class="form-control shadow-none">
                        </div>
                        <div class="mt-3">
                            <label class="form-label" style="font-weight: 500;">Email</label>
                            <input type="email" name="email" required class="form-control shadow-none">
                        </div>
                        <div class="mt-3">
                            <label class="form-label" style="font-weight: 500;">Contact Number</label>
                            <input type="number" name="contact" required class="form-control shadow-none">
                        </div>
                        <div class="mt-3">
                            <label class="form-label" style="font-weight: 500;">Subject</label>
                            <input type="text" name="subject" required class="form-control shadow-none">
                        </div>
                        <div class="mt-3">
                            <label class="form-label" style="font-weight: 500;">Message</label>
                            <textarea name="message" required class="form-control shadow-none" rows="5" style="resize: none;"></textarea>
                        </div>
                            <button type="submit" name="send" class="btn text-white custom-bg mt-3">SEND</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
        if(isset($_POST['send']))
        {
            $frm_data = filteration($_POST);

            $q = "INSERT INTO `user_query`(`name`, `email`, `contact`, `subject`, `message`) 
                    VALUES (?,?,?,?,?)";
            $values = [$frm_data['name'],$frm_data['email'],$frm_data['contact'],$frm_data['subject'],$frm_data['message']];

            $res = insert($q,$values,'ssiss');
            if($res==1)
            {
                alert('success', 'Message sent!');
            }
            else{
                alert('error', 'Server Down! Try again later.');
            }
        }
    ?>

    <?php require('includes/footer.php'); ?>


</body>
</html>



    




