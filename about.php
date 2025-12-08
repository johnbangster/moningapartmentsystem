
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    

    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?>- About</title>
    <link rel="stylesheet" href="css/custom.css">

    <style>
        .box:hover{
            border-top-color: var(--teal) !important;
            transform: scale(1.03);
            transition: all 0.3s;
        }
    </style>
    
</head>
<body class="bg-light">

    <?php require('includes/navbar.php'); ?>

    <div class="my-5 px-4">
        <h2 class="fw-bold h-font text-center">ABOUT US</h2>
            <div class="h-line bg-dark"></div>
            <p class="text-center mt-3">
                
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-lg-6 col-md-5 mb-4 order-lg-1 order-md-1 order-2">
                <h3 class="mb-3">MONINGS RENTAL SERVICES</h3>
                <p>
                    Monings Place started in the 1970s with a friendly mission: 
                    to provide quality, safe, and convenient rental homes and spaces for everyone. 
                    We're here to help you find the perfect place to call home!
                </p>
            </div>
            <div class="col-lg-5 col-md-5 mb-4 order-lg-2 order-md-2 order-1">
             <img src="images/about/about2.jpg" class="w-100">
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="images/about/about1.jpg" width="70px">
                    <h4 class="mt-3">10 ROOMS</h4>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="images/about/about1.jpg" width="70px">
                    <h4 class="mt-3">10 APARTMENT</h4>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="images/about/about1.jpg" width="70px">
                    <h4 class="mt-3">5 CONDO UNITS</h4>
                </div>
            </div>
            <!-- <div class="col-lg-3 col-md-6 mb-4 px-4">
                <div class="bg-white rounded shadow p-4 border-top border-4 text-center box">
                    <img src="images/about/about1.jpg" width="70px">
                    <h4 class="mt-3">6 BED SPACERS</h4>
                </div>
            </div> -->
        </div>
    </div>

    <!-- <h2 class="my-5 fw-bold h-font text-center">OUR LOCATIONS</h2>

    <div class="container px-4">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper mb-5">
                <?php
                    $about_r = selectAll('team_details');
                    $path = ABOUT_IMG_PATH;
                    while($row = mysqli_fetch_assoc($about_r)){
                    echo<<<data
                        <div class="swiper-slide bg-white text-center overflow-hidden rounded">
                            <img src="$path$row[picture]" class="w-100">
                            <h5 class="mt-2">$row[name]</h5>
                        </div>
                        data;
                    }
                ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div> -->




    
    <?php require('includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 4,
            spaceBetween: 40,
            pagination: {
            el: ".swiper-pagination",
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });
    </script>


    </body>
</html>



    




