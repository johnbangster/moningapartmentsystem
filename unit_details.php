
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?> - UNIT DETAILS</title>
    <link rel="stylesheet" href="css/custom.css">
   
</head>
<body class="bg-light">
    <?php
        if(!isset($_GET['id'])){
         header("Location: units.php");
        }

        $data =  filteration($_GET);

        $unit_res = select("SELECT * FROM `units` WHERE `id`=? AND `status`=? AND `removed`=?",[$data['id'],1,0],'iii');

        if(mysqli_num_rows($unit_res)==0){
          header("Location: confirm_booking.php");
        }

        $unit_data = mysqli_fetch_assoc($unit_res);
    ?>

    <div class="container">
        <div class="row">
            <div class="col-12 my-5 mb-4 px-4">
                <h2 class="fw-bold"><?php echo $unit_data['name']; ?></h2>
                <div style="font-size: 14px;">
                    <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
                    <span class="text-secondary"> > </span>
                    <a href="units.php"  class="text-secondary text-decoration-none">UNITS</a>
                </div>
            </div>
            <div class="col-lg-7 col-md-12 px-4">
                <div id="unitCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php

                            $unit_img = UNITS_IMG_PATH."Thumbnails.jpg";//this serve as default pictre if thumnail deleted
                            $img_q = mysqli_query($con,"SELECT * FROM `unit_images` 
                                         WHERE `unit_id`='$unit_data[id]'");

                            if(mysqli_num_rows($img_q)>0)
                            {   //to view active image but didn't add a thumbnail check
                                $active_class = 'active';
                                while($img_res = mysqli_fetch_assoc($img_q))
                                {
                                    echo"
                                        <div class='carousel-item $active_class'>
                                            <img src='".UNITS_IMG_PATH.$img_res['image']. "' class='d-block w-100 rounded'>
                                        </div>
                                    ";
                                    $active_class='';
                                }
                                
                            }
                            else{
                                echo"<div class='carousel-item active'>
                                    <img src='$unit_img' class='d-block w-100'>
                                </div>";
                            }
                        
                        ?>
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#unitCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#unitCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-lg-5 col-md-12 px-4">
                <div class="card mb-4 border-0 shadow-sm rounded-3">
                    <div class="card-body">

                        <?php
                            $formatted_price = number_format($unit_data['price'], 2); // Format price to 2 decimal places
                            echo <<<price
                                <h4 id="price" class="mb-4">₱$formatted_price/Monthly</h4>
                            price;
                         

                            $fea_q = mysqli_query($con,"SELECT f.name FROM `features` f 
                            INNER JOIN `unit_features`ufea ON f.id = ufea.features_id 
                            WHERE ufea.unit_id = '$unit_data[id]'");

                            $features_data = "";
                            while($fea_row = mysqli_fetch_assoc($fea_q)){
                                $features_data .="<span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                                $fea_row[name]
                                </span>";
                            }

                            echo<<<features
                                <div class="mb-3">
                                    <h6 class="mb-1">Features</h6>
                                    $features_data
                                </div>
                            features;

                            $fac_q = mysqli_query($con,"SELECT f.name FROM `facilities` f 
                             INNER JOIN `unit_facilities` ufac ON f.id = ufac.facilities_id 
                             WHERE ufac.unit_id = '$unit_data[id]' ");

                            $facilities_data = "";

                            while($fac_row = mysqli_fetch_assoc($fac_q)){
                                $facilities_data .="<span class='badge rounde-pill bg-light text-dark text-wrap me-1'>
                                $fac_row[name]
                                </span>";
                            }

                            echo<<<facilities
                                <div class="mb-3">
                                 <h6 class="mb-1">Facilities</h6>
                                  $facilities_data
                                </div>
                            facilities;

                            echo<<<guests
                                <div class="mb-3">
                                    <h6 class="mb-1">Capacity</h6>
                                    <span class="badge rounde-pill bg-light text-dark text-wrap">
                                        $unit_data[adult] Adults
                                    </span>
                                    <span class="badge rounde-pill bg-light text-dark text-wrap">
                                        $unit_data[children] Children
                                    </span>
                                </div>
                            guests;

                            echo<<<area
                                <div class="mb-3">
                                    <h6 class="mb-1">Area</h6>
                                    <span class='badge rounde-pill bg-light text-dark text-wrap me-1 mb-1'>
                                        $unit_data[area] sq. ft.
                                    </span>
                                </div>
                            area;

                            $book_btn = "";

                            if(!$settings_r['shutdown']){
                                $book_btn ="<a href='confirm_booking.php?id=$unit_data[id]' class='btn btn-sm text-white w-100 mb-2 custom-bg shadow-none'>Book Now</a>";
                                // $reserve_btn ="<a href='reservation.php?id=$unit_data[id]' class='btn btn-sm text-white w-100 mb-2 custom-bg shadow-none'>Reserve Now</a>";

                            }
                            echo<<<book
                                $book_btn
                            book;
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4 px-4">
                <div class="mb-5">
                    <h5>Description</h5>
                    <p>
                        <?php echo $unit_data['description']; ?>
                    </p>
                </div>

                <!-- </div>
                    <h5 class="mb-3">Reviews & Rating</h5>
                    <div>
                        <div class="d-flex align-items-center mb-2">
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
                </div> -->
            </div>
        </div>
    </div>
    
    <?php require('includes/footer.php'); ?>
    <script>
         // Safe number formatter
        function formatNumber(num) {
            return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Format inputs (monthlyRent & deposit)
        document.getElementById('price').addEventListener('input', function (event) {
            let value = event.target.value.replace(/[^0-9]/g,'');
            event.target.value = value ? formatNumber(value) : '';
        });
    </script>

</body>
</html>
