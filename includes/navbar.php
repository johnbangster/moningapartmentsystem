    
    <?php
        $contact_q = "SELECT * FROM `contact_details` WHERE `id`=?";
        $settings_q = "SELECT * FROM `settings` WHERE `id`=?";
        $values = [1];
        $contact_r = mysqli_fetch_assoc(select($contact_q,$values,'i'));
        $settings_r = mysqli_fetch_assoc(select($settings_q,$values,'i'));
    ?>
    <nav id="nav-bar" class="navbar navbar-expand-lg navbar-light bg-white px-lg-0 py-lg-2 shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand me-5  fs-3 h-font" href="index.php"><?php echo $settings_r['site_title']; ?></a>
            <button class="navbar-toggler shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link me-2"  href="index.php">Home</a>
                    </li>
                    <!-- <li class="nav-item">
                    <a class="nav-link me-2" href="units.php">Units</a>
                    </li> -->
                    <!-- <li class="nav-item">
                    <a class="nav-link me-2" href="facilities.php">Facilities</a>
                    </li> -->
                    <li class="nav-item ">
                    <a class="nav-link me-2" href="contact.php">Contact Us</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <!-- <li class="nav-item ">
                        <a class="nav-link" href="about.php">Login</a>
                    </li> -->
                </ul>
                 <div class="d-flex"> 

                    <?php if(isset($_SESSION['auth_user'])) : ?>
                    <!-- <div class="btn-group">
                        <button type="button" class="btn btn-outline-dark shadow-none dropdown-toggle me-1" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                            <?= $_SESSION['auth_user']['user_name']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-lg-end">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="bookings.php">My Bookings</a></li>
                            <li><a class="dropdown-item" href="#">Account</a></li>
                            <li>
                                <form action="logout.php" method="post">
                                    <button type="submit" class="dropdown-item"  name="logout">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div> -->
                    <?php else : ?>
                     <a href="login.php" class="btn btn-outline-dark shadow-none me-lg-3 me-2" >
                        Login
                    </a>
                    <?php endif; ?>
                    <!-- <button type="button" class="btn btn-outline-dark shadow-none me-lg-3 me-3" data-bs-toggle="modal" data-bs-target="#registerModal">
                        Register
                    </button> -->
                </div>
            </div>
        </div>
    </nav>

    <!-- login Modal -->
    <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="logincode.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi bi-person-circle fs-3 me-2"></i> User Login
                        </h5>
                        <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" class="form-control shadow-none">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control shadow-none">
                        </div>                         
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <button name="login_btn" type="submit" class="btn btn-dark shadow-none">Login</button>
                            <a href="javascript: void(0)" class="text-secondary text-decoration-none">Forgot Password?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- register Modal -->
    <!-- <div class="modal fade" id="registerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form>
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-person-lines-fill fs-3 me-2"></i> User Registration
                        </h5>
                        <button type="reset" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <span class="badge bg-info text-dark mb-3 text-wrap lh-base">
                            Note: Please input the details that match the ID(Company ID, Passport, etc) 
                            that will be required during move-in.
                        </span>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-4 ps-0 mb-3">
                                    <label>First Name</label>
                                    <input type="text" class="form-control shadow-none">
                                </div>
                                <div class="col-md-4  mb-3">
                                    <label>Middle Name</label>
                                    <input type="text" class="form-control shadow-none">
                                </div>
                                <div class="col-md-4  mb-3">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control shadow-none">
                                </div>
                                <div class="col-md-12 ps-0 mb-3">
                                    <label>Address</label>
                                    <textarea class="form-control shadow-none" rows="1"></textarea>
                                </div>
                                <div class="col-md-6 ps-0 mb-3">
                                    <label>Contact Number</label>
                                    <input type="number" class="form-control shadow-none">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control shadow-none">
                                </div>
                                <div class="col-md-6 ps-0 mb-3">
                                    <label>Password</label>
                                    <input type="password" class="form-control shadow-none">
                                </div>
                                <br>
                                <div class="col-md-6 mb-3">
                                    <label>User Type</label>
                                    <select class="form-select" aria-label="Default select example">
                                    <option value="1">Admin</option>
                                    <option value="2">Renter</option>
                                    </select>
                                </div>
                                <div class="col-md-6 ps-0 mb-3">
                                    <label class="form-check-label" for="flexCheckDefault">
                                    Status
                                    </label>
                                    <input class="form-check-input" type="checkbox">
                                </div>
                            </div>
                        </div>
                        <div class="text-center my-1">
                            <button type="submit" class="btn btn-dark shadow-none">Register</button>
                        </div>
                    </div>
                </form>
                </div>
        </div>
    </div> -->





