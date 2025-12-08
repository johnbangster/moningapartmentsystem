<?php
    require('includes/header.php'); 
    require 'function_booking.php';


    // $movein_default ="";
    // $adult_default ="";
    // $children_default ="";

    // if(isset($_GET['check_availability']))
    // {
    //     $frm_data = filteration($_GET);

    //     $movein_default = $frm_data['movein'];
    //     $adult_default= $frm_data['adult'];
    //     $children_default= $frm_data['children'];


    // }
?>
<body class="bg-light">


    <!-- carousel -->
    <div class="container-fluid px-lg-4 mt-4">
         <div class="swiper swiper-container">
            <div class="swiper-wrapper">
                <?php
                    $res  = selectAll('carousel');
                    while($row = mysqli_fetch_assoc($res))
                    {
                        $path = CAROUSEL_IMG_PATH;
                        echo <<<data
                            <div class="swiper-slide">
                                <img src="$path$row[image]" class="mx-auto d-block" style="width:100%">
                            </div>
                        data;
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- checkAvailability -->
    <div class="container availability-form">
    <div class="row">
        <div class="col-lg-12 bg-white shadow p-4 rounded">
            <h5 class="mb-4">Check Booking Availability</h5>
            <form action="units.php">
                <div class="row align-items-end">
                    <div class="col-lg-4 mb-3">
                        <label class="form-label" style="font-weight: 500;" >Move-in Date</label>
                        <input type="date" class="form-control shadow-none" name="movein" required>
                    </div>
                    <!-- <div class="col-lg-3 mb-3">
                        <label class="form-label" style="font-weight: 500;" >Move-out Date</label>
                        <input type="date" class="form-control shadow-none">
                    </div> -->
                    <div class="col-lg-4 mb-3">
                        <label class="form-label" style="font-weight: 500;" >Adult</label>
                        <select class="form-select shadow-none" name="adult">
                            <?php 
                                $guests_query = mysqli_query($con, "SELECT MAX(adult) AS `max_adult`, MAX(children) AS `max_children` 
                                 FROM `units` WHERE `removed`='0'");
                                $guest_res = mysqli_fetch_assoc($guests_query);

                                for($i=1; $i<=$guest_res['max_adult']; $i++){
                                    echo"<option value='$i'>$i</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="form-label" style="font-weight: 500;" >Children</label>
                        <select class="form-select shadow-none" name="children">
                           <?php 
                                $guests_query = mysqli_query($con, "SELECT MAX(adult) AS `max_adult`, MAX(children) AS `max_children` 
                                 FROM `units` WHERE `removed`='0'");
                                $guest_res = mysqli_fetch_assoc($guests_query);

                                for($i=1; $i<=$guest_res['max_children']; $i++){
                                    echo"<option value='$i'>$i</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="check_availability">
                    <div class="col-lg-3 mb-lg-3 mt-2">
                        <button type="submit" class="btn text-white shadow-none custom-bg">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- units -->
    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">AVAILABLE UNITS</h2>
    <div class="container">
        <div class="row">

                <?php

                    $unit_res = select("SELECT * FROM `units` WHERE `status`=? AND `removed`=? ORDER BY `id` DESC LIMIT 3",[1,0],'ii');

                    while($unit_data = mysqli_fetch_assoc($unit_res))
                    {
                        //get features of unit

                        $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
                        INNER JOIN `unit_features`ufea ON f.id = ufea.features_id 
                        WHERE ufea.unit_id = '$unit_data[id]'");

                        $features_data = "";
                        while($fea_row = mysqli_fetch_assoc($fea_q))
                        {
                            $features_data .="<span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $fea_row[name]
                            </span>";
                        }
                        
                        //get facilities of unit

                        $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                            INNER JOIN `unit_facilities` ufac ON f.id = ufac.facilities_id 
                            WHERE ufac.unit_id = '$unit_data[id]' ");

                        $facilities_data = "";

                        while($fac_row = mysqli_fetch_assoc($fac_q))
                        {
                            $facilities_data .="<span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                            $fac_row[name]
                            </span>";
                        }

                        //get thumbnail of unit

                        $unit_thumb = UNITS_IMG_PATH."Thumbnails.jpg";//this serve as default pictre if thumnail deleted
                        $thumb_q = mysqli_query($con,"SELECT * FROM `unit_images` 
                            WHERE `unit_id`='$unit_data[id]' 
                            AND `thumb`='1'");

                            if(mysqli_num_rows($thumb_q)>0)
                            {
                                $thumb_res = mysqli_fetch_assoc($thumb_q);
                                $unit_thumb = UNITS_IMG_PATH.$thumb_res['image'];
                            }

                            $book_btn = "";
                            $formatted_price = number_format($unit_data['price'], 2);
                            if(!$settings_r['shutdown']){
                                $book_btn ="<a href='confirm_booking.php?id=$unit_data[id]' class='btn btn-sm text-white custom-bg shadow-none'>Book Now</a>";
                            }

                            echo <<<data
                                <div class="col-lg-4 col-md-6 my-3">
                                    <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                                        <img src="$unit_thumb" class="card-img-top">
                                        <div class="card-body">
                                            <h5>$unit_data[name]</h5>
                                            <h6 class="mb-4"> ₱ $formatted_price</h6>
                                            <div class="features mb-4">
                                                <h6 class="mb-1">Features</h6>
                                                $features_data
                                            </div>
                                            <div class="facilities mb-4">
                                                <h6 class="mb-1">Facilities</h6>
                                                $facilities_data
                                            </div>
                                            <div class="guests mb-4">
                                                <h6 class="mb-1">Capacity</h6>
                                                <span class="badge rounde-pill bg-light text-dark text-wrap me-1 mb-1">
                                                    $unit_data[adult] Adults
                                                </span>
                                                <span class="badge rounde-pill bg-light text-dark text-wrap me-1 mb-1">
                                                    $unit_data[children] Children
                                                </span>
                                            </div>
                                             <div class="status mb-4">
                                                <h6 class="mb-1">Status</h6>
                                                <span class="badge bg-info badge rounde-pill  text-light text-wrap me-1 mb-1">
                                                    $unit_data[status]
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-evenly mb-2">
                                                $book_btn
                                                <a href="units.php" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            data;

                    }

                ?>


        

            <!-- // ratings<div class="rating mb-4">
            //     <h6 class="mb-1">Rating</h6>
            //     <span class="badge rounde-pill bg-light">
            //         <i class="bi bi-star-fill text-warning"></i>
            //         <i class="bi bi-star-fill text-warning"></i>
            //         <i class="bi bi-star-fill text-warning"></i>
            //         <i class="bi bi-star-fill text-warning"></i>
            //     </span>
            // </div> -->
            <!-- <div class="col-lg-4 col-md-6 my-3">
                <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                    <img src="images/units/unit1.jpg" class="card-img-top">
                    <div class="card-body">
                        <h5>Room One</h5>
                        <h6 class="mb-4"> ₱4000/MONTHLY</h6>
                        <div class="features mb-4">
                            <h6 class="mb-1">Features</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                WIFI
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 AC
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 Bed
                            </span>

                        </div>
                        <div class="facilities mb-4">
                            <h6 class="mb-1">Facilities</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            24/7 CCTV
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                Lap Pool
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                GYM
                            </span>

                        </div>
                        <div class="guests mb-4">
                            <h6 class="mb-1">Guests</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Adults
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Children
                            </span>
                        </div>
                        <div class="rating mb-4">
                            <h6 class="mb-1">Rating</h6>
                            <span class="badge rounde-pill bg-light">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </span>
                        </div>
                        <div class="d-flex justify-content-evenly mb-2">
                            <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                            <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 my-3">
                <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                    <img src="images/units/unit1.jpg" class="card-img-top">
                    <div class="card-body">
                        <h5>Room One</h5>
                        <h6 class="mb-4">₱4000/MONTHLY</h6>
                        <div class="features mb-4">
                            <h6 class="mb-1">Features</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                WIFI
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 AC
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 Bed
                            </span>
                        </div>
                        <div class="facilities mb-4">
                            <h6 class="mb-1">Facilities</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            24/7 CCTV
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                Lap Pool
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                GYM
                            </span>
                        </div>
                        <div class="guests mb-4">
                            <h6 class="mb-1">Guests</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Adults
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Children
                            </span>
                        </div>
                        <div class="rating mb-4">
                            <h6 class="mb-1">Rating</h6>
                            <span class="badge rounde-pill bg-light">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </span>
                        </div>
                        <div class="d-flex justify-content-evenly mb-2">
                            <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                            <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 my-3">
                <div class="card border-0 shadow" style="max-width: 350px; margin: auto;">
                    <img src="images/units/unit1.jpg" class="card-img-top">
                    <div class="card-body">
                        <h5>Room One</h5>
                        <h6 class="mb-4">₱4000/MONTHLY</h6>
                        <div class="features mb-4">
                            <h6 class="mb-1">Features</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                WIFI
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 AC
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                1 Bed
                            </span>
                        </div>
                        <div class="facilities mb-4">
                            <h6 class="mb-1">Facilities</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            24/7 CCTV
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                Lap Pool
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                                GYM
                            </span>
                        </div>
                        <div class="guests mb-4">
                            <h6 class="mb-1">Guests</h6>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Adults
                            </span>
                            <span class="badge rounde-pill bg-light text-dark text-wrap">
                            2 Children
                            </span>
                        </div>
                        <div class="rating mb-4">
                            <h6 class="mb-1">Rating</h6>
                            <span class="badge rounde-pill bg-light">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </span>
                        </div>
                        <div class="d-flex justify-content-evenly mb-2">
                            <a href="#" class="btn btn-sm text-white custom-bg shadow-none">Book Now</a>
                            <a href="#" class="btn btn-sm btn-outline-dark shadow-none">More Details</a>
                        </div>
                    </div>
                </div>
            </div> -->
            
            <div class="col-lg-12 text-center mt-5">
                <a href="units.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">More Units >>></a>
            </div>

        </div>
    </div>

    <!-- Facilities -->
    <!-- <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">OUR FACILITIES</h2> -->

    <!-- <div class="container">
        <div class="row justify-content-evenly px-lg-0 px-md-0 px-5">

        <?php

            $res =  mysqli_query($con,"SELECT * FROM `facilities` ORDER BY id DESC LIMIT 5 ");
            $path = FEATURES_IMG_PATH;

            while($row = mysqli_fetch_assoc($res)){
                echo<<<data
                  <div class="col-lg-2 col-md-2 text-center bg-white rounded shadow py-4 my-3">
                    <img src="$path$row[icon]" width="80px">
                    <h5 class="mt-3">$row[name]</h5>
                  </div>
                data;
            }
            ?>
            <div class="col-lg-12 text-center mt-5">
                <a href="facilities.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">More Facilities >>></a>
            </div>
        </div>
    </div> -->

    <!-- TESTIMONIALS -->
    <!-- <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">TESTIMONIALS</h2>

    <div class="container mt-5">
        <div class="swiper swiper-testimony">
                <div class="swiper-wrapper mb-5">
                    <div class="swiper-slide bg-white p-4">
                        <div class="profile d-flex align-items-center mb-3">
                        <img src="images/features/wifi.jpg" width="30px;" />
                        <h6 class="m-0 ms-2">Mabolo</h6>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                            Facere in quisquam explicabo officiis repudiandae voluptatibus 
                            beatae, ullam atque. Maiores, alias.
                        </p>
                        <div class="rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                    <div class="swiper-slide bg-white p-4">
                        <div class="profile d-flex align-items-center p-4">
                        <img src="images/features/wifi.jpg" width="30px;" />
                        <h6 class="m-0 ms-2">Mabolo</h6>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                            Facere in quisquam explicabo officiis repudiandae voluptatibus 
                            beatae, ullam atque. Maiores, alias.
                        </p>
                        <div class="rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                    <div class="swiper-slide bg-white p-4">
                        <div class="profile d-flex align-items-center p-4">
                        <img src="images/features/wifi.jpg" width="30px;" />
                        <h6 class="m-0 ms-2">Mabolo</h6>
                        </div>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                            Facere in quisquam explicabo officiis repudiandae voluptatibus 
                            beatae, ullam atque. Maiores, alias.
                        </p>
                        <div class="rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                            <i class="bi bi-star-fill text-warning"></i>
                        </div>
                    </div>
                </div>
            <div class="swiper-pagination"></div>
        </div>
        <div class="col-lg-12 text-center mt-5">
            <a href="about.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">Know More>>></a>
        </div>
    </div> -->

    <!-- contact US -->
    <?php
        $contact_q = "SELECT * FROM `contact_details` WHERE `id`=? ";
        $values = [1];
        $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
    ?>
    <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">CONTACT US </h2>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-8 p-4 mb-lg-0 mb-3 bg-white rounded">
                <iframe class="w-100 rounded" height="320px" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3925.367357775188!2d123.90757757583513!3d10.31245676757873!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a9991792b3c98f%3A0x1a7b0a2683dca20e!2sMonings%20Apartment!5e0!3m2!1sen!2sph!4v1747467888205!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="bg-white p-4 rounded mb-4">
                    <h5>Call us</h5>
                    <a href="tel: +<?php echo $contact_r['pn1']; ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
                        <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn1']; ?>
                    </a><br>
                    <a href="tel: +<?php echo $contact_r['pn2']; ?>" class="d-inline-block mb-2 text-decoration-none text-dark">
                        <i class="bi bi-telephone-fill"></i> +<?php echo $contact_r['pn2']; ?>
                    </a>
                    <br>
                </div>
                <!-- <div class="bg-white p-4 rounded mb-4">
                    <h5>Follow us</h5>
                    <a href="<?php echo $contact_r['fb']; ?>" class="d-inline-block mb-3">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="bi bi-facebook me-1"></i> Facebook
                        </span> 
                    </a>
                    <br>
                    <a href="<?php echo $contact_r['msgr']; ?>" class="d-inline-block">
                        <span class="badge bg-light text-dark fs-6 p-2">
                            <i class="bi bi-messenger me-1"></i> Messenger
                        </span> 
                    </a>
                </div> -->
            </div>
        </div>
    </div>

    
<?php require('includes/footer.php'); ?>


    




