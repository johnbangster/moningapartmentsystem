<?php
// Hide warnings from breaking HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);
require 'function_booking.php';


// Define default values so variables exist
$movein_default   = ''; // empty date
$adult_default    = 1;  // default 1 adult
$children_default = 0;  // default 0 children
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    
    <?php require('includes/links.php');?>
    <title><?php echo $settings_r['site_title']; ?> - UNITS</title>
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
        <h2 class="fw-bold h-font text-center">UNITS FOR RENT</h2>
            <div class="h-line bg-dark"></div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-12 mb-lg-0 mb-4 ps-4">
                <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow">
                    <div class="container-fluid flex-lg-column align-items-stretch">
                        <h4 class="mt-4">FILTERS</h4>
                        <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterDropDown" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse flex-column align-items-stretch mt-2" id="filterDropDown">
                            <!--check AVAILABILITY-->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font-size: 18px;">
                                    <span>CHECK AVAILABILITY</span>
                                    <button id="chk_avail_btn" onclick="chk_avail_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <label class="form-label">Move-in Date</label>
                                <input type="date" class="form-control shadow-none mb-3" value="<?php echo $movein_default ?>" id="movein" onchange="chk_avail_filter()">
                                <!-- <label class="form-label">Move-out Date</label>
                                <input type="date" class="form-control shadow-none" id="moveout" onchange="chk_avail_filter()"> -->
                            </div>

                            <!--facilites-->
                            <div class="border bg-light p-3 rounded mb-3">
                                 <h5 class="d-flex align-items-center justify-content-between mb-3" style="font-size: 18px;">
                                  <span>FACILITIES</span>
                                   <button id="facilities_btn" onclick="facilities_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <?php

                                    $facilities_query = selectAll('facilities');
                                    while($row = mysqli_fetch_assoc($facilities_query))
                                    {
                                        echo<<<facilities
                                            <div class="mb-2">
                                                <input type="checkbox" name="facilties" onclick="fetch_units()" value="$row[id]" class="form-check-input shadow-none me-1" id="$row[id]">
                                                <label class="form-check-label" for="$row[id]">$row[name]</label>
                                            </div>
                                        facilities;

                                    }
                                
                                ?>
                                
                                <div class="mb-2">
                                    <input type="checkbox" id="f2" class="form-check-input shadow-none me-1">
                                    <label class="form-check-label" for="f2">Facility Two</label>
                                </div>
                                <div class="mb-2">
                                    <input type="checkbox" id="f3" class="form-check-input shadow-none me-1">
                                    <label class="form-check-label" for="f3">Facility Three</label>
                                </div>
                            </div>
                            <!--guests-->
                            <div class="border bg-light p-3 rounded mb-3">
                                <h5 class="d-flex align-items-center justify-content-between mb-3" style="font-size: 18px;">
                                  <span>GUESTS</span>
                                   <button id="guests_btn" onclick="guests_clear()" class="btn shadow-none btn-sm text-secondary d-none">Reset</button>
                                </h5>
                                <div class="d-flex">
                                    <div class="me-3">
                                        <label class="form-label">Adults</label>
                                        <input type="number"
                                        value="<?= htmlspecialchars($adult_default) ?>"
                                        id="adults"
                                        oninput="guests_filter()"
                                        class="form-control shadow-none">
                                        <!-- <input type="number"  value="<?php echo $adult_default ?>" id="adults" oninput="guests_filter()" class="form-control shadow-none"> -->
                                    </div>
                                    <div>
                                        <label class="form-label">Children</label>
                                        <input type="number"
                                            value="<?= htmlspecialchars($children_default) ?>"
                                            id="children"
                                            oninput="guests_filter()"
                                            class="form-control shadow-none">
                                        <!-- <input type="number" id="children"  value="<?php echo $children_default ?>" oninput="guests_filter()" class="form-control shadow-none"> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div> 

            <div class="col-lg-9 col-md-12 px-4" id="units-data"></div>

        </div>
    </div>

     <script>

        let units_data = document.getElementById('units-data');

        let movein = document.getElementById('movein');
        // let moveout = document.getElementById('moveout');
        let chk_avail_btn = document.getElementById('chk_avail_btn');

        let adults = document.getElementById('adults');
        let children = document.getElementById('children');
        let guests_btn = document.getElementById('guests_btn');

        let facilities_btn = document.getElementById('facilities_btn');



        function fetch_units()
        {
            let chk_avail = JSON.stringify({
                movein: movein.value,
                // moveout: moveout.value
            });

            let guests = JSON.stringify({
                 adults:  adults.value,
                children: children.value
            });

            let facility_list = {"facilities":[]};

            let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
            if(get_facilities.length>0)
            {
                get_facilities.forEach((facility)=>{
                    facility_list.facilities.push(facility.value);
                });
                facilities_btn.classList.remove('d-none');
            }
            else{
                facilities_btn.classList.add('d-none');

            }

            facility_list = JSON.stringify(facility_list);

            let xhr = new XMLHttpRequest();
            xhr.open("GET","admin/ajax/units.php?fetch_units&chk_avail="+chk_avail+"&guests="+guests+"&facility_list="+facility_list,true);

            xhr.onprogress = function() {
                units_data.innerHTML = `<div class="spinner-border text-info mb-3 d-block mx-auto" id="loader" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>`;
            }

            xhr.onload = function(){
                units_data.innerHTML = this.responseText
            }

            xhr.send();
        }

        function chk_avail_filter()
        {
            if(movein.value!= ''){
                fetch_units();
                chk_avail_btn.classList.remove('d-none');
            }
        }

        function chk_avail_clear()
        {
            movein.value= '';
            chk_avail_btn.classList.add('d-none');
            fetch_units();
        }

        function guests_filter(){
            if(adults.value>0 || children.value>0){
                fetch_units();guests_btn.classList.remove('d-none')

            }
        }

        function guests_clear(){
            adults.value= '';
            children.value='';
            guests_btn.classList.add('d-none');
            fetch_units();
        }

        function facilities_clear(){
            let get_facilities = document.querySelectorAll('[name="facilities"]:checked');
            get_facilities.forEach((facility)=>{
                facility.checked=false;
            });
            facilities_btn.classList.add('d-none');
            fetch_units();
        }

        window.onload = function(){
        fetch_units();

        }


    </script> 

    <?php require('includes/footer.php'); ?>


</body>
</html>
