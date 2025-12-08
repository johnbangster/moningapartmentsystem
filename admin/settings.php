<?php
include('authentication.php');
include('middleware/superadminAuth.php');

require ('includes/header.php');
require ('message.php');
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">SETTINGS</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol>
    <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
    ?>
    <!--settings general -->
    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                 <h5 class="card-title m-0">General Settings</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#general-s">
                    <i class="fa-solid fa-pen-to-square"></i> Edit 
                    </button>
                </div>
                <h6 class="card-subtitle mb-1 fw-bold">Site Title</h6>
                <p class="card-text" id="site_title"></p>
                <h6 class="card-subtitle mb-1 fw-bold">Site About</h6>
                <p class="card-text" id="site_about"></p>
            </div>
        </div>

        <!--general settings Modal -->
        <div class="modal fade" id="general-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="general_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">General Settings</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Site Title</label>
                                <input type="text" id="site_title_inp" name="site_title" class="form-control shadow-none" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label  fw-bold">About us</label>
                                <textarea name="site_about" id="site_about_inp" class="form-control shadow-none" rows="6" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="site_title.value = general_data.site_title, site_about.value = general_data.site_about" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                            <button type="submit" class="btn custom-bg text-white shdow-none">SUBMIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--shutdown settings -->
        <div class="card border-0 shadow-sm mb-4"> 
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                 <h5 class="card-title m-0">Shutdown Website</h5>
                    <div class="form-check form-switch">
                        <form>
                            <input onchange="upd_shutdown(this.value)" class="form-check-input" type="checkbox" id="shutdown-toggle" value="">
                        </form>
                    </div>
                </div> 
                <p class="card-text"> 
                    No customers will be allowed to book units, when shutdown mode is on.
                </p>
            </div>
        </div>

        <!--contact settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                 <h5 class="card-title m-0">Contact Settings</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#contacts-s">
                       <i class="fa-solid fa-pen-to-square"></i> Edit 
                    </button>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Address</h6>
                            <p class="card-text" id="address"></p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Google Map</h6>
                            <p class="card-text" id="gmap"></p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Address</h6>
                            <p class="card-text mb-1">
                                <i class="fa-solid fa-phone">Phone Numbers</i>
                                <span id="pn1"></span>
                            </p>
                            <p class="card-text">
                                <i class="fa-solid fa-phone"></i>
                                <span id="pn2"></span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Email</h6>
                            <p class="card-text" id="email"></p>
                        </div>
                    </div>
                    <!-- <div class="col-lg-6">
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">Social Links</h6>
                            <p class="card-text mb-1">
                                <i class="fa-brands fa-facebook"></i>
                                <span id="fb"></span>
                            </p>
                            <p class="card-text mb-1">
                                <i class="fa-brands fa-facebook-messenger"></i>
                                <span id="msgr"></span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <h6 class="card-subtitle mb-1 fw-bold">iFrame</h6>
                            <iframe id="iframe" class="border p-2 w-100" loading="lazy"></iframe>
                        </div>

                    </div> -->
                </div>
                 
            </div>
        </div>

        <!--contact settings Modal -->
        <div class="modal fade" id="contacts-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="contacts_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Contact Settings</h5>
                        </div>
                        <div class="modal-body">
                            <div class="container-fluid p-0">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                         <label class="form-label fw-bold">Address</label>
                                         <input type="text" id="address_inp" name="address" class="form-control shadow-none" required>
                                        </div>
                                        <div class="mb-3">
                                         <label class="form-label fw-bold">Google Map Link</label>
                                         <textarea name="gmap" id="gmap_inp" class="form-control shadow-none" rows="6" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Contact Numbers</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                                <input type="number" name="pn1" id="pn1_inp" class="form-control shadow-none" required>
                                            </div>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                                                <input type="number" name="pn2" id="pn2_inp" class="form-control shadow-none" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                         <label class="form-label fw-bold">Email</label>
                                         <input type="email" name="email" id="email_inp" class="form-control shadow-none" rows="6" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Social Links</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-brands fa-facebook"></i></span>
                                                <input type="text" name="fb" id="fb_inp" class="form-control shadow-none" required>
                                            </div>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text"><i class="fa-brands fa-facebook-messenger"></i></span>
                                                <input type="text" name="msgr" id="msgr_inp" class="form-control shadow-none" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                         <label class="form-label fw-bold">iFrame Src</label>
                                         <input type="text" id="iframe_inp" name="iframe" class="form-control shadow-none" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="contacts_inp(contacts_data)" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                            <button type="submit"  class="btn custom-bg text-white shdow-none">SUBMIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!--management settings -->
        <!-- <div class="card border-0 shadow mb-4">
            <div class="card-body">

                <div class="d-flex align-items-center justify-content-between mb-3">
                 <h5 class="card-title m-0">Management Team</h5>
                    <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#team-s">
                       <i class="fa-regular fa-square-plus"></i> Add
                    </button>
                </div>
                <div class="row" id="team-data">
                </div>


            </div>
        </div> -->

        <!--management settings Modal -->
        <!-- <div class="modal fade" id="team-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="team_s_form">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Add Management Team</h5>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <input type="text" id="member_name_inp" name="member_name" class="form-control shadow-none" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label  fw-bold">Picture</label>
                                <input type="file" id="member_picture_inp" name="member_picture" accept="[.jpg, .jpeg, .png]" class="form-control shadow-none" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" onclick="member_name.value='', member_picture.value='' " class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                            <button type="submit"  class="btn custom-bg text-white shdow-none">SUBMIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div> -->
    </div>
</div>

<script src="scripts/settings.js"></script>


<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>





