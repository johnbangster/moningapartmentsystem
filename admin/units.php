<?php

include('authentication.php');
require ('config/code.php');
require ('includes/header.php');

$filter_name = isset($_GET['unit_name']) ? trim($_GET['unit_name']) : '';

?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">UNITS</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol> 

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="text-start mb-4">
                    <button type="button" class="btn btn-success shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-unit">
                         ADD
                    </button>
                    <div class="row" id="team-data">
                    </div>
                </div>  

                <!-- <?php echo htmlspecialchars($filter_name); ?> -->
                  <div class="d-flex justify-content-end mb-4">
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <label for="unit_name" class="fw-bold mb-0 me-2">Search:</label>
                        <input type="text" id="unit_name" name="unit_name" 
                            class="form-control shadow-none"
                            placeholder="Search by unit..."
                            value="<?php echo htmlspecialchars($filter_name); ?>"
                            style="width: 220px;">
                        <button type="submit" class="btn btn-primary shadow-none">
                            <i class="fa-solid fa-magnifying-glass"></i> Search
                        </button>
                        <a href="units.php" class="btn btn-secondary shadow-none">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </a>
                    </form>
                </div>
                <!-- <div class="d-flex justify-content-end mb-4">
                    <div class="d-flex align-items-center" style="width: 300px;">
                        <label for="renter_name" class="form-label fw-bold mb-0 me-2">Search:</label>
                        <input type="text" id="renter_name" name="renter_name" 
                            class="form-control shadow-none" 
                            placeholder="Search"
                            value="">
                        <a href="renter.php" class="btn btn-secondary shadow-none">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </a>
                    </div>
                </div> -->



                <div class="table-responsive-lg" style="height:450px; overflow-y: scroll;">
                    <table class="table table-hover border text-center">
                        <thead class="table-dark text-center">
                            <tr class="bg-dark text-light">
                            <th scope="col">UNIT ID</th>
                            <th scope="col">HOUSE NAME</th>
                            <!-- <th scope="col">Area</th> -->
                            <!-- <th scope="col">Unit Price</th> -->
                            <th scope="col">LOCATION</th>
                            <th scope="col">UNIT TYPE</th>
                            <th scope="col">OCCUPIED</th>
                            <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="unit-data">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--add unit Modal --> 
<div class="modal fade" id="add-unit" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="add_unit_form" autocomplete="off">
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
                                    $types = mysqli_query($con, "SELECT * FROM  unit_type ORDER BY type_name ASC");
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
                                    $branches = mysqli_query($con, "SELECT * FROM branch WHERE status='Active' ORDER BY name ASC");
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
                    <button type="submit" class="btn custom-bg text-white shadow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>



