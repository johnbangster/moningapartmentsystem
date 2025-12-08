<?php
include('authentication.php');

require ('config/code.php');
require ('includes/header.php');
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">CAROUSEL</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol>

    <!--CAROUSEL section-->
    <div class="row">
        
        <!--Carousel settings -->
        <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                 <h5 class="card-title m-0">Images</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#carousel-s">
                       <i class="fa-regular fa-square-plus"></i> Add
                    </button>
                </div>
                <div class="row" id="carousel-data">
                </div>


            </div>
        </div>

        <!--Carousel settings Modal -->
        <div class="modal fade" id="carousel-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="carousel_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Add Image</h5>
                        </div>
                        <div class="modal-body">
                             <div class="mb-3">
                                <label class="form-label  fw-bold">Picture</label>
                                <input type="file" id="carousel_picture_inp" name="carousel_picture" accept="[.jpg, .jpeg, .png]" class="form-control shadow-none" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="carousel_picture.value=''" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                            <button type="submit"  class="btn custom-bg text-white shdow-none">SUBMIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>



    </div>
</div>

<script src="scripts/carousel.js"></script>

<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>





