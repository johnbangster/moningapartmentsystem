<?php
    session_start();
    include('authentication.php');
    require ('config/code.php');
    require ('includes/header.php');
    // require ('message.php');

    // if (isset($_SESSION['success'])) {
    //     echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
    //     unset($_SESSION['success']);
    // }
    // if (isset($_SESSION['error'])) {
    //     echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
    //     unset($_SESSION['error']);
    // }

    // Get renter filter
    $filter_name = isset($_GET['renter_name']) ? trim($_GET['renter_name']) : '';

     
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">RENTER</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol> 

    <!-- <?php
        // if (isset($_SESSION['success'])) {
        //     echo '<div class="alert alert-success text-center">' . $_SESSION['success'] . '</div>';
        //     unset($_SESSION['success']);
        // }
        // if (isset($_SESSION['error'])) {
        //     echo '<div class="alert alert-danger text-center">' . $_SESSION['error'] . '</div>';
        //     unset($_SESSION['error']);
        // }
    ?> -->

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <!-- Filter Form -->
                 <div class="text-start mb-4">
                    <button type="button" class="btn btn-success shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-renter">
                         ADD
                    </button>
                    <div class="row" id="team-data">
                    </div>
                </div> 
                <div class="d-flex justify-content-end mb-4">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label for="renter_name" class="fw-bold mb-0 me-2">Search:</label>
                        <input type="text" id="renter_name" name="renter_name" 
                            class="form-control shadow-none"
                            placeholder="Search by renter name..."
                            value="<?php echo htmlspecialchars($filter_name); ?>"
                            style="width: 220px;">
                        <a href="renter.php" class="btn btn-secondary shadow-none">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </a>
                    </form>
                </div>
                 

                <div class="table-responsive-lg" style="height:450px; overflow-y: scroll;">
                    <table class="table table-hover border text-center">
                        <thead class="table-dark text-center">
                            <tr class="bg-dark text-light">
                                <th scope="col">RENTER ID</th>
                                <!-- <th scope="col">Image</th> -->
                                <th scope="col">RENTER NAME</th>
                                <th scope="col">CONTACT NO</th>
                                <th scope="col">HOUSE NAME</th>
                                <th scope="col">Status</th>                                
                                <th scope="col">ACTIONS</th>
                                <th scope="col">AGREEMENT</th>

                                <!-- <th scope="col">VIEW DETAILS</th> -->
                            </tr>
                        </thead>
                        <tbody id="renter-data">
                            <?php
                             // Pagination setup
                                $limit = 5;
                                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
                                $offset = ($page - 1) * $limit;

                                // Prepare filter condition
                                $where = "";
                                if (!empty($filter_name)) {
                                    $safe_name = mysqli_real_escape_string($con, $filter_name);
                                    $where = "WHERE 
                                        r.first_name LIKE '%$safe_name%' 
                                        OR r.middle_name LIKE '%$safe_name%' 
                                        OR r.last_name LIKE '%$safe_name%'";
                                }

                                // Count total renters
                                $count_query = "
                                    SELECT COUNT(*) AS total 
                                    FROM renters r
                                    JOIN units u ON r.unit_id = u.id
                                    LEFT JOIN rental_agreements ra ON r.id = ra.renter_id AND r.unit_id = ra.unit_id
                                    $where
                                ";

                                // $count_query = "SELECT COUNT(*) AS total FROM renters";
                                $count_result = mysqli_query($con, $count_query);
                                $total_rows = mysqli_fetch_assoc($count_result)['total'];
                                $total_pages = ceil($total_rows / $limit);

                                $query = "
                                            SELECT 
                                                r.*, 
                                                u.name AS unit_name,
                                                ra.id AS agreement_id,
                                                ra.term_months,
                                                ra.start_date,
                                                ra.end_date,
                                                ra.monthly_rent,
                                                ra.deposit,
                                                ra.status AS agreement_status
                                            FROM renters r
                                            JOIN units u ON r.unit_id = u.id
                                            LEFT JOIN rental_agreements ra 
                                                ON r.id = ra.renter_id AND r.unit_id = ra.unit_id
                                            $where
                                            ORDER BY r.created_at DESC

                                            LIMIT $limit OFFSET $offset
                                        "; 

                                        $result = mysqli_query($con, $query);
                                        // Display renters
                                        $i = $offset + 1;

                                        $prev_page = $page - 1;
                                        $next_page = $page + 1; 
                                        
                                        // onclick=\"unit_images($row[id],'$row[name]')\"-->add this onclick on image button
                                        if(mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {
                                        
                                            echo "<tr>";
                                            echo "<td>{$i}</td>";
                                            // ' style='width:50px; height:50px;' alt='img'/></td>";
                                            // echo "<td> <img src='../../<?=$row[image]; 

                                            echo "<td>
                                                    <span class='badge rounded-pill bg-light text-dark'>
                                                      $row[first_name]  $row[last_name] $row[middle_name]
                                                    </span><br>
                                                  </td>";
                                            echo "<td>{$row['contacts']}</td>";

                                            echo "<td>{$row['unit_name']}</td>";
                                            echo "<td>{$row['status']}</td>";
                                            echo "<td>
                                                    <button class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>
                                                        <i class='fa-solid fa-pen-to-square'></i>
                                                    </button>

                                                    <button type='button' onclick=\"renter_images({$row['id']}, '{$row['first_name']} {$row['last_name']}')\" 
                                                        class='btn btn-info shadow-none' data-bs-toggle='modal' data-bs-target='#renter-images'>
                                                        <i class='fa-solid fa-images'></i>
                                                    </button>

                                                    
                                                   <a href='functions/delete_renter.php?id={$row['id']}' class='btn btn-danger' onclick=\"return confirm('Are you sure you want to delete this renter?');\">
                                                    <i class='fa-solid fa-trash'></i>
                                                   </a>
                                                   <a href='view_renter.php?renter_id= {$row['id']}' class='btn btn-outline-success btn-sm'>
                                                            <i class='fa-solid fa-eye'></i> View
                                                    </a>
                                            </td>";
                                                echo "<td>
                                                        <a href='view_agreement.php?agreement_id={$row['agreement_id']}' class='btn btn-success'>
                                                            <i class='fa-solid fa-file-contract'></i>
                                                        </a>
                                                    </td>";
                                        echo "</tr>";

                                        include('modals/edit_renter_modal.php');
                                    $i++;
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-danger fw-bold'>No renters found.</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>

                

                <!--Pagination Controls -->
                <div class="text-center mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end">
                            <?php if ($page > 1): ?>
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!--add renter Modal -->
<div class="modal fade" id="add-renter" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        
        <form action="functions/insert_renter.php" method="POST" id="addRenterForm" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel"><i class="fa-solid fa-house-circle-check"></i> Add Renter</h5>
                </div>
                <div class="modal-body" id="validationErrors">
                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" id="fname" name="first_name" class="form-control shadow-none" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed.">
                        </div>
                        <!-- Last Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" id="lname" name="last_name" class="form-control shadow-none" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed.">
                        </div>
                        <!-- Middle Name -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">M.I</label>
                            <input type="text" id="middle_name" name="middle_name" class="form-control shadow-none">
                        </div>
                        <!-- Contact -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contact Number</label>
                            <input type="text" id="contact" name="contact" class="form-control shadow-none" required pattern="\d{11}" maxlength="11" title="Must be exactly 11 digits (e.g. 639XXXXXXXXX)">
                        </div>
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" id="email" name="email" class="form-control shadow-none" required>
                        </div>
                        <!-- Address -->
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control shadow-none" rows="1" required></textarea>
                        </div>
                        <!-- Password -->
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Password (Default: renter123)</label>
                            <input type="password" name="password" class="form-control shadow-none" value="renter123" readonly required>
                        </div>
                        <!-- Add members -->
                         <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Family Members</label>
                                <div id="members-container">
                                    <!-- Member row will be cloned here -->
                                    <div class="row member-row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" name="member_name[]" class="form-control shadow-none" placeholder="Member Name"  pattern="[A-Za-z ]+">
                                        </div>
                                        <div class="col-md-5">
                                            <select name="member_type_id[]" class="form-control shadow-none">
                                                <option value="">-- Select Relationship --</option>
                                                <?php
                                                    $types = mysqli_query($con, "SELECT * FROM members");
                                                    while($row = mysqli_fetch_assoc($types)) {
                                                        echo "<option value='".$row['id']."'>".$row['member_type']."</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger remove-member w-100">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            <button type="button" id="add-member" class="btn btn-sm btn-secondary mt-2">+ Add Member</button>
                         </div>
                        <!-- Move-in Date -->
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Date Started</label>
                            <input type="date" id="move_in_date" name="move_in_date" class="form-control shadow-none" required>
                        </div>
                        <!-- Lease Term -->
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Lease Term (6/12 Months)</label>
                            <select name="lease_term" class="form-control shadow-none" required>
                                <option selected disabled>-- Select Lease Term --</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                            </select>
                        </div>
                       <!-- Unit -->
                       <div class="col-md-6 mb-2">
                            <label class="form-label fw-bold">House Name</label>
                            <div class="input-group">
                                <select name="unit_id" id="unit_id" class="form-control shadow-none" required>
                                    <option selected>-- Select Unit --</option>
                                    <?php
                                    // Fetch units with Available, Booked, or Reserved status
                                    $unit_res = mysqli_query($con, "
                                        SELECT id, name, status 
                                        FROM units 
                                        WHERE removed = 0 
                                        AND status IN ('Available', 'Booked', 'Reserved')
                                    ");

                                    while ($unit = mysqli_fetch_assoc($unit_res)) {
                                        // Display status label
                                        $status_label = " (" . $unit['status'] . ")";

                                        echo '<option value="' . $unit['id'] . '">'
                                                . $unit['name'] . $status_label .
                                            '</option>';
                                    }
                                    ?>
                                </select>

                                <button type="button" class="btn btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#add-unit-modal">
                                    + Add Unit
                                </button>
                            </div>
                       </div>                  
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" name="add_renter" class="btn custom-bg text-white shadow-none">ADD</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!--renter image Modal -->
<div class="modal fade" id="renter-images" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title">Renter Name</h1>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="image-alert">

        </div>
        <div class="border-bottom border-3 pb-3 mb-3">
            <form id="add_image_form">
                <label class="form-label fw-bold">Add Image</label>
                <input type="file" name="image" accept=".jpg, .jpeg, .png" class="form-control shadow-none mb-3" required>
                <button class="btn custom-bg text-white shadow-none">ADD</button>
                <input type="hidden" name="renter_id">
            </form>
        </div>
        <div class="table-responsive-lg" style="height:350px; overflow-y: scroll;">
            <table class="table table-hover border text-center">
                <thead>
                    <tr class="bg-dark text-light sticky-top">
                    <th scope="col" width="60%">Image</th>
                    <th scope="col">Thumb</th>
                    <th scope="col">Delete</th>
                    </tr>
                </thead>
                <tbody id="renter-image-data">
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({ icon: 'success', title: '<?php echo $_SESSION['success']; ?>', timer: 1500, showConfirmButton: false });
    </script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({ icon: 'error', title: '<?php echo $_SESSION['error']; ?>' });
    </script>
<?php unset($_SESSION['error']); endif; ?>

<!-- Add Unit Modal -->
<div class="modal fade" id="add-unit-modal" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="add_unit_form" action="functions/add_unit_ajax.php" method="POST" autocomplete="off">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-house-circle-check"></i> Add Unit</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" name="name" class="form-control shadow-none" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Area (sq. ft.)</label>
                            <input type="number" name="area" class="form-control shadow-none" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Price</label>
                            <input type="text" name="price" id="price" class="form-control shadow-none" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Unit Type</label>
                            <select name="unit_type_id" class="form-control shadow-none" required>
                                <option value="">-- Select Type --</option>
                                <?php
                                    $types = mysqli_query($con, "SELECT * FROM unit_type");
                                    while($row = mysqli_fetch_assoc($types)) {
                                        echo "<option value='".$row['id']."'>".$row['type_name']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <!-- Branch select -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <select name="branch_id" class="form-control shadow-none" required>
                                <option value="">-- Select Branch --</option>
                                <?php
                                    $branches = mysqli_query($con, "SELECT * FROM branch WHERE status='Active'");
                                    while($b = mysqli_fetch_assoc($branches)) {
                                        echo "<option value='".$b['id']."'>".$b['name']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Adult</label>
                            <input type="number" min="1" name="adult" class="form-control shadow-none" required>
                        </div> 
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Children</label>
                            <input type="number" name="children" class="form-control shadow-none">
                        </div>

                        <div class="col-12 mb-3">
                          <label class="form-label fw-bold">Features</label>
                          <div class="row">
                            <?php
                                $res = selectAll('features');
                                while($opt = mysqli_fetch_assoc($res)){
                                    echo"
                                        <div class='col-md-3 mb-1'>
                                            <label>
                                                <input type='checkbox' name='features[]' value='$opt[id]' class='form-check-input shadow-none'>
                                                $opt[name]
                                            </label>
                                        </div>
                                    ";
                                }
                            ?>
                          </div>
                        </div>

                        <div class="col-12 mb-3">
                          <label class="form-label fw-bold">Facilities</label>
                          <div class="row">
                            <?php
                                $res = selectAll('facilities');
                                while($opt = mysqli_fetch_assoc($res)){
                                    echo"
                                        <div class='col-md-3 mb-1'>
                                            <label>
                                                <input type='checkbox' name='facilities[]' value='$opt[id]' class='form-check-input shadow-none'>
                                                $opt[name]
                                            </label>
                                        </div>
                                    ";
                                }
                            ?>
                          </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="desc" class="form-control shadow-none" rows="2" required ></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <!-- <button type="submit" class="btn custom-bg text-white shadow-none">SUBMIT</button> -->
                     <button type="submit" class="btn btn-success">SAVE UNIT</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script src="scripts/renter.js"></script>

<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>



<!-- AlertifyJS -->
<!-- <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    // --- Add & Remove Family Members ---
    document.addEventListener("DOMContentLoaded", () => {
        const addMemberBtn = document.getElementById("add-member");
        const membersContainer = document.getElementById("members-container");

        //Save a copy of the select options at the start
        let memberTypeOptions = "";
        const firstSelect = document.querySelector("select[name='member_type_id[]']");
        if (firstSelect) {
            memberTypeOptions = firstSelect.innerHTML;
        }

        // Add new member row
        addMemberBtn.addEventListener("click", () => {
            const newRow = document.createElement("div");
            newRow.classList.add("row", "member-row", "mb-2");
            newRow.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="member_name[]" class="form-control shadow-none" placeholder="Member Name" pattern="[A-Za-z ]+">
                </div>
                <div class="col-md-5">
                    <select name="member_type_id[]" class="form-control shadow-none">
                        <option value="">-- Select Relationship --</option>
                        ${memberTypeOptions}
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-member w-100">Remove</button>
                </div>
            `;
            membersContainer.appendChild(newRow);
        });

        // Remove member row
        membersContainer.addEventListener("click", (e) => {
            if (e.target.classList.contains("remove-member")) {
                e.target.closest(".member-row").remove();
            }
        });
    });

   

    //input validation
   document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("addRenterForm");

    const fields = {
        fname: /^[A-Za-z\s]+$/,
        lname: /^[A-Za-z\s]+$/,
        contact: /^\d{11}$/,
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    };

    //  Real-time validation for each input
    Object.keys(fields).forEach(id => {
        const input = document.getElementById(id);
        input.addEventListener("input", () => {
            if (fields[id].test(input.value.trim())) {
                input.classList.add("is-valid");
                input.classList.remove("is-invalid");
            } else {
                input.classList.add("is-invalid");
                input.classList.remove("is-valid");
            }
        });
    });

    //  Form submission
    form.addEventListener("submit", function(e) {
        let isValid = true;
        let errorMessage = "";

        const fname = document.getElementById("fname");
        const lname = document.getElementById("lname");
        const contact = document.getElementById("contact");
        const email = document.getElementById("email");
        const moveInDate = document.getElementById("move_in_date").value;
        const leaseTerm = document.querySelector("select[name='lease_term']").value;
        const unit = document.querySelector("select[name='unit_id']").value;

        const namePattern = /^[A-Za-z\s]+$/;
        const contactPattern = /^\d{11}$/;
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Validate primary fields
        if (!namePattern.test(fname.value.trim())) {
            isValid = false; errorMessage += "• First Name is invalid.<br>";
            fname.classList.add("is-invalid");
        }
        if (!namePattern.test(lname.value.trim())) {
            isValid = false; errorMessage += "• Last Name is invalid.<br>";
            lname.classList.add("is-invalid");
        }
        if (!contactPattern.test(contact.value.trim())) {
            isValid = false; errorMessage += "• Contact must be 11 digits.<br>";
            contact.classList.add("is-invalid");
        }
        if (!emailPattern.test(email.value.trim())) {
            isValid = false; errorMessage += "• Invalid Email format.<br>";
            email.classList.add("is-invalid");
        }
        if (!moveInDate) {
            isValid = false; errorMessage += "• Move-in Date is required.<br>";
        }
        if (!leaseTerm) {
            isValid = false; errorMessage += "• Lease Term is required.<br>";
        }
        if (!unit) {
            isValid = false; errorMessage += "• Please select a unit.<br>";
        }

        // Family members validation
        document.querySelectorAll(".member-row").forEach((row, index) => {
            const memberName = row.querySelector("input[name='member_name[]']");
            const memberType = row.querySelector("select[name='member_type_id[]']");

            if (memberName.value.trim() !== "" && !namePattern.test(memberName.value.trim())) {
                isValid = false;
                errorMessage += `• Member ${index + 1} name is invalid (letters only).<br>`;
                memberName.classList.add("is-invalid");
            }
            if (memberName.value.trim() !== "" && memberType.value === "") {
                isValid = false;
                errorMessage += `• Select a relationship for Member ${index + 1}.<br>`;
            }
        });

        // Show Error (Stop Form Submit)
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Form Error",
                html: errorMessage,
                confirmButtonColor: "#d33"
            });
        } else {
            //  Show Success After Submission
            e.preventDefault(); 
            Swal.fire({
                icon: "success",
                title: "Renter Added Successfully!",
                text: "The form has been submitted.",
                confirmButtonColor: "#28a745"
            }).then(() => {
                form.submit(); 
            });
        }
    });
});

$(document).ready(function () {

    // Hide Add Renter modal when Add Unit opens
    $("#add-unit-modal").on("show.bs.modal", function () {
        $("#add-renter").modal("hide");
    });

    // Submit Add Unit form via AJAX
    $("#add_unit_form").on("submit", function (e) {
        e.preventDefault();

        // Before submitting, attach the hidden.bs.modal listener
        $("#add-unit-modal").one('hidden.bs.modal', function () {
            // Reset Add Unit form
            $("#add_unit_form")[0].reset();

            // Reopen Add Renter modal
            $("#add-renter").modal("show");
        });

        $.ajax({
            url: "functions/add_unit_ajax.php",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {

                    // Show success alert
                    Swal.fire({
                        icon: 'success',
                        title: res.message,
                        timer: 1000,
                        showConfirmButton: false
                    });

                    // Add new unit to Add Renter dropdown
                    let newUnit = `<option value="${res.unit.id}" selected>${res.unit.name} (Available)</option>`;
                    $("#unit_id").append(newUnit);

                    // Hide Add Unit modal → triggers hidden.bs.modal listener
                    $("#add-unit-modal").modal("hide");

                } else if (res.status === "error") {
                    Swal.fire({
                        icon: 'error',
                        title: res.message
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'AJAX Error',
                    text: error
                });
            }
        });
    });

});

    
  

document.addEventListener("DOMContentLoaded", function() {
    //set tne min attribute to todays date
const today =  new Date().toISOString().split("T")[0];
document.getElementById("move_in_date").setAttribute("min", today);
});

document.getElementById("addRenterForm").addEventListener("submit", function (e) {
    const contact = document.getElementById("contact").value;
    const moveInDate = document.getElementById("move_in_date").value;
    const today = new Date().toISOString().split("T")[0]; //yyyy-mm-dd
    // document.getElementById("move_in_date").setAttribute("min", today);

    if (!/^\d{11}$/.test(contact)) {
        alert("Contact number must be exactly 11 digits.");
        e.preventDefault();
        return;
    }


    //validate move-in date
    if(moveInDate < today) {
        alert("Move-in date cannot be in the past.");
        e.preventDefault();
        return;
    }

});


const resetCanvas = document.getElementById("resetCanvas")
const getImage = document.getElementById("getImage")

// Call signature with the root element and the options object, saving its reference in a variable
const component = Signature(root, {
    width: 500,
    height: 100,
    instructions: "Please sign in the box above"
});

resetCanvas.addEventListener("click", () => {
    component.value = [];
});

getImage.addEventListener("click", () => {
    getImage.nextElementSibling.src = component.getImage();
});

function handleDownload() {
    // Retrieve the base64 string value from the signature component
    const cleanBase64 = component.getImage().split(',')[1]

    // Convert base64 to a blob
    const byteCharacters = atob(cleanBase64);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: 'application/octet-stream' });

    // Create a download link
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(blob);
    downloadLink.download = 'signature.png'; // Set the desired file name and extension

    // Trigger the download
    document.body.appendChild(downloadLink);
    downloadLink.click();

    // Remove the link from the page
    document.body.removeChild(downloadLink); 
}

$(document).ready(function() {

function loadRenters(page = 1, filter = '') {
    $.ajax({
        url: "fetch_renter.php",
        type: "POST",
        data: { renter_name: filter, page: page },
        beforeSend: function() {
            $("#renter-data").html("<tr><td colspan='7' class='text-center'>Loading...</td></tr>");
        },
        success: function(data) {
            $("#renter-data").html(data);
        }
    });
}

// initial load
loadRenters();

// handle filter form submit
$("form").on("submit", function(e) {
    e.preventDefault();
    const filterVal = $("input[name='renter_name']").val();
    loadRenters(1, filterVal);
});

// handle pagination click dynamically
$(document).on("click", ".page-link-ajax", function(e) {
    e.preventDefault();
    const page = $(this).data("page");
    const filterVal = $("input[name='renter_name']").val();
    loadRenters(page, filterVal);
});

// handle reset button
$(".btn-secondary").on("click", function(e) {
    e.preventDefault();
    $("input[name='renter_name']").val('');
    loadRenters();
});


});






</script>





   