<!-- Edit Unit Modal -->
<div class="modal fade" id="edit-unit" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="editUnitLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="edit_unit_form" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-house-circle-check"></i> Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <input type="hidden" name="unit_id" id="edit_unit_id">

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control shadow-none" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Area (sq. ft.)</label>
                            <input type="number" name="area" id="edit_area" class="form-control shadow-none" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Price</label>
                            <input type="text" name="price" id="edit_price" class="form-control shadow-none" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Unit Type</label>
                            <select name="unit_type_id" id="edit_unit_type_id" class="form-control shadow-none" required>
                                <option value="">-- Select Type --</option>
                                <?php
                                    $types = mysqli_query($con, "SELECT * FROM unit_type");
                                    while($row = mysqli_fetch_assoc($types)) {
                                        echo "<option value='".$row['id']."'>".$row['type_name']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <select name="branch_id" id="edit_branch_id" class="form-control shadow-none" required>
                                <option value="">-- Select Branch --</option>
                                <?php
                                    $branches = mysqli_query($con, "SELECT * FROM branch WHERE status='Active'");
                                    while($b = mysqli_fetch_assoc($branches)) {
                                        echo "<option value='".$b['id']."'>".$b['name']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <!--update status-->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="edit_status" class="form-control shadow-none" required>
                                <option value="">-- Select Status --</option>
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Inactive">Inactive</option> <!-- if you use this as soft delete -->
                                <option value="Booked">Booked</option>
                                <option value="Reserved">Reserved</option>

                            </select>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Adult</label>
                            <input type="number" min="1" name="adult" id="edit_adult" class="form-control shadow-none" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Children</label>
                            <input type="number" name="children" id="edit_children" class="form-control shadow-none">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Features</label>
                            <div class="row" id="edit_features_list">
                                <?php
                                    $res = selectAll('features');
                                    while($opt = mysqli_fetch_assoc($res)){
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                            <label>
                                                <input type='checkbox' name='features[]' value='$opt[id]' class='edit_feature_checkbox form-check-input shadow-none'>
                                                $opt[name]
                                            </label>
                                        </div>";
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Facilities</label>
                            <div class="row" id="edit_facilities_list">
                                <?php
                                    $res = selectAll('facilities');
                                    while($opt = mysqli_fetch_assoc($res)){
                                        echo "
                                        <div class='col-md-3 mb-1'>
                                            <label>
                                                <input type='checkbox' name='facilities[]' value='$opt[id]' class='edit_facility_checkbox form-check-input shadow-none'>
                                                $opt[name]
                                            </label>
                                        </div>";
                                    }
                                ?>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="desc" id="edit_desc" class="form-control shadow-none" rows="2" required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit" class="btn custom-bg text-white shadow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>



<!--manage unit image Modal -->
<div class="modal fade" id="unit-images" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title">Unit Name</h1>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="image-alert">

        </div>
        <div class="border-bottom border-3 pb-3 mb-3">
            <form id="add_image_form">
                <label class="form-label fw-bold">Add Image</label>
                <label class="form-label fw-bold text-danger">
                 <span class="text-danger">*</span>
                </label>
                <small class="text-danger">Allowed file types: jpg/jpeg/png only. Max size: 10MB.</small>
                <input type="file" name="image" accept="[.jpg, .jpeg, .png]" class="form-control shadow-none mb-3" required>
                <button class="btn custom-bg text-white shadow-none">ADD</button>
                <input type="hidden" name="unit_id">
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
                <tbody id="unit-image-data">
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
    include('includes/scripts.php');
?>

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="scripts/units.js"></script>

<script src="https://cdn.jsdelivr.net/npm/alertifyjs/build/alertify.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs/build/css/alertify.min.css"/>

<script>
 // Safe number formatter
//   function formatNumber(num) {
//     return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
//   }

//   // Format inputs numver
//   document.getElementById('price').addEventListener('input', function (event) {
//     let value = event.target.value.replace(/[^0-9]/g,'');
//     event.target.value = value ? formatNumber(value) : '';
//   });

//   document.getElementById('edit_price').addEventListener('input', function (event) {
//     let value = event.target.value.replace(/[^0-9]/g,'');
//     event.target.value = value ? formatNumber(value) : '';
//   });

 function remove_unit(unit_id) {
        Swal.fire({
            title: "Are you sure?",
            text: "This unit will be deleted permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                let data = new FormData();
                data.append('unit_id', unit_id);
                data.append('remove_unit', '');

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax/units.php", true);

                xhr.onload = function () {
                    if (this.responseText.trim() == "1") {
                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text: "Unit has been successfully removed.",
                            timer: 1500,
                            showConfirmButton: false
                        })

                        // Refresh the unit list dynamically
                        setTimeout(() => {
                            get_all_unit();
                        }, 1500);
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed!",
                            text: "Failed to remove unit. Please try again."
                        });
                    }
                };

                xhr.onerror = function () {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error!",
                        text: "Could not connect to the server."
                    });
                };

                xhr.send(data);
            }
        });
    }


    $('#add_unit_form').on('submit', function(e){
        e.preventDefault();

        $.ajax({
            url: "function/add_unit.php",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: "json",

            success: function(res){
                if(res.status === "success"){

                    alertify.set('notifier','position', 'top-right');
                    alertify.success(res.message);

                    // Close modal properly
                    const modalEl = document.getElementById('add-unit');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();

                    // Remove leftover backdrop
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('padding-right','');

                    // Refresh page
                    setTimeout(() => {
                        window.location.href = "units.php";
                    }, 800);
                }
            }
        });
    });

    // Handle form submit
    //     $("#add_unit_form").submit(function(e){
    //         e.preventDefault();

    //         $.ajax({
    //             url: "function/add_unit.php",
    //             type: "POST",
    //             data: $(this).serialize(),
    //             dataType: "json",
    //             success: function(res){
    //                 if(res.status === "success"){
    //                     alertify.success(res.message);
    //                     alertify.set('notifier','position', 'top-right');
    //                     $("#add-unit").modal("hide");
    //                     $("#add_unit_form")[0].reset();
    //                     loadUnits();
    //                     $("#unit-data").load("unit_fetch.php");
    //                 } else {
    //                     alertify.error(res.message);
    //                 }
    //             },
    //             error: function(xhr, status, error){
    //                 alertify.error("AJAX Error: " + error);
    //             }
    //         });
    //     });

    // });

    // load table data
    // function loadUnits(){
    //     $.ajax({
    //         url: "unit_fetch.php",
    //         success: function(data){
    //             $("#unit-data").html(data);
    //         }
    //     });
    // }
    function loadUnits(search = '') {
        $.ajax({
            url: "unit_fetch.php",
            type: "GET",
            data: { unit_name: search },
            success: function(data){
                $("#unit-data").html(data);
            }
        });
    }

    // Trigger search on input
    $('#unit_name').on('input', function() {
        let searchVal = $(this).val();
        loadUnits(searchVal);
    });

    // load table on page ready
    loadUnits();

    //Handle Edit button click
    $(document).on("click", ".editUnitBtn", function() {
        let unit_id = $(this).data("id");

        // AJAX request to fetch single unit data
        $.ajax({
            url: "function/get_unit.php",
            type: "GET",
            data: { unit_id: unit_id },
            dataType: "json", // expects JSON
            success: function(res) {
                try {
                    // If `res` is already parsed by jQuery, this works
                    // But if response is invalid, it will throw
                    if(res.status === "success") {
                        const unit = res.data;

                        // populate modal fields
                        $("#edit_unit_id").val(unit.id);
                        $("#edit_name").val(unit.name);
                        $("#edit_area").val(unit.area);
                        $("#edit_price").val(unit.price);
                        $("#edit_unit_type_id").val(unit.unit_type_id);
                        $("#edit_branch_id").val(unit.branch_id);
                        $("#edit_adult").val(unit.adult);
                        $("#edit_children").val(unit.children);
                        $("#edit_desc").val(unit.description);

                        // uncheck all checkboxes
                        $(".edit_feature_checkbox").prop("checked", false);
                        $(".edit_facility_checkbox").prop("checked", false);

                        if(unit.features) unit.features.forEach(fid => $(".edit_feature_checkbox[value='" + fid + "']").prop("checked", true));
                        if(unit.facilities) unit.facilities.forEach(faid => $(".edit_facility_checkbox[value='" + faid + "']").prop("checked", true));

                        $("#edit-unit").modal("show");
                    } else {
                        alertify.error(res.message);
                    }
                } catch (e) {
                    console.error("Invalid JSON response:", e);
                    console.log("Raw response:", res);
                    alertify.error("AJAX Error: Invalid server response. Check console.");
                }
            },
            error: function(xhr, status, error){
                console.error("AJAX Request Failed:", xhr.responseText);
                alertify.error("AJAX Error: Check console for details.");
            }
        });
    });


    // Handle form submit for updating unit
   $("#edit_unit_form").submit(function(e){
    e.preventDefault();

    $.ajax({
        url: "function/update_unit.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(res){
            if(res.status === "success"){
                alertify.success(res.message);
                alertify.set('notifier','position', 'top-right');

                // Redirect to units.php
                window.location.href = "units.php";
            } else {
                alertify.error(res.message);
            }
        },
        error: function(xhr, status, error){
            alertify.error("AJAX Error: " + error);
        }
    });
});

