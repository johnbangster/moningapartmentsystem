<?php
include('authentication.php');

    require ('config/code.php');
    require ('includes/header.php');
?> 

<div class="container-fluid px-4">
    <h1 class="mt-4">FEATURES | FACILITIES</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ADMIN DASHBOARD</li>
    </ol>

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                         <h5 class="card-title m-0">Features</h5>
                            <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#feature-s">
                            <i class="fa-regular fa-square-plus"></i> Add
                            </button>
                        </div>
                        <div class="row" id="team-data">
                        </div>
                    </div>
                </div>    
              <div class="table-responsive-md" style="height:250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-dark text-light">
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="features-data">
                        </tbody>
                    </table>
              </div> 
            </div>
        </div>
    </div>

    <div class="row">
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <div class="card border-0 shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="card-title m-0">Facilities</h5>
                            <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#facility-s">
                            <i class="fa-regular fa-square-plus"></i> Add
                            </button>
                        </div>
                        <div class="row" id="team-data">
                        </div>
                    </div>
                </div>    
                <div class="table-responsive-md" style="height:250px; overflow-y: scroll;">
                    <table class="table table-hover border">
                        <thead>
                            <tr class="bg-dark text-light">
                            <th scope="col">#</th>
                            <th scope="col">Icon</th>
                            <th scope="col">Name</th>
                            <th scope="col" width="40%">Description</th>
                            <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody id="facilities-data">
                        </tbody>
                    </table>
                </div> 
            </div>
        </div>
    </div>
    
</div>

<!--feature Modal -->
<div class="modal fade" id="feature-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="feature_s_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Add Feature</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text"name="feature_name" id="feature_name" class="form-control shadow-none" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit"  class="btn custom-bg text-white shdow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!--facilities Modal -->
<div class="modal fade" id="facility-s" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="facility_s_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Add Facilities</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" id="facility_name_inp" name="facility_name" class="form-control shadow-none" required>
                    </div>
                        <div class="mb-3">
                        <label class="form-label  fw-bold">Icon</label>
                        <input type="file" name="facility_icon" accept=".svg" class="form-control shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 500;">Description</label>
                        <textarea name="facility_desc" required class="form-control shadow-none" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn text-secondary shadow-none" data-bs-dismiss="modal">CANCEL</button>
                    <button type="submit"  class="btn custom-bg text-white shadow-none">SUBMIT</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="scripts/features_facilities.js"></script>

<?php
    include('includes/footer.php');
    include('includes/scripts.php');
?>


   