//     $(document).on('click', '.editUnitBtn', function() {
//     let unit_id = $(this).data('id');

//     $.ajax({
//         type: "POST",
//         url: "function/fetch_unit.php",
//         data: { unit_id: unit_id },
//         dataType: "json",
//         success: function(res) {
//             if(res.status == 200){
//                 let unit = res.data;

//                 // Populate modal inputs
//                 $('#edit_unit_id').val(unit.id);
//                 $('#edit_name').val(unit.name);
//                 $('#edit_area').val(unit.area);
//                 $('#edit_price').val(unit.price);
//                 $('#edit_unit_type_id').val(unit.unit_type_id);
//                 $('#edit_branch_id').val(unit.branch_id);
//                 $('#edit_status').val(unit.status);
//                 $('#edit_adult').val(unit.adult);
//                 $('#edit_children').val(unit.children);
//                 $('#edit_desc').val(unit.desc);

//                 // Populate features
//                 $('.edit_feature_checkbox').prop('checked', false);
//                 unit.features.forEach(fid => {
//                     $('.edit_feature_checkbox[value="'+fid+'"]').prop('checked', true);
//                 });

//                 // Populate facilities
//                 $('.edit_facility_checkbox').prop('checked', false);
//                 unit.facilities.forEach(faid => {
//                     $('.edit_facility_checkbox[value="'+faid+'"]').prop('checked', true);
//                 });

//                 // Show the modal **after populating**
//                 $('#edit-unit').modal('show');
//             } else {
//                 alertify.error(res.message);
//             }
//         },
//         error: function() {
//             alertify.error('Something went wrong.');
//         }
//     });
// });

</script>

<?php
    include('includes/footer.php');
?>




   





